<?php

class BaseXREF Extends \BaseClass {
    protected int $id = 0; // AutoIncrementing Record ID
	protected $_tableName;
    protected array $_tableUKColumns = []; // Unique Key Columns
}