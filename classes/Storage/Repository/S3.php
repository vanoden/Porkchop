<?php
	namespace Storage\Repository;
	require THIRD_PARTY.'/autoload.php';

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
			$this->_addMetadataKeys(array("accessKey","secretKey","bucket","region"));
			parent::__construct($id);
		}

		public function connect() {
			$this->configuration = new \Site\Configuration();

			// Try to get bucket metadata - if it doesn't exist, we can't connect
			if (empty($this->getMetadata('bucket'))) {
				$this->error("Bucket name is required");
				$this->_connected = false;
				return false;
			}

			if (!empty($this->accessKey()) && !empty($this->secretKey())) {
				// Use explicit credentials if provided
				$this->credentials = new \Aws\Credentials\CredentialProvider($this->accessKey(), $this->secretKey());

				// Instantiate the S3 client with your AWS credentials
				$this->client = new \Aws\S3\S3Client( [
					'region' => $this->region() ?: 'us-east-1',
					'version' => 'latest',
					'credentials' => $this->credentials
				]);
			}
			else {
				// Use IAM roles or instance profile authentication
				$this->client = new \Aws\S3\S3Client([
					'region' => $this->region() ?: 'us-east-1',
					'version' => 'latest'
				]);
			}

			// Test the connection by checking if bucket exists
			try {
				$result = $this->client->doesBucketExist($this->getMetadata('bucket'));
				if (!$result) {
					$this->error("Bucket '" . $this->getMetadata('bucket') . "' does not exist or is not accessible");
					$this->_connected = false;
					return false;
				}
			}
			catch (\Aws\S3\Exception\S3Exception $e) {
				$this->error("AWS Exception: " . $e->getMessage());
				$this->_connected = false;
				return false;
			}
			catch (\Exception $e) {
				$this->error("General Exception: " . $e->getMessage());
				$this->_connected = false;
				return false;
			}

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
			if (isset($bucket)) $this->setMetadata('bucket', $bucket);
			return $this->getMetadata('bucket');
		}

		/**
		 * Get/Set AWS Access Key
		 * @param string $key 
		 * @return string 
		 */
		public function accessKey($key = null) {
			if (isset($key)) $this->setMetadata('accessKey', $key);
			return $this->getMetadata('accessKey');
		}

		/**
		 * Get/Set AWS Secret Key
		 * @param string $key 
		 * @return string 
		 */
		public function secretKey($key = null) {
			if (isset($key)) $this->setMetadata('secretKey', $key);
			return $this->getMetadata('secretKey');
		}

		/**
		 * Get/Set AWS Region
		 * @param string $region 
		 * @return string 
		 */
		public function region($region = null) {
			if (isset($region)) $this->setMetadata('region', $region);
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
					$this->error("Failed to connect to S3 service: ".$this->error());
					app_log("Failed to connect to S3 service for bucket '".$this->_bucket()."': ".$this->error(),'error');
					app_log("Bucket: ".$this->_bucket(),'error');
					app_log("Region: ".$this->region(),'error');
print_r("Error connecting to S3: ".$this->error());
					return false;
				}
print_r("Connected to S3 bucket '".$this->_bucket()."' in region '".$this->region()."'");
			}
			else {
				app_log("Already connected to S3 bucket '".$this->_bucket()."' in region '".$this->region()."'");
print_r("Already connected to S3 bucket '".$this->_bucket()."' in region '".$this->region()."'");
			}

			try {
				// Upload an object by streaming the contents of a file
				$result = $this->client->putObject(array(
					'Bucket'     => $this->_bucket(),
					'Key'        => $file->code(),
					'SourceFile' => $path,
					'Metadata'   => array(
						'Source' => 'Uploaded from Website'
					)
				));
				app_log("Uploaded File '".$file->code()."' to S3 bucket '".$this->_bucket()."' [".$result['ObjectURL']."]",'notice');
			}
			catch (\Aws\S3\Exception\S3Exception $e) {
				$this->error("Repository upload error: ".$e->getMessage());
print_r("AWS Exception uploading file to S3: ".$this->error());
				$this->_connected = false;
				return false;
			}
			catch( \Exception $e) {
				$this->error("General Exception: ".$e->getMessage());
print_r("General Exception uploading file to S3: ".$this->error());
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
					if (empty($this->error())) $this->error("Failed to connect to S3 service");
					return null;
				}
			}

			$tmpFile = '/tmp/s3-'.$file->code();
			app_log("Getting file ".$file->code());
			// Load contents from filesystem
			try {
				$this->client->getObject(array(
					'Bucket'	=> $this->_bucket(),
					'Key'		=> $file->code(),
					'SaveAs'	=> $tmpFile
				));
			} catch (\exception $e) {
				$this->error("Failed to get file: ".$e->getMessage());
				return;
			}
			if (file_exists($tmpFile)) {
				app_log("Downloaded File '".$tmpFile."' [".filesize($tmpFile)." bytes]",'notice');

				$fh = fopen($tmpFile,'r');
				header("Content-Type: ".$file->mime_type);
				header('Content-Disposition: filename="'.$file->name().'"');
				while (!feof($fh)) {
					$buffer = fread($fh,8192);
					print $buffer;
					flush();
					ob_flush();
				}
				fclose($fh);
				exit;
			}
			else {
				app_log("Failed to download file",'error');
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
					if (empty($this->error())) $this->error("Failed to connect to S3 service");
					return null;
				}
			}

			try {
				$result = $this->client->getObject(array(
					'Bucket'	=> $this->_bucket(),
					'Key'		=> $file->code()
				));
				return $result['Body'];
			} catch (\Aws\S3\Exception\S3Exception $e) {
				$this->error("Failed to get file content: ".$e->getMessage());
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
					if (empty($this->error())) $this->error("Failed to connect to S3 service");
					return null;
				}
			}
			$result = $this->client->headObject(array(
				'Bucket'	=> $this->_bucket(),
				'Key'		=> $string
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
			if (empty($string)) return true;
			if (preg_match('/^\w{16,128}$/',$string)) return true;
			else return false;
		}

		/**
		 * Secret key must be empty or have 20+ chars of 
		 * alphanumeric, / or + characters
		 * @param mixed string containing secret key to check 
		 * @return bool True if valid, false if not
		 */
		public function validSecretKey($string) {
			if (empty($string)) return true;
			if (preg_match('/^[\w\/\+]{20,}$/',$string)) return true;
			else return false;
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
			
			// Bucket names cannot have 2 adjacent periods
			if (preg_match('/\.\./',$string)) return false;

			// Bucket names cannot be formated as an ip address
			if (preg_match('/^\d+\.\d+\.\d+\.\d+$',$string)) return false;

			// Bucket names cannot start with xn--
			if (preg_match('/^xn\-\-',$string)) return false;

			// Bucket names cannot end with -s3alias
			if (preg_match('/\-s3alias$/',$string)) return false;

			// Bucket names must start and end with a letter or number, and must be 3-63 letters, numbers, dots or hyphens
			if (preg_match('/^\w[\w\.\-]{1,61}\w$/',$string)) return true;

			return false;
		}

		/**
		 * Validate AWS Region Names
		 * @param mixed $string
		 * @return bool True if valid, false if not
		 */
		public function validRegion($string) {
			// Allow empty region - AWS SDK will use default region
			if (empty($string)) return true;
			if (preg_match('/^[a-z]{2}\-[a-z]+\-\d+$/',$string)) return true;
			else return false;
		}
	}
