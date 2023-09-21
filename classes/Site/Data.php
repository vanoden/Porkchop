<?php

	namespace Site;
	use \ReflectionClass;
    use \ReflectionProperty;
    use \stdClass;
    
	class Data {
	
	    // currently set properties of nested JSON to export, import
		protected $marketingContent = array();
		protected $navigation = array();
		protected $configurations = array();
		protected $termsOfUse = array();
		
		// currently mapped classes to generate JSON from public properties
		protected $mappedClasses = array (
		    "marketingContent"          => "\Site\Page",
		    "marketingContentMetaData"  => "\Site\Page\Metadata",
            "navigation"                => "\Navigation\Menu",
            "navigationItems"           => "\Navigation\Item",
            "configurations"            => "\Site\Configuration",
            "termsOfUse"                => "\Site\TermsOfUse",
            "termsOfUseVersions"        => "\Site\TermsOfUseVersion",
            "contentBlocks"             => "\Content\Message"
		);
        
		/**
		 * set single level configurations JSON array for export
		 * 
		 * @param $values, values in database of object
		 */	
        public function setConfigurations($values) {
            $this->configurations = $this->getPublicFieldsByArray('configurations', $values);
        }
        
        /**
		 * set nested level JSON array of navigation items for export of DB objects and sub-objects
		 * 
		 * @param $values, current values in database
		 */
		 public function setNavigationItems($navigationData) {
		 
            if (isset($navigationData['menus']) && isset($navigationData['navigationItems'])) {
                $jsonData = array();
                foreach ($navigationData['menus'] as $menuItem) 
                    $jsonData['menu_id_'.$menuItem->id] = array('menuItem' => $this->getPublicFieldValues('navigation', $menuItem), 'navigationItems' => array());
                foreach ($navigationData['navigationItems'] as $menuItems) 
                    foreach ($menuItems as $menuItem)
                        $jsonData['menu_id_'.$menuItem->menu_id]['navigationItems'][] = $this->getPublicFieldValues('navigationItems', $menuItem);    
                $this->navigation = $jsonData;   
            }
        }
        
        /**
		 * set nested level JSON array of terms of use items for export of DB objects and sub-objects
		 * 
		 * @param $values, current values in database
		 */
		 public function setTermsOfUseItems($termsOfUseData) {
            if (isset($termsOfUseData['termsOfUse']) && isset($termsOfUseData['termsOfUseVersions'])) {
                $jsonData = array();
                foreach ($termsOfUseData['termsOfUse'] as $touItem) 
                    $jsonData['tou_id_'.$touItem->id] = array('termsOfUseItem' => $this->getPublicFieldValues('termsOfUse', $touItem), 'termsOfUseVersions' => array());
                foreach ($termsOfUseData['termsOfUseVersions'] as $touVersions) 
                    $jsonData['tou_id_'.$touVersions->tou_id]['termsOfUseVersions'][] = $this->getPublicFieldValues('termsOfUseVersions', $touVersions);    
                $this->termsOfUse = $jsonData;   
            }
        }
        
        
        /**
		 * set nested level JSON array of terms of use items for export of DB objects and sub-objects
		 * 
		 * @param $values, current values in database
		 */
		 public function setMarketingContent($marketingContentData) {
            if (isset($marketingContentData['pages']) && isset($marketingContentData['pageMetaData']) && isset($marketingContentData['contentBlocks'])) {
                $jsonData = array();
                foreach ($marketingContentData['pages'] as $page) {
					$jsonData['page_id_'.$page->id] = array('page' => $this->getPublicFieldValues('marketingContent', $page), 'pageMetaData' => array());
					foreach ($marketingContentData['contentBlocks'] as $contentBlock) 
						if ($contentBlock->target == $page->index) $jsonData['page_id_'.$page->id]['contentBlocks'][] = $this->getPublicFieldValues('contentBlocks', $contentBlock);			
				}
                foreach ($marketingContentData['pageMetaData'] as $pageMetaDataItem) 
                    $jsonData['page_id_'.$pageMetaDataItem->page_id]['pageMetaData'][] = $this->getPublicFieldValues('marketingContentMetaData', $pageMetaDataItem);    
                    
                $this->marketingContent = $jsonData;   
            }
        }        
        
		/**
		 * get public properties ONLY of given mapped class tied to ORM objects
		 *    to save as JSON data for export
		 * 
		 * @param $mappedClassName, mapped class reference for object
		 * @param $objectArray, populated objects as an array
		 */	
        protected function getPublicFieldsByArray($mappedClassName, $objectArray) {
            $objArray = array();
            foreach ($objectArray as $objectInstance) $objArray[] = $this->getPublicFieldValues($mappedClassName, $objectInstance);
            return $objArray;
        }
        
		/**
		 * get public properties ONLY of given mapped class tied to ORM objects
		 *    to save as JSON data for export
		 * 
		 * @param $mappedClassName, mapped class reference for object
		 * @param $object, populated object
		 */	
        protected function getPublicFieldValues($mappedClassName, $object) {
        
            $reflectedClass = new ReflectionClass($this->mappedClasses[$mappedClassName]);
            $savedValueObject = new \stdClass();
            foreach ($reflectedClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) 
                if (isset($object->{$property->name})) $savedValueObject->{$property->name} = $object->{$property->name};
                
            return $savedValueObject;
        }
        
		/**
		 * get JSON data for export
		 */		
		public function getJSON() {
		    $jsonData = array();
    		foreach (get_object_vars($this) as $propertyName => $property) 
    		    if (!empty($property) && $propertyName !== "mappedClasses") $jsonData[$propertyName] = $property;
    		return json_encode($jsonData);
		}
		
		/**
		 * view the data as an array for debugging
		 */
		public function viewData() {
		    $jsonData = array();
    		foreach (get_object_vars($this) as $propertyName => $property) 
    		    if (!empty($property) && $propertyName !== "mappedClasses") $jsonData[$propertyName] = $property;
    		return print_r($jsonData, true);
		}
		
		public function import() {
		
		}
	}
