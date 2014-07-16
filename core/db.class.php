<?php

class DB {

	private $db_host; # ip host
	private $db_port; # port
	private $db_dbname; # database name
	private $db_user; # user
	private $db_password; # password

	private $db_connection;

	public $query;

	function __construct($db_host=null, $db_port=null, $db_dbname=null, $db_user=null, $db_password=null)
 	{
 		foreach (is_array($db_host) ? $db_host : get_defined_vars() as $arg => $value) { @$this->{$arg} = $value; }

 		if ($db_host["db_host"] && $db_host["db_user"] && $db_host["db_password"]) { $this->connect( $this->getDBString() ); }

 		$this->query["query"][0] = null;
		$this->query["query"][1] = null;
		$this->query["query"][2] = null;
		$this->query["query"][3] = null;
		$this->query["param"] = null;

	}

	public function connect( $db_string )
	{
		$this->db_connection = pg_connect( $db_string );
		return $this->db_connection;
	}

	public function connected( $db_string )
	{
		return $this->db_connection ? true : false;
	}

	private function getDBString()
	{
		$db_string = "";
		$db_array = array("host"=>null, "port"=>null, "dbname"=>null, "user"=>null, "password"=>null);
		foreach ($db_array as $arg=>$value) {
			$db_string .= $this->{"db_".$arg} ? $arg."=".$this->{"db_".$arg}." " : "";
		}
		return $db_string;
	}

	public function execute( /*array*/ )
	{
		if ( !$this->db_connection ) { return false; }
		$x = pg_query_params($this->get_query(), $this->get_param());
		return $x ? $x : pg_last_error();
	}

	public function select( $table, $columns="*")
	{
		if (is_array($columns))
		{
			$cols = array();
			foreach ($columns as $col) {
				$cols[] = $col;
			}
			$columns = join(", ", $cols);
		}

		$this->query["query"][0] = "SELECT $columns FROM $table\n ";

		//$this->query["param"] = preg_match('/\$[0-9]+/', join(" \n", $this->query)) ? $param : null;

		return $this;

	}

	public function join( $table, $join=false, $type="LEFT" )
	{
		if (!$join or !is_array($join) or !$this->query["query"][0]) {
			return $this;
		}
		
		$table_ = explode("FROM ", $this->query["query"][0]);
		$table_ = trim($table_[1]);

		foreach ($join as $tableA => $tableB) {
			$tableA = strpos($tableA, "LIKE") || strpos($tableA, "=") ? $tableA : $tableA." = "; 
			$this->query["query"][1] .= " $type JOIN $table ON $table.$tableA $table_.$tableB \n";
		}

		return $this;

	}

	public function where( $where=array(), $param = array() )
	{

		if (!$where or !is_array($where) or !$this->query["query"][0]) {
			return $this;
		}

		$table_ = explode("FROM ", $this->query["query"][0]);
		$table_ = trim($table_[1]);

		$this->query["query"][2] = "\n WHERE \n";
		foreach ($where as $a => $b) {
			$a = strpos($a, "LIKE") || strpos($a, "=") ? $a : $a." = ";
			$b = strpos($b, "AND") || strpos($b, "OR") ? $b : $b." AND ";
			$this->query["query"][2] .= ( strpos($a, ".") ? $a : $table_.".".$a )." $b \n";
		}

		$this->query["query"][2] = trim(trim($this->query["query"][2]), "AND");

		$this->query["param"] = $this->query["param"]==null && preg_match('/\$[0-9]+/', join(" \n", $this->query["query"])) ? $param : null;

		return $this;

	}

	public function limit( $limit )
	{
		$this->query["query"][3] = $limit ? "LIMIT ".$limit : null;

		return $this;
	}

	public function get_query()
	{
		return join(" \n", $this->query["query"]);
	}
	public function get_param()
	{
		return $this->query["param"] ? $this->query["param"] : array();
	}

	public function insert( $table, $values=array() )
	{
		$values_ = array();
		foreach ($values as $key=>$v) {
			$values_[] = '$'.($key+1);
		}
		$query = "INSERT INTO $table VALUES (".join(", ", $values_).")";

		return $query;
	}

}