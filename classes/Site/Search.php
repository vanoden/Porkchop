<?php
namespace Site;

class Search {

    private $definitions = array();

    public function search($search_string = "", $definitions = array()) {

        if (empty($search_string)) return array();
        if (empty($definitions)) $definitions = $this->definitions();
        $results = array();
        foreach ($this->definitions() as $definition)
            if (empty($definitions) || in_array($definition, $definitions)) $results = array_merge($results, $this->searchByDefinition($definition, $search_string));

        return $results;
    }

    public function definitions() {
        $list = new \Site\Search\DefinitionList();
        return $list->getDefinitionList();
    }

    private function searchByDefinition($definition, $search_string) {
        $class = "Site\\Search\\Definitions\\$definition";
        if (class_exists($class)) {
            $instance = new $class();
            return $instance->summarize($search_string);
        }
        return array();
    }
}
