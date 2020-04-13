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
	    public $accessKey;
	    public $secretKey;
	    public $region;
	    
		public function __construct($id = null) {

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
			
		    $this->configuration = new \Site\Configuration();
		    $this->credentials = new Credentials($this->accessKey, $this->secretKey);

            // Instantiate the S3 client with your AWS credentials
            $this->s3Client = S3Client::factory ( array (
                'credentials' => $this->credentials
            ) );

            // Create a service builder using a configuration file
            $this->aws = Aws::factory();
            
            // Get the client from the builder by namespace
            $this->client = $this->aws->get('S3');		
		}

        public function update($parameters) {
        
            // create the repo, then continue to add the custom values needed for S3 only
            parent::update($parameters);
		
		    $this->_updateMetadata('accessKey', $parameters['accessKey']);
		    $this->_updateMetadata('secretKey', $parameters['secretKey']);
		    $this->_updateMetadata('bucket', $parameters['bucket']);
		    $this->_updateMetadata('region', $parameters['region']);
		}
		
        public function add($parameters) {
        
            // create the repo, then continue to add the custom values needed for S3 only
            parent::add($parameters);
            		
		    $this->_setMetadata('accessKey', $parameters['accessKey']);
		    $this->_setMetadata('secretKey', $parameters['secretKey']);
		    $this->_setMetadata('bucket', $parameters['bucket']);
		    $this->_setMetadata('region', $parameters['region']);
		}	
		
		private function _path($path = null) {
			if (isset($path)) $this->_setMetadata('path', $path);
			return $this->getMetadata('path');
		}

		private function _bucket($bucket = null) {
			if (isset($bucket)) $this->_setMetadata('bucket', $bucket);
			return $this->getMetadata('bucket');
		}

        /**
         * Write contents to S3 cloud storage
         *
         * @param $file
         * @param $path
         */
		public function addFile($file, $path) {

            // Upload an object by streaming the contents of a file
            $result = $this->s3Client->putObject(array(
                'Bucket'     => $this->configuration->getByKey('bucket'),
                'Key'        => "/" . $file->code(),
                'SourceFile' => $path,
                'Metadata'   => array(
                    'Source' => 'Uploaded from Website'
                ),
                'ACL' => 'public-read'
            ));
            
            return $result;
		}

        /**
         * Load contents from filesystem
         */
		public function retrieveFile($file) {

			// Load contents from filesystem 
			$fh = fopen("https://" . $this->configuration->getByKey('bucket') . ".s3.amazonaws.com/" . $file->code,'rb');
			if (FALSE === $fh) {
				$this->error = "Failed to open file";
				return false;
			}

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
	}
