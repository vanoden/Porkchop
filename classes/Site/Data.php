<?php
	namespace Site;
	use \ReflectionClass;
    use \ReflectionProperty;
    use \stdClass;
    
	class Data {
	
		protected $marketingContent = array();
		protected $contetnBlocks = array();		
		protected $navigation = array();
		protected $navigationItems = array();
		protected $configurations = array();
		protected $termsOfUse = array();
		protected $termsOfUseItems = array();
		
		protected $mappedClasses = array (
		    "marketingContent"  => "\Site\Page",
            "navigation"        => "\Navigation\Menu",
            "navigationItems"   => "\Navigation\Item",
            "configurations"    => "\Site\Configuration",
            "termsOfUse"        => "\Site\TermsOfUse",
            "termsOfUseItems"   => "\Site\TermsOfUseVersion",
            "contentBlocks"     => "\Content\Message"
		);
		
		/**
		 * setter for siteData class
		 *      get public properties of the class in question by $name, and create a JSON string of values to save
		 * @param string name
		 * @param mixed value
		 */
        public function __set($name, $value) {
        
            if (array_key_exists($name, $this->mappedClasses)) {
                $savedJSON = array();
                $reflectedClass = new ReflectionClass($this->mappedClasses[$name]);
                $properties = $reflectedClass->getProperties(ReflectionProperty::IS_PUBLIC);
                foreach ($value as $valueInstance) {            
                    $savedValueObject = new \stdClass();
                    foreach ($properties as $property) if (isset($valueInstance->{$property->name})) $savedValueObject->{$property->name} = $valueInstance->{$property->name};
                    if (!empty($savedValueObject)) $savedJSON[] = $savedValueObject;
                }
                
                // sub items have appended sub arrays
                if ($name == "navigationItems" || $name == "termsOfUseItems" || $name == "contentBlocks") {
                    $this->$name[] = json_encode($savedJSON, JSON_PRETTY_PRINT);
                } else {
                    $this->$name = json_encode($savedJSON, JSON_PRETTY_PRINT);
                }                
            }
            
        }
		
		public function getJSON() {
		
		    $jsonData = array();
    		foreach (get_object_vars($this) as $propertyName => $property) {
    		    if (!empty($property) && $propertyName !== "mappedClasses") $jsonData[$propertyName] = $property;
    		}
    		return json_encode($jsonData);
		}
		
		
		public function viewData() {
		
		    $jsonData = array();
    		foreach (get_object_vars($this) as $propertyName => $property) {
    		    if (!empty($property) && $propertyName !== "mappedClasses") {
        		    $jsonData[$propertyName] = $property;
    		    }
    		}
    		print_r($jsonData);
		}
				
		
		
		public function import() {
		
		}
	}
