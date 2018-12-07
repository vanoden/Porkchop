<?
	class PorkchopModule
	{
		public $error;

		public function find($parameters = array())
		{
			if (! is_dir(MODULES))
			{
				$this->error = "No modules patch defined";
				return 0;
			}

			# Get Modules From MODULE folder
			if ($handle = opendir(MODULES))
			{
				$modules = array();
				while (false !== ($module = readdir($handle)))
				{
					if (preg_match('/^[\w\-\_]+$/',$module))
					{
						if ($parameters['name'])
						{
							if ($parameters['name'] != $module) continue;
						}

						$object = $this->details($module);
						array_push($modules,$object);
						if ($this->error) return 0;
					}
				}
				return $modules;
			}
			else
			{
				$this->error = "Error in PorkchopModule::find: Cannot view modules data";
				return 0;
			}
		}
		public function import_metadata($module)
		{
			$metadata = $this->getMetaData($module);

			# Loop through Roles
			print_r($metadata);
		}
		private function details($module)
		{
			$object = new StdClass();
			# Load Metadata
			$object = $this->getMetadata($module);
			$object->name = $module;

			$object->views = $this->views($module);
			if ($this->error)
			{
				$object->error = "Error loading views: ".$this->error;
				$this->error = '';
			}
			return $object;
		}
		public function views($module,$style = 'default')
		{
			if (! preg_match('/^[\w\-\_]+$/',$module))
			{
				$this->error = "Invalid module definition";
				return 0;
			}
			if (! preg_match('/^[\w\-\_]+$/',$style))
			{
				$this->error = "Invalid style definition";
				return 0;
			}
			if (! is_dir(MODULES."/$module"))
			{
				$this->error = "Module '$module' not found in ".MODULES."/$module";
				return 0;
			}
			if (! is_dir(MODULES."/$module/$style"))
			{
				$this->error = "Style not found";
				return 0;
			}
			if ($handle = opendir(MODULES."/$module/$style"))
			{
				$views = array();
				while (false !== ($view = readdir($handle)))
				{
					if (preg_match('/^([\w\-\_])_mc\.php$/',$view,$matches))
					{
						array_push($views,$views[1]);
					}
				}
				return $views;
			}
			else
			{
				$this->error = "Cannot view view data";
				return 0;
			}
		}
		private function getMetadata($module)
		{
			$metadata = new StdClass();
			$path = MODULES."/$module/metadata.xml";
			if (is_file($path))
			{
				require_once 'XML/Unserializer.php';
				$options = array(
					XML_SERIALIZER_OPTION_RETURN_RESULT => true,
					XML_SERIALIZER_OPTION_MODE          => 'simplexml',
				);
				$xml = new XML_Unserializer($options);
				if ($xml->unserialize($path,true,$options))
        		{
					$metadata = (object) $xml->getUnserializedData();
				}
        	}
			return $metadata;
		}
	}
?>
