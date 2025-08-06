<?php
namespace Storage\Repository;
require THIRD_PARTY . '/autoload.php';

use \Aws\Common\Aws;
use \Aws\S3\S3Client;
use \Aws\Common\Credentials\Credentials;
use \Aws\S3\Exception\S3Exception;

class S3 extends \Storage\Repository {
	protected $aws;
	public $bucket;
	public $configuration;
	public $region;
	protected $credentials;
	private $client;
	private $_connected = false;

	public function __construct($id = null) {
		$this->type = 's3';
		$this->_addMetadataKeys(array("accessKey", "secretKey", "bucket", "region"));
		parent::__construct($id);
	}

	public function connect() {
		$this->configuration = new \Site\Configuration();

		// Try to get bucket metadata - if it doesn't exist, we can't connect
		if (empty($this->getMetadata('bucket'))) {
			$this->error("Bucket name is required");
			app_log("S3 Repository connection failed: Bucket name is required", 'error');
			$this->_connected = false;
			return false;
		}

		$bucket_name = $this->getMetadata('bucket');
		$region = $this->region() ?: 'us-east-1';

		app_log("Attempting to connect to S3 bucket: '$bucket_name' in region: '$region'", 'info');

		try {
			if (!empty($this->accessKey()) && !empty($this->secretKey())) {
				// Use explicit credentials if provided
				app_log("Using explicit AWS credentials for S3 connection", 'debug');
				$this->credentials = new \Aws\Credentials\CredentialProvider($this->accessKey(), $this->secretKey());

				// Instantiate the S3 client with your AWS credentials
				$this->client = new \Aws\S3\S3Client([
					'region' => $region,
					'version' => 'latest',
					'credentials' => $this->credentials
				]);
			} else {
				// Use IAM roles or instance profile authentication
				app_log("Using IAM role/instance profile authentication for S3 connection", 'debug');
				$this->client = new \Aws\S3\S3Client([
					'region' => $region,
					'version' => 'latest'
				]);
			}

			// Test the connection by checking if bucket exists
			app_log("Testing S3 bucket existence and accessibility: '$bucket_name'", 'debug');
			$result = $this->client->doesBucketExist($bucket_name);
			if (!$result) {
				$this->error("Bucket '$bucket_name' does not exist or is not accessible");
				app_log("S3 Bucket verification failed: Bucket '$bucket_name' does not exist or is not accessible", 'error');
				$this->_connected = false;
				return false;
			}

			// Test write permissions by attempting to check bucket location
			$location = $this->client->getBucketLocation(['Bucket' => $bucket_name]);
			app_log("S3 Bucket location confirmed: " . ($location['LocationConstraint'] ?: 'us-east-1'), 'debug');

		} catch (\Aws\S3\Exception\S3Exception $e) {
			$error_code = $e->getAwsErrorCode();
			$error_message = $e->getMessage();
			$this->error("AWS S3 Exception [$error_code]: $error_message");
			app_log("S3 Connection failed with AWS Exception [$error_code]: $error_message", 'error');
			app_log("Bucket: '$bucket_name', Region: '$region'", 'error');
			$this->_connected = false;
			return false;
		} catch (\Exception $e) {
			$error_message = $e->getMessage();
			$this->error("General Exception: $error_message");
			app_log("S3 Connection failed with General Exception: $error_message", 'error');
			app_log("Bucket: '$bucket_name', Region: '$region'", 'error');
			$this->_connected = false;
			return false;
		}

		app_log("Successfully connected to S3 bucket: '$bucket_name'", 'info');
		$this->_connected = true;
		return true;
	}

	/**
	 * Update an existing S3 Repository
	 * @param array $parameters
	 * @return bool True if update successful
	 */
	public function update($parameters = array()): bool {
		// Shared update method
		parent::update($parameters);
		return true;
	}

	/**
	 * Get/Set Name of S3 Bucket
	 * @param string $bucket 
	 * @return string 
	 */
	private function _bucket($bucket = null) {
		if (isset($bucket))
			$this->setMetadata('bucket', $bucket);
		return $this->getMetadata('bucket');
	}

	/**
	 * Get/Set AWS Access Key
	 * @param string $key 
	 * @return string 
	 */
	public function accessKey($key = null) {
		if (isset($key))
			$this->setMetadata('accessKey', $key);
		return $this->getMetadata('accessKey');
	}

	/**
	 * Get/Set AWS Secret Key
	 * @param string $key 
	 * @return string 
	 */
	public function secretKey($key = null) {
		if (isset($key))
			$this->setMetadata('secretKey', $key);
		return $this->getMetadata('secretKey');
	}

	/**
	 * Get/Set AWS Region
	 * @param string $region 
	 * @return string 
	 */
	public function region($region = null) {
		if (isset($region))
			$this->setMetadata('region', $region);
		return $this->getMetadata('region');
	}

