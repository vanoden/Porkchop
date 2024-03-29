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
			else {
				$this->client = new \Aws\S3\S3Client([
					'region' => $this->region(),
					'version' => 'latest'
				]);
			}

			// This fails if not in an EC2 host
			if (!empty($this->getMetadata('bucket'))) {
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

		public function update($parameters = array()): bool {

			// create the repo, then continue to add the custom values needed for S3 only
			parent::update($parameters);
		
			$this->_updateMetadata('accessKey', $parameters['accessKey']);
			$this->_updateMetadata('secretKey', $parameters['secretKey']);
			$this->_updateMetadata('bucket', $parameters['bucket']);
			$this->_updateMetadata('region', $parameters['region']);
			return true;
		}
		
		private function _path($path = null) {
			if (isset($path)) $this->_setMetadata('path', $path);
			return $this->getMetadata('path');
		}

		private function _bucket($bucket = null) {
			if (isset($bucket)) $this->_setMetadata('bucket', $bucket);
			return $this->getMetadata('bucket');
		}

		public function accessKey($key = null) {
			if (isset($key)) $this->_setMetadata('accessKey', $key);
			return $this->getMetadata('accessKey');
		}

		public function secretKey($key = null) {
			if (isset($key)) $this->_setMetadata('secretKey', $key);
			return $this->getMetadata('secretKey');
		}

		public function region($region = null) {
			if (isset($region)) $this->_setMetadata('region', $region);
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
		public function validAccessKey($string) {
			if (preg_match('/^\w{16,128}$/',$string)) return true;
			else return false;
		}

		public function validSecretKey($string) {
			if (preg_match('/^[\w\/\+]{20,}$/',$string)) return true;
			else return false;
		}

		public function validBucket($string) {
			// Bucket naming rules from AWS:
			// https://docs.aws.amazon.com/AmazonS3/latest/userguide/bucketnamingrules.html

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

		public function validRegion($string) {
			if (preg_match('/^[a-z]{2}\-[a-z]+\-\d+$/',$string)) return true;
			else return false;
		}
	}
