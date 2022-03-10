<?php
	namespace GoogleAPI;

	class QRCode {
		private $_content;
		private $_width = 300;
		private $_height = 300;
		private $_error;
		private $_filepath;

		public function create($params = array()) {
			if (!empty($params['width'])) $this->_width = $params['width'];
			if (!empty($params['height'])) $this->_height = $params['height'];
			if (empty($params['content'])) {
				$this->error("content required");
				return false;
			}
			else $this->_content = $params['content'];
			return true;
		}
			
		public function download($path = null) {
			if (empty($this->_width) || !is_numeric($this->_width)) {
				$this->error("Valid width not set");
				return false;
			}
			if (empty($this->_height) || !is_numeric($this->_height)) {
				$this->error("Valid height not set");
				return false;
			}
			if (empty($this->_content)) {
				$this->error("Content not set");
				return false;
			}
			if (!empty($path)) {
				$this->_filepath = $path;
			}
			elseif (empty($this->_filepath)) {
				$this->_filepath = "/tmp/phpQRcode-".uniqid().".png";
			}
			$rh = fopen($this->url(),'rb');
			if (!$rh) {
				$this->error("Cannot access charts api");
				throw new Exception('Download error...');
				return false;
			}
			$wh = fopen($this->_filepath,'w+');
			if (!$wh) {
				$this->error("Cannot open local file");
				return false;
			}
			while (!feof($rh)) {
				if (fwrite($wh, fread($rh, 4096)) === FALSE) {
					return false;
				}
				flush;
			}

			fclose($rh);
			fclose($wh);

			return true;
		}

		public function filePath($path = null) {
			if (!empty($path)) $this->_filepath = $path;
			return $this->_filepath;
		}

		public function url() {
			return "https://chart.googleapis.com/chart?chs=".$this->_width."x".$this->_height."&cht=qr&chl=".urlencode($this->_content)."&choe=UTF-8";
		}

		public function error($error = null) {
			if (!empty($error)) $this->_error = $error;
			return $this->_error;
		}
	}
