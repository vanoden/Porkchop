<?php
    namespace Site;

    class HeaderList Extends \BaseListClass {
        public function find($params = array()) {
            $find_objects_query = "
                SELECT  id
                FROM    site_headers
            ";
            $bind_params = array();

            $rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
            if (! $rs) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return null;
            }

            $array = array();
            while (list($id) = $rs->FetchRow()) {
                $this->_count ++;
                $header = new \Site\Header($id);
                push($array,$header);
            }
            return $array;
        }
    }
?>