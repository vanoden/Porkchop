<?php
namespace Site\Search;

class DefinitionList {

    protected $_definitions = array();

    public function __construct() {
        $definitions_dir = dirname(__FILE__).'/Definitions';
        $definition_files = scandir($definitions_dir);
        foreach ($definition_files as $file) {
            if (preg_match('/^[A-Z].*\.php$/', $file)) {
                $class_name = preg_replace('/\.php$/', '', $file);
                $this->_definitions[$class_name] = $class_name;
            }
        }
    }

    public function getDefinition($class_name) {
        return $this->_definitions[$class_name];
    }

    public function getDefinitionList() {
        return $this->_definitions;
    }
}