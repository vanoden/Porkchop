<?php

namespace Site;

/**
 * Input Class
 * 
 * Handles input data processing, sanitization, and security
 */
class Input {

    /**
     * IP address of the current user
     */
    protected string $ip_address = '';

    /**
     * Request headers
     */
    protected array $headers = [];

    /**
     * Raw input stream data
     */
    protected ?string $raw_input_stream = null;

    /**
     * Parsed input stream data
     */
    protected ?array $input_stream = null;

    /**
     * Sanitization patterns for different input types
     */
    protected array $patterns = [

        // Basic text patterns
        'text' => '/[^a-zA-Z0-9\s\-_\.]/u',
        'alpha' => '/[^a-zA-Z]/u',
        'alphanumeric' => '/[^a-zA-Z0-9]/u',
        'name' => '/[^a-zA-Z\s\-\']/u',  // Allows letters, spaces, hyphens, apostrophes

        // Contact information patterns
        'phone' => '/[^0-9\+\-\(\)\s]/u',  // International phone format
        'phone_number' => '/[^0-9\+\-\(\)\s]/u',  // Alias for phone
        'email' => '/[^a-zA-Z0-9\@\.\-_]/u',
        'website' => '/[^a-zA-Z0-9\:\-\_\.\/?&=@]/u',

        // Address patterns
        'address' => '/[^a-zA-Z0-9\s\-\.,#\']/u',
        'city' => '/[^a-zA-Z\s\-\']/u',
        'state' => '/[^a-zA-Z\s]/u',
        'zip' => '/[^0-9\-]/u',
        'postal_code' => '/[^0-9a-zA-Z\-\s]/u',  // International postal codes

        // Number patterns
        'integer' => '/[^0-9\-]/u',
        'decimal' => '/[^0-9\-\.]/u',
        'price' => '/[^0-9\.\,]/u',
        'percentage' => '/[^0-9\-\.%]/u',

        // Date and time patterns
        'date' => '/[^0-9\-\/]/u',
        'time' => '/[^0-9\:apmAPM\s]/u',
        'datetime' => '/[^0-9\-\/\:\s]/u',

        // Document patterns
        'filename' => '/[^a-zA-Z0-9\-_\.]/u',
        'path' => '/[^a-zA-Z0-9\-_\.\/]/u',

        // Social media patterns
        'username' => '/[^a-zA-Z0-9_\-\.@]/u',

        // Special formats
        'password' => '/[^a-zA-Z0-9\-_!@#$%^&*()+=]/u',

        // Code patterns
        'ip_address' => '/[^0-9\.]/u',
        'mac_address' => '/[^0-9a-fA-F\:]/u',

    ];

    /**
     * Custom patterns added at runtime
     */
    protected array $customPatterns = [];

    /**
     * Error messages
     */
    protected array $errors = [];

    /**
     * Fetch from array
     *
     * Internal method used to retrieve values from global arrays.
     */
    protected function fetchFromArray(array &$array, string|array|null $index = null, bool $xss_clean = false): mixed {

        // If $index is NULL, return the whole array
        if ($index === null) return $xss_clean ? $this->cleanXSS($array) : $array;

        // Allow fetching multiple keys at once
        if (is_array($index)) {
            $output = [];
            foreach ($index as $key) $output[$key] = $this->fetchFromArray($array, $key, $xss_clean);
            return $output;
        }

        // Handle array notation (e.g., "foo[bar][baz]")
        if (preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) {
            $value = $array;
            foreach ($matches[0] as $segment) {
                $key = trim($segment, '[]');
                if ($key === '') break;
                if (!isset($value[$key])) return null;
                $value = $value[$key];
            }
            return $xss_clean ? $this->cleanXSS($value) : $value;
        }

        // Simple key
        $value = $array[$index] ?? null;
        return $xss_clean ? $this->cleanXSS($value) : $value;
    }

    /**
     * Clean data for XSS
     */
    protected function cleanXSS(mixed $data): mixed {

        if (is_array($data)) {
            foreach ($data as $key => $value) $data[$key] = $this->cleanXSS($value);
            return $data;
        }

        if (is_string($data)) return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }

    /**
     * Sanitize a value based on a pattern type
     */
    public function sanitize(mixed $value, string $type): mixed {

        if (!isset($this->patterns[$type])) {
            $this->errors[] = "Unknown type '{$type}' for sanitization";
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) $value[$key] = $this->sanitize($val, $type);
            return $value;
        }

        if (!is_string($value)) return $value;

