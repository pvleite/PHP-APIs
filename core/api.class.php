<?php

class API {
	public $api_path = null;
	public $api_uri = null;

	public function __construct($path)
	{
		$this->api_path = $path;
		$this->api_uri = !empty($_GET["path"]) ? $_GET["path"] : "index" ;
		return $this;
	}

	public function validPath()
	{
		$path = $_SERVER["DOCUMENT_ROOT"]."/".$this->api_path."/include/".$this->api_uri.".php";
		return file_exists($path) ? $path : false;
	}

	public function fixGET()
	{
		try {
			$vars = isset($_GET["get"]) ? explode( "/", trim($_GET["get"], "/") ) : array(null);

			if (count($vars) && count($vars)%2==1 && (int)$vars[0])
			{
				$vars = explode( "/", trim("code/".$_GET["get"], "/") );
			}

			if (!$this->validPath()) {
				header("HTTP/1.0 404 Not Found");
				echo "O path \"<b>".(isset($_GET["path"])?$_GET["path"]:"N/A")."</b>\" não é uma opção válida.";
				#array_unshift($vars, $_GET["path"]);
				#$this->api_uri = "index";
				exit;
			}

			unset($_GET["get"], $_GET["path"]);
			# if (count($vars) % 2 != 0) { $vars = array_pop($vars); }
			foreach ($vars as $key => $value)
			{
				if ($key%2==1 || !$value) { continue; }
			 	$_GET[$value] = isset($vars[$key++]) ? $vars[$key++] : null;
			}
			return true;
		} catch (Exception $e) {
			return false;
		}

	}
	
	public function build( /*array*/ $data )
	{
		if ( !isset($_GET["type"]) )
		{
			$_GET["type"] = "xml";
		}
		return $this->{"build".strtoupper($_GET["type"])}( $data );
	}

	public function buildJSON( /*array*/ $data )
	{
		header('Content-Type: application/json; charset=utf-8');
		return json_encode($data);
	}

	public function buildXML( /*array*/ $data )
	{
		header('Content-Type: application/xml; charset=utf-8');
		$xml_data = new SimpleXMLElement("<?xml version=\"1.0\"?><data></data>");

		$xml_data->addAttribute('module', $this->api_uri);
		$xml_data->addAttribute('success', (int)$data);

		$this->array_to_xml($data, $xml_data);
		
		return $xml_data->asXML();
	}


	function array_to_xml($data, &$xml_data) {
	    foreach($data as $key => $value) {
	        if(is_array($value)) {
	            if(!is_numeric($key)){
	                $subnode = $xml_data->addChild("$key");
	                $this->array_to_xml($value, $subnode);
	            }
	            else
	            {
	                # $subnode = $xml_data->addChild(""); $subnode->addAttribute('id', $key);
	                $this->array_to_xml($value, $xml_data);
	            }
	        }
	        else {
	            $xml_data->addChild("$key",htmlspecialchars("$value"));
	        }
	    }
	}

	function curl( $url, $useragent )
	{
		$useragent = $useragent ? $useragent : "Athos".md5(rand(0, 1000));
		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, $useragent);
		$query = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $query;
	}
}