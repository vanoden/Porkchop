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

			if (!empty($this->accessKey())) {
				$this->credentials = new \Aws\Credentials\CredentialProvider($this->accessKey(), $this->secretKey());

				// Instantiate the S3 client with your AWS credentials
				$this->client = new \Aws\S3\S3Client( [
					'region' => $this->region(),
					'version' => 'latest',
					'credentials' => $this->credentials
				]);
			}
			else if (!empty($this->getMetadata('bucket'))) {
				$this->client = new \Aws\S3\S3Client([
					'region' => $this->region(),
					'version' => 'latest'
				]);
			}

			// This fails if not in an EC2 host
			if (empty($this->getMetadata('bucket'))) {
				$this->_connected = false;
				return false;
			}
			else {
				try {
					$result = $this->client->doesBucketExist($this->getMetadata('bucket'));
				}
				catch (\Aws\S3\Exception\S3Exception $e) {
					$this->error($e->getMessage());
					$this->_connected = false;
					return false;
				}
				catch (\Exception $e) {
					$this->error($e->getMessage());
					$this->_connected = false;
					return false;
				}
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
					$this->error("Failed to connect to S3 service");
					return null;
				}
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
			}
			catch (\Aws\S3\Exception\S3Exception $e) {
				$this->error("Repository upload error: ".$e->getMessage());
				$this->_connected = false;
				return false;
			}
			
			return $result;
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

		/**
		 * Load contents from filesystem
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
			if (preg_match('/^[a-z]{2}\-[a-z]+\-\d+$/',$string)) return true;
			else return false;
		}
	}
