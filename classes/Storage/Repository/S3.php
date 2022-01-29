<?php
	namespace Storage\Repository;
    require THIRD_PARTY.'/autoload.php';

    use Aws\Common\Aws;
    use Aws\S3\S3Client;
    use Aws\Common\Credentials\Credentials;

	class S3 extends \Storage\Repository {

	    protected $aws;
	    protected $client;
	    public $bucket;
	    public $configuration;
	    public $region;
		private $_connected = false;
	    
		public function __construct($id = null) {

			if ($id > 0) {
				$this->id = $id;
				if ($this->details()) {
					$this->connect();
				}
			}
		}

		public function connect() {
			$this->configuration = new \Site\Configuration();
			$this->credentials = new Credentials($this->accessKey(), $this->secretKey());

			// Instantiate the S3 client with your AWS credentials
			$this->s3Client = S3Client::factory ( array (
				'credentials' => $this->credentials
			) );

			// Create a service builder using a configuration file
			$this->aws = Aws::factory();
		
			// Get the client from the builder by namespace
			$this->client = $this->aws->get('S3');

			$this->_connected = true;
		}

        public function update($parameters = array()) {

            // create the repo, then continue to add the custom values needed for S3 only
            parent::update($parameters);
		
		    $this->_updateMetadata('accessKey', $parameters['accessKey']);
		    $this->_updateMetadata('secretKey', $parameters['secretKey']);
		    $this->_updateMetadata('bucket', $parameters['bucket']);
		    $this->_updateMetadata('region', $parameters['region']);
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

        /**
         * Write contents to S3 cloud storage
         *
         * @param $file
         * @param $path
         */
		public function addFile($file, $path) {
			if (!$this->_connected) {
				if (!$this->connect()) {
					$this->error = "Failed to connect to S3 service";
					return null;
				}
			}

            // Upload an object by streaming the contents of a file
            $result = $this->s3Client->putObject(array(
                'Bucket'     => $this->_bucket(),
                'Key'        => $file->code(),
                'SourceFile' => $path,
                'Metadata'   => array(
                    'Source' => 'Uploaded from Website'
                ),
                'ACL' => 'public-read'
            ));
            
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
		    unset($this->Array);
		    unset($this->credentials);
		    unset($this->s3Client);
	    }
		
        /**
         * Load contents from filesystem
         */
		public function retrieveFile($file) {
			if (!$this->_connected) {
				if (!$this->connect()) {
					$this->error = "Failed to connect to S3 service";
					return null;
				}
			}

			$tmpFile = '/tmp/s3-'.$file->code();
			app_log("Getting file ".$file->code());
			// Load contents from filesystem
			try {
				$result = $this->s3Client->getObject(array(
					'Bucket'	=> $this->_bucket(),
					'Key'		=> $file->code(),
					'SaveAs'	=> $tmpFile
				));
			} catch (\exception $e) {
				$this->error = "Failed to get file: ".$e->getMessage();
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
	}
