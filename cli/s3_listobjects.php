<?php
        ###################################################
        ### de_version.php                                                              ###
        ### Add/Remove Specified privileges for a               ###
        ### specified role.                                                             ###
        ### A. Caravello 6/1/2022                                               ###
        ###################################################
        ### This file and its contents belong to                ###
        ### Root Seven Technologies.                                    ###
        ###################################################
        ### Modifications                                                               ###
        ###################################################

        ###################################################
        ### Load Dependencies                                                   ###
        ###################################################
        # Load Config
        require '../config/config.php';

        # Set Server Environment
        $_SERVER['HTTP_HOST'] = "localhost";
        $_SERVER['SERVER_NAME'] = $GLOBALS['_config']->site->hostname;
        $_SERVER['HTTP_USER_AGENT'] = "cron";

        # General Utilities
        require INCLUDES.'/functions.php';
        spl_autoload_register('load_class');

        # Database Abstraction
        require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

        # Debug Variables
        $_debug_queries = array();

        ###################################################
        ### Connect to Logger                                                   ###
        ###################################################
        $logger = \Site\Logger::get_instance(array('type' => APPLICATION_LOG_TYPE,'path' => APPLICATION_LOG));
        if ($logger->error()) {
                error_log("Error initializing logger: ".$logger->error());
                print "Logger error\n";
                exit;
        }
        $logger->connect();
        if ($logger->error()) {
                error_log("Error initializing logger: ".$logger->error());
                print "Logger error\n";
                exit;
        }

        ###################################################
        ### Initialize Common Objects                                   ###
        ###################################################
        # Connect to Database
        $_database = NewADOConnection('mysqli');
        if ($GLOBALS['_config']->database->master->port) $_database->port = $GLOBALS['_config']->database->master->port;
        $_database->Connect(
                $GLOBALS['_config']->database->master->hostname,
                $GLOBALS['_config']->database->master->username,
                $GLOBALS['_config']->database->master->password,
                $GLOBALS['_config']->database->schema
        );
        if ($_database->ErrorMsg()) {
                print "Error connecting to database:<br>\n";
                print $_database->ErrorMsg();
                $logger->write("Error connecting to database: ".$_database->ErrorMsg(),'error');
                exit;
        }
        $logger->write("Database Initiated",'trace');

        ###################################################
        ### Main Procedure                                                              ###
        ###################################################
        $GLOBALS['_SESSION_'] = new \Site\Session();
        $GLOBALS['_SESSION_']->elevate();

        $db_service = new \Database\Service();
        $db_version = $db_service->version();
        print "Version: ".$db_version."\n";
        print "Supports password(): ";
        if ($db_service->supports_password()) print "Yes\n";
        else print "No\n";

        require THIRD_PARTY.'/autoload.php';
echo THIRD_PARTY."\n";
        use Aws\Common\Aws;
        use Aws\S3\S3Client;
        use Aws\Common\Credentials\Credentials;
        use Aws\S3\Exception\S3Exception;

        $s3client = new \Aws\S3\S3Client(['region' => 'us-east-1', 'version' => 'latest']);

        try {
                $contents = $s3client->listObjects([
                        'Bucket'        => 'spectros-test-deploy'
                ]);
                foreach ($contents['Contents'] as $content) {
                        echo $content['Key']."\n";
                }
        }
        catch (Exception $exception) {
                echo "Failed: ".$exception->getMessage();
                exit;
        }
        #$repositoryFactory = new \Storage\RepositoryFactory();
        #$repository = $repositoryFactory->create('s3',14);
        #print_r($repository);
        #$repository->listFiles();