	/**
	 * Write contents to S3 cloud storage
	 *
	 * @param $file
	 * @param $path
	 */
	public function addFile($file, $path) {
		if (!$this->_connected) {
			if (!$this->connect()) {
				$this->error("Failed to connect to S3 service: " . $this->error());
				app_log("Failed to connect to S3 service for bucket '" . $this->_bucket() . "': " . $this->error(), 'error');
				app_log("Bucket: " . $this->_bucket(), 'error');
				app_log("Region: " . $this->region(), 'error');
print_r("Error connecting to S3: ".$this->error());
				return false;
			}
print_r("Connected to S3 bucket '".$this->_bucket()."' in region '".$this->region()."'");
			}
			else {
				app_log("Already connected to S3 bucket '".$this->_bucket()."' in region '".$this->region()."'");
print_r("Already connected to S3 bucket '".$this->_bucket()."' in region '".$this->region()."'");
		}

		$bucket_name = $this->_bucket();
		$file_key = $file->code();
		$file_size = filesize($path);

		app_log("Attempting to upload file '$file_key' to S3 bucket '$bucket_name' (Size: $file_size bytes)", 'info');
		app_log("Upload source path: '$path'", 'debug');

		// Verify source file exists and is readable
		if (!file_exists($path)) {
			$this->error("Source file does not exist: $path");
			app_log("Upload failed: Source file does not exist: $path", 'error');
			return false;
		}

		if (!is_readable($path)) {
			$this->error("Source file is not readable: $path");
			app_log("Upload failed: Source file is not readable: $path", 'error');
			return false;
		}

		try {
			// Upload an object by streaming the contents of a file
			$upload_params = array(
				'Bucket' => $bucket_name,
				'Key' => $file_key,
				'SourceFile' => $path,
				'Metadata' => array(
					'Source' => 'Uploaded from Website',
					'OriginalName' => $file->name ?? 'unknown',
					'UploadTime' => date('Y-m-d H:i:s')
				)
			);

			app_log("S3 Upload parameters: Bucket='$bucket_name', Key='$file_key'", 'debug');

			$result = $this->client->putObject($upload_params);

			$object_url = $result['ObjectURL'] ?? "s3://$bucket_name/$file_key";
			app_log("Successfully uploaded file '$file_key' to S3 bucket '$bucket_name' [$object_url]", 'notice');

			// Verify the upload by checking if object exists
			$head_result = $this->client->headObject([
				'Bucket' => $bucket_name,
				'Key' => $file_key
			]);
			$uploaded_size = $head_result['ContentLength'];
			app_log("Upload verification: Object size in S3 = $uploaded_size bytes", 'debug');

		} catch (\Aws\S3\Exception\S3Exception $e) {
			$error_code = $e->getAwsErrorCode();
			$error_message = $e->getMessage();
			$this->error("S3 Upload Error [$error_code]: $error_message");
			app_log("S3 Upload failed with AWS Exception [$error_code]: $error_message", 'error');
			app_log("Failed upload details - Bucket: '$bucket_name', Key: '$file_key', Source: '$path'", 'error');
			$this->_connected = false;
			return false;
		} catch (\Exception $e) {
			$error_message = $e->getMessage();
			$this->error("Upload Exception: $error_message");
			app_log("S3 Upload failed with General Exception: $error_message", 'error');
			app_log("Failed upload details - Bucket: '$bucket_name', Key: '$file_key', Source: '$path'", 'error');
			$this->_connected = false;
			return false;
		}

		return true;
	}

	/**
	 * for public API, unset the AWS info for security
	 */
	public function unsetAWS() {
		unset($this->aws);
		unset($this->client);
		unset($this->configuration);
		unset($this->secretKey);
		unset($this->credentials);
	}

	/** @method retrieveFile($file)
	 * Retrieve file from S3 storage and send it to the client
	 * @param $file
	 * @return bool
	 */
	public function retrieveFile($file) {
		if (!$this->_connected) {
			if (!$this->connect()) {
				if (empty($this->error()))
					$this->error("Failed to connect to S3 service");
				return null;
			}
		}

		$tmpFile = '/tmp/s3-' . $file->code();
		app_log("Getting file " . $file->code());
		// Load contents from filesystem
		try {
			$this->client->getObject(array(
				'Bucket' => $this->_bucket(),
				'Key' => $file->code(),
				'SaveAs' => $tmpFile
			));
		} catch (\exception $e) {
			$this->error("Failed to get file: " . $e->getMessage());
			return;
		}
		if (file_exists($tmpFile)) {
			app_log("Downloaded File '" . $tmpFile . "' [" . filesize($tmpFile) . " bytes]", 'notice');

			$fh = fopen($tmpFile, 'r');
			header("Content-Type: " . $file->mime_type);
			header('Content-Disposition: filename="' . $file->name() . '"');
			while (!feof($fh)) {
				$buffer = fread($fh, 8192);
				print $buffer;
				flush();
				ob_flush();
			}
			fclose($fh);
			exit;
		} else {
			app_log("Failed to download file", 'error');
		}
	}

