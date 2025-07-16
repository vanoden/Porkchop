<?php
	namespace Email;

	class Transport Extends \BaseClass {
		private $_provider = 'smtp';

		/** @method public Create(parameters)
		 * Factory method to create an email transport instance based on the provider.
		 * @param array $parameters Associative array containing provider and other parameters.
		 */
		public function Create($parameters = array()) {
			// Transport provide given in parameters
			if (!empty($parameters['provider'])) {
				if (!$this->validProvider($parameters['provider'])) {
					app_log("Invalid Email Transport Provider: ".$parameters['provider'],'error');
					return null;
				}
				$this->_provider = $parameters['provider'];
			}
			// Transport provider given in config
			elseif (!empty($GLOBALS['_config']->email->provider)) {
				if (!$this->validProvider($GLOBALS['_config']->email->provider)) {
					app_log("Invalid Email Transport Provider: ".$GLOBALS['_config']->email->provider,'error');
					return null;
				}
				$this->_provider = $GLOBALS['_config']->email->provider;
			}
			// Default to SMTP if no provider is specified
			else {
			   $this->_provider = 'SMTP';
			}

			// Create the transport instance based on the provider
			switch ($this->_provider) {
				case 'SMTP':
					return new \Email\Transport\SMTP($parameters);
				case 'Proxy':
					return new \Email\Transport\Proxy($parameters);
				case 'Queue':
					return new \Email\Transport\Queue($parameters);
				case 'InSite':
					return new \Email\Transport\InSite($parameters);
				case 'PearMail':
					return new \Email\Transport\PearMail($parameters);
				case 'Slack':
					return new \Email\Transport\Slack($parameters);
				default:
					app_log("Invalid Email Transport Provider: ".$this->_provider,'error');
					return null;
			}
		}

		/** @method public validProvider(string)
		 * Checks if the given provider is valid.
		 * @param string $provider The provider to check.
		 * @return bool Returns true if valid, false otherwise.
		 */
		public function validProvider($provider) {
			$valid_providers = array('SMTP', 'Proxy', 'Queue','InSite','PearMail','Slack');
			return in_array($provider, $valid_providers);
		}
	}
