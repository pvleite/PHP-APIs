<?php

class API {
	public $api_path = null;

	public function __construct($page)
	{
		$this->api_path = $page;
		return $this;
	}

	public function validPage()
	{
		return $_SERVER["DOCUMENT_ROOT"]."/".$this->api_path."/".$_GET["page"].".php";#$this->api_path ? file_exists($_SERVER["DOCUMENT_ROOT"]."/".$this->api_path."/".$_GET["page"].".php") : false;
	}

	public function fixGET( )
	{
		$vars = explode( "/", trim($_GET["get"], "/") );
		unset($_GET["get"], $_GET["page"]);
		# if (count($vars) % 2 != 0) { $vars = array_pop($vars); }
		foreach ($vars as $key => $value) {
			if ($key%2==1) { continue; }
		 	$_GET[$value] = isset($vars[$key++]) ? $vars[$key++] : null;
		}
	}
}