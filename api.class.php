<?php

require_once("./core/api.php");

class URA extends API {
	function __construct() {
		$this->fixGET();
		parent::__construct( "ura" );
	}
}

$api = new URA();
include($api->validPage());