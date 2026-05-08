<?php

namespace Shipping;

class UPSStatus
{
    const UPS_TRACK_URL = 'https://webapis.ups.com/track/api/Track/GetStatus';

    // Minimum seconds between queries for the same tracking number (tune as needed)
    const MIN_QUERY_INTERVAL = 900; // 15 minutes

    /**
     * Query UPS public tracking page and parse status.
     *
     * @param string   $trackingNumber
     * @param int|null $lastQueriedAt Unix timestamp of last query for this tracking number (optional)
     * @return array{
     *   success: bool,
     *   status_text?: string,
     *   delivered?: bool,
     *   status_datetime?: string|null,
     *   location?: string|null,
     *   raw_status?: string,
     *   error?: string,
     *   rate_limited?: bool,
     *   parse_error?: bool
     * }
     */
    public static function query(string $trackingNumber, ?int $lastQueriedAt = null): array
    {
        $trackingNumber = trim($trackingNumber);

        if ($trackingNumber === '') {
            return [
                'success' => false,
                'error'   => 'No UPS tracking number specified.',
            ];
        }

        // Simple rate limit safeguard
        if ($lastQueriedAt !== null && (time() - $lastQueriedAt) < self::MIN_QUERY_INTERVAL) {
            return [
                'success'      => false,
                'rate_limited' => true,
                'error'        => 'UPS was queried too recently for this shipment.',
            ];
        }

        $result = self::fetchTrackingJson($trackingNumber);

        if ($result === null) {
            return [
                'success' => false,
                'error'   => 'Unable to contact UPS tracking service (no response object).',
            ];
        }

        if (isset($result['transport_error']) && $result['transport_error'] === true) {
            // Network / HTTP / decode level problem; bubble up details.
            return [
                'success' => false,
                'error'   => $result['message'] ?? 'Unable to contact UPS tracking service (transport error).',
            ];
        }

        return self::parseTrackingJson($result['data']);
    }

    /**
     * Fetch UPS tracking JSON via cURL.
     */
    protected static function fetchTrackingJson(string $trackingNumber): ?array
    {
        $url = self::UPS_TRACK_URL . '?' . http_build_query([
            'loc' => 'en_US',
        ]);
        $payload = [
            'Locale'         => 'en_US',
            'TrackingNumber' => [$trackingNumber],
        ];
        $ch = curl_init();
        // Force HTTP/1.1 explicitly
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Connection: close',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body   = curl_exec($ch);
        $errNo  = curl_errno($ch);
        $errMsg = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($errNo !== 0) {
            $msg = sprintf('UPSStatus cURL error (%d): %s', $errNo, $errMsg);
            error_log($msg);
            return [
                'transport_error' => true,
                'message'         => $msg,
            ];
        }
        if ($status < 200 || $status >= 300) {
            $msg = sprintf('UPSStatus HTTP status %d for %s (body: %s)', $status, $url, substr((string)$body, 0, 256));
            error_log($msg);
            return [
                'transport_error' => true,
                'message'         => $msg,
            ];
        }
        if (!is_string($body) || $body === '') {
            $msg = 'UPSStatus empty body received from UPS.';
            error_log($msg);
            return [
                'transport_error' => true,
                'message'         => $msg,
            ];
        }
        $data = json_decode($body, true);
        if (!is_array($data)) {
            $msg = 'UPSStatus JSON decode failed for body: ' . substr($body, 0, 512);
            error_log($msg);
            return [
                'transport_error' => true,
                'message'         => $msg,
            ];
        }
        return [
            'transport_error' => false,
            'data'            => $data,
        ];
    }

    /**
     * Parse UPS tracking JSON structure.
     */
    protected static function parseTrackingJson(array $data): array
    {
        if (empty($data['trackResponse']['shipment'][0]['package'][0]['activity'][0]['status']['description'])) {
            error_log('UPSStatus JSON parse failure; unexpected structure: ' . substr(json_encode($data), 0, 512));
            return [
                'success'     => false,
                'error'       => 'Could not extract UPS status from JSON response.',
                'parse_error' => true,
            ];
        }

        $activity    = $data['trackResponse']['shipment'][0]['package'][0]['activity'][0];
        $status      = $activity['status'] ?? [];
        $statusText  = (string)($status['description'] ?? '');

        $lower     = mb_strtolower($statusText, 'UTF-8');
        $delivered = (strpos($lower, 'delivered') !== false);

        // Date/time come as YYYYMMDD and HHMMSS; convert to human-friendly if present.
        $statusDatetime = null;
        if (!empty($activity['date'])) {
            $date = $activity['date'];
            $time = $activity['time'] ?? null;
            if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $date, $m)) {
                $formatted = $m[1] . '-' . $m[2] . '-' . $m[3];
                if ($time && preg_match('/^(\d{2})(\d{2})(\d{2})$/', $time, $t)) {
                    $formatted .= ' ' . $t[1] . ':' . $t[2] . ':' . $t[3];
                }
                $statusDatetime = $formatted;
            }
        }

        // Location text (if available)
        $location = null;
        if (!empty($activity['location']['address'])) {
            $addr = $activity['location']['address'];
            $parts = [];
            foreach (['city', 'stateProvince', 'postalCode', 'country'] as $key) {
                if (!empty($addr[$key])) {
                    $parts[] = $addr[$key];
                }
            }
            if ($parts) {
                $location = implode(', ', $parts);
            }
        }

        return [
            'success'         => true,
            'status_text'     => $statusText,
            'delivered'       => $delivered,
            'status_datetime' => $statusDatetime,
            'location'        => $location,
            'raw_status'      => $statusText,
        ];
    }
}
