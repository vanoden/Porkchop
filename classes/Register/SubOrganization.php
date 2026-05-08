<?php

    namespace Register;

    class SubOrganization extends Organization {
        public $parent_organization_id;

        public function __construct(?int $id = null) {
            $this->_tableName = "register_sub_organizations";
            $this->_tableUKColumn = "parent_organization_id";
            $this->_addFields(array("parent_organization_id"));
            parent::__construct($id);
        }

        public function parentOrganization() {
            return new \Register\Organization($this->parent_organization_id);
        }
    }