        // Special pre-processing
        $value = trim($value);
        switch ($type) {
            case 'email':
                $value = strtolower($value);
                break;
            case 'price':
                $value = str_replace(',', '', $value);
                break;
            case 'percentage':
                $value = str_replace('%', '', $value);
                break;
        }

        // Apply regex pattern
        $value = preg_replace($this->patterns[$type], '', $value);

        // Post-processing validation
        switch ($type) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL))
                    $this->errors[] = "Invalid email format";
                break;
            case 'website':
                if (!filter_var($value, FILTER_VALIDATE_URL))
                    $this->errors[] = "Invalid URL format";
                break;
            case 'ip_address':
                if (!filter_var($value, FILTER_VALIDATE_IP))
                    $this->errors[] = "Invalid IP address format";
                break;
        }

        return $value;
    }

    /**
     * Add a custom sanitization pattern
     */
    public function addPattern(string $type, string $pattern): bool {

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $type))
            throw new \InvalidArgumentException("Invalid type name. Must start with a letter and contain only lowercase letters, numbers, and underscores.");

        if (empty($pattern))
            throw new \InvalidArgumentException("Pattern cannot be empty.");

        if ($pattern[0] !== '/' || substr($pattern, -1) !== '/')
            throw new \InvalidArgumentException("Pattern must start and end with '/'.");

        try {
            preg_match($pattern, '');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid regex pattern: " . $e->getMessage());
        }

        if (isset($this->patterns[$type]) && !isset($this->customPatterns[$type])) {
            $this->errors[] = "Cannot overwrite built-in pattern type '{$type}'. Use a different name.";
            return false;
        }

        $this->patterns[$type] = $pattern;
        $this->customPatterns[$type] = true;
        return true;
    }

    /**
     * Get any errors that occurred during sanitization
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Check if any errors occurred during sanitization
     */
    public function hasErrors(): bool {
        return !empty($this->errors);
    }

    /**
     * Fetch an item from GET data
     */
    public function get(?string $index = null, ?string $type = null, bool $xss_clean = false): mixed {
        $value = $this->fetchFromArray($_GET, $index, $xss_clean);
        return $type ? $this->sanitize($value, $type) : $value;
    }

    /**
     * Fetch an item from POST data
     */
    public function post(?string $index = null, ?string $type = null, bool $xss_clean = false): mixed {
        $value = $this->fetchFromArray($_POST, $index, $xss_clean);
        return $type ? $this->sanitize($value, $type) : $value;
    }

    /**
     * Fetch an item from POST data with fallback to GET
     */
    public function postGet(string $index, ?string $type = null, bool $xss_clean = false): mixed {
        $output = $this->post($index, $type, $xss_clean);
        if ($output === null) $output = $this->get($index, $type, $xss_clean);
        return $output;
    }

    /**
     * Fetch an item from GET data with fallback to POST
     */
    public function getPost(string $index, ?string $type = null, bool $xss_clean = false): mixed {
        $output = $this->get($index, $type, $xss_clean);
        if ($output === null) $output = $this->post($index, $type, $xss_clean);
        return $output;
    }

    /**
     * Fetch an item from REQUEST data
     */
    public function request(?string $index = null, ?string $type = null, bool $xss_clean = false): mixed {
        $value = $this->fetchFromArray($_REQUEST, $index, $xss_clean);
        return $type ? $this->sanitize($value, $type) : $value;
    }

    /**
     * Fetch an item from SERVER data
     */
    public function server(string $index, ?string $type = null, bool $xss_clean = false): mixed {
        $value = $this->fetchFromArray($_SERVER, $index, $xss_clean);
        return $type ? $this->sanitize($value, $type) : $value;
    }

    /**
     * Get Request Method
     */
    public function method(bool $upper = false): string {
        $method = $this->server('REQUEST_METHOD') ?? 'GET';
        return $upper ? strtoupper($method) : strtolower($method);
    }

    /**
     * Get raw input stream
     */
    public function rawInput(): string {
        if ($this->raw_input_stream === null) $this->raw_input_stream = file_get_contents('php://input');
        return $this->raw_input_stream;
    }

    /**
     * Get JSON input
     */
    public function json(?string $index = null, ?string $type = null, bool $xss_clean = false): mixed {
        if ($this->input_stream === null) $this->input_stream = json_decode($this->rawInput(), true) ?? [];
        $value = $this->fetchFromArray($this->input_stream, $index, $xss_clean);
        return $type ? $this->sanitize($value, $type) : $value;
    }
}
