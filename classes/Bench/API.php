<?php
	namespace Bench;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_admin_role = 'bench operator';
			$this->_name = 'bench';
			$this->_version = '0.1.1';
			$this->_release = '2021-08-16';
			$this->_schema = new \Bench\Schema();
			parent::__construct();
		}

        ###################################################
        ### Add Bench Inventory							###
        ###################################################
        public function registerAsset() {
            if ($_REQUEST['code']) {
                # Find Requested Organization
                $asset = new \Bench\Asset('code');
                if ($asset->error) $this->app_error("Error registering asset: ".$asset->error,__FILE__,__LINE__);
            }
            else {
                $this->app_error("'code' required for new asset");
            }

            $this->response->success = 1;
            $this->response->asset = $asset;

            print $this->formatOutput($this->response);
        }

        public function testRequest() {
            $client = new \HTTP\Client();

        }

        ###################################################
        ### Load Bench Asset							###
        ###################################################
        public function getAsset() {
            if (! isset($_REQUEST['code'])) {
                $this->app_error("'code' required for new asset");
            }

            $porkchop = new \Porkchop\Session();
            if (! $porkchop->connect('test.spectrosinstruments.com'))
                $this->error("Could not connect: ".$porkchop->error());
            if (! $porkchop->authenticate())
                $this->error("Could not authenticate: ".$porkchop->error());

            $asset = new \Porkchop\Monitor\Asset($porkchop);
            if ($asset->error())
                $this->error("Could initialize asset: ".$asset->error());
            $asset->get($_REQUEST['code']);
            if ($asset->error())
                $this->error("Could not get asset: ".$asset->error());
            $obj = array(
                'id'	=> $asset->id(),
                'code'	=> $asset->code(),
                'product'	=> array(
                    'code'		=> $asset->product()->code(),
                    'name'		=> $asset->product()->name(),
                    'type'		=> $asset->product()->type(),
                    'status'	=> $asset->product()->status()
                )
            );

            $this->response->success = 1;
            $this->response->asset = $obj;

            print $this->formatOutput($this->response);
        }

        ###################################################
        ### Get Bench Inventory							###
        ###################################################
        public function findAssets() {
            $assetList = new \Bench\AssetList();
            if ($assetList->error) $this->app_error("Error initializing Asset List: ".$assetList->error,__FILE__,__LINE__);

            if ($_REQUEST['code']) {
            }
            else {
            }

            $this->response->success = 1;
            $this->response->asset = $asset;

            print $this->formatOutput($this->response);
        }

        public function pingBuildService() {
            $buildService = new \Build\API();
            $result = $buildService->ping();

            $this->response->success = 1;
            $this->response->info = $result;

            print $this->formatOutput($this->response);
        }

		public function _methods() {
			return array(
				'ping'			=> array(),
				'registerAsset'			=> array(
					'code'	=> array('required' => true),
				),
				'getAsset'			=> array(
					'code'	=> array('required' => true),
				),
				'findAssets'			=> array(
					'code'	=> array(),
				)
			);
		}
	}