	/** @method content()
	 * Get the content of specified file from S3
	 * @param $file
	 * @return string
	 */
	public function content($file) {
		if (!$this->_connected) {
			if (!$this->connect()) {
				if (empty($this->error()))
					$this->error("Failed to connect to S3 service");
				return null;
			}
		}

		try {
			$result = $this->client->getObject(array(
				'Bucket' => $this->_bucket(),
				'Key' => $file->code()
			));
			return $result['Body'];
		} catch (\Aws\S3\Exception\S3Exception $e) {
			$this->error("Failed to get file content: " . $e->getMessage());
			return null;
		}
	}

	/**
	 * See if file is present on S3
	 * @param mixed $string 
	 * @return null|void 
	 */
	public function checkFile($string) {
		if (!$this->_connected) {
			if (!$this->connect()) {
				if (empty($this->error()))
					$this->error("Failed to connect to S3 service");
				return null;
			}
		}
		$result = $this->client->headObject(array(
			'Bucket' => $this->_bucket(),
			'Key' => $string
		));
		print_r($result);
	}

	/********************************/
	/* Validation Methods 			*/
	/********************************/
	/**
	 * Access key must be empty or have 16-129
	 * alphanumeric characters
	 * @params string containing access key to check
	 * @return bool True if valid, false if not
	 */
	public function validAccessKey($string) {
		if (empty($string))
			return true;
		if (preg_match('/^\w{16,128}$/', $string))
			return true;
		else
			return false;
	}

	/**
	 * Secret key must be empty or have 20+ chars of 
	 * alphanumeric, / or + characters
	 * @param mixed string containing secret key to check 
	 * @return bool True if valid, false if not
	 */
	public function validSecretKey($string) {
		if (empty($string))
			return true;
		if (preg_match('/^[\w\/\+]{20,}$/', $string))
			return true;
		else
			return false;
	}

	/**
	 * Test S3 write permissions by attempting to upload a small test file
	 * @return bool
	 */
	public function testWritePermissions() {
		if (!$this->_connected) {
			if (!$this->connect()) {
				return false;
			}
		}

		$bucket_name = $this->_bucket();
		$test_key = 'test-write-permissions-' . time() . '.txt';
		$test_content = 'This is a test file to verify write permissions.';

		app_log("Testing S3 write permissions for bucket '$bucket_name'", 'debug');

		try {
			// Attempt to upload a test object
			$this->client->putObject([
				'Bucket' => $bucket_name,
				'Key' => $test_key,
				'Body' => $test_content,
				'Metadata' => [
					'Source' => 'Write Permission Test'
				]
			]);

			// If successful, delete the test object
			$this->client->deleteObject([
				'Bucket' => $bucket_name,
				'Key' => $test_key
			]);

			app_log("S3 write permissions test passed for bucket '$bucket_name'", 'debug');
			return true;

		} catch (\Aws\S3\Exception\S3Exception $e) {
			$error_code = $e->getAwsErrorCode();
			$error_message = $e->getMessage();
			app_log("S3 write permissions test failed [$error_code]: $error_message", 'error');
			$this->error("S3 write permissions test failed [$error_code]: $error_message");
			return false;
		} catch (\Exception $e) {
			app_log("S3 write permissions test failed: " . $e->getMessage(), 'error');
			$this->error("S3 write permissions test failed: " . $e->getMessage());
			return false;
		}
	}

	/**
	 * Validate bucket names based on naming rules from 
	 * https://docs.aws.amazon.com/AmazonS3/latest/userguide/bucketnamingrules.html
	 * @param mixed string containing bucket name to check
	 * @return bool True if valid, false if not
	 */
	public function validBucket($string) {
		// Bucket name is required for S3 repositories
		if (empty($string)) {
			$this->error("Bucket name is required for S3 repositories");
			return false;
		}

		// Bucket names must be 3-63 characters long
		if (strlen($string) < 3 || strlen($string) > 63) {
			$this->error("Bucket name must be between 3 and 63 characters long");
			return false;
		}

		// Bucket names cannot have 2 adjacent periods
		if (preg_match('/\.\./', $string)) {
			$this->error("Bucket name cannot contain consecutive periods");
			return false;
		}

		// Bucket names cannot be formated as an ip address
		if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $string)) {
			$this->error("Bucket name cannot be formatted as an IP address");
			return false;
		}

		// Bucket names cannot start with xn--
		if (preg_match('/^xn\-\-', $string)) {
			$this->error("Bucket name cannot start with 'xn--'");
			return false;
		}

		// Bucket names cannot end with -s3alias
		if (preg_match('/\-s3alias$/', $string)) {
			$this->error("Bucket name cannot end with '-s3alias'");
			return false;
		}

		// Bucket names must start and end with a letter or number, and must be 3-63 letters, numbers, dots or hyphens
		if (preg_match('/^\w[\w\.\-]{1,61}\w$/', $string))
			return true;

		$this->error("Bucket name contains invalid characters or format");
		return false;
	}

	/**
	 * Validate AWS Region Names
	 * @param mixed $string
	 * @return bool True if valid, false if not
	 */
	public function validRegion($string) {
		// Allow empty region - AWS SDK will use default region
		if (empty($string))
			return true;
		if (preg_match('/^[a-z]{2}\-[a-z]+\-\d+$/', $string))
			return true;
		else
			return false;
	}
}
