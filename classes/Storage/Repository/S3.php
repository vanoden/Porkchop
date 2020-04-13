<?php
	namespace Storage\Repository;
    require THIRD_PARTY.'/autoload.php';
    
    use Aws\Common\Aws;
    use Aws\S3\S3Client;
    use Aws\Common\Credentials\Credentials;

	class S3 extends \Storage\Repository {
	
	    protected $aws;
	    protected $client;
	    protected $bucket;
	    protected $configuration;
	
		public function __construct($id = null) {
		
		    $this->configuration = new \Site\Configuration();
		    $this->credentials = new Credentials($this->configuration->getByKey('accessKey'), $this->configuration->getByKey('secretKey'));
		
            // Instantiate the S3 client with your AWS credentials
            $this->s3Client = S3Client::factory(array(
                'credentials' => $this->credentials
            ));
		
		
            // Create a service builder using a configuration file
            $this->aws = Aws::factory();
            
            // Get the client from the builder by namespace
            $this->client = $this->aws->get('S3');		

			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		private function _path($path = null) {
			if (isset($path)) $this->setMetadata('path',$path);
			return $this->getMetadata('path');
		}

		private function _bucket($bucket = null) {
			if (isset($bucket)) $this->setMetadata('bucket',$bucket);
			return $this->getMetadata('bucket');
		}

		public function details() {
			parent::details();
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
