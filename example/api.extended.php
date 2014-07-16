<?php

require_once("./core/api.class.php");
require_once("./core/db.class.php");

class Example extends API {
	private $db;
	function __construct()
	{
		parent::__construct( "example" );
		$this->fixGET();
		$this->db = new DB();//( /*host*/, /*port*/, /*dbname*/, /*user*/, /*password*/);
	}
	public function getUsuario( $q )
	{
		$where = array("id"=>"$1", "status"=>"$2");
		$params = array((string)$q, "true");
		$result = $this->db
					->select(
						"funcionario"
						/*,array(
							"id",
							"nome",
							"status"
						)*/
					)
					->where( $where, $params )
					->execute();

		$result = pg_fetch_assoc( $result );
		return $result != false ? $result : array();
	}

}