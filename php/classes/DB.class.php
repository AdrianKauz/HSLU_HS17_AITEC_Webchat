<?php

class DB
{
	private static $instance;
	private $MySQLi;

    /*
    ================
    __construct()
    ================
    */
	private function __construct(array $dbOptions)
    {

		$this->MySQLi = @ new mysqli(	$dbOptions['db_host'],
										$dbOptions['db_user'],
										$dbOptions['db_pass'],
										$dbOptions['db_name'] );

		if (mysqli_connect_errno()) {
			throw new Exception('Database error.');
		}

		$this->MySQLi->set_charset("utf8");
	}

    /*
    ================
    init()
    ================
    */
	public static function init(array $dbOptions)
    {
		if(self::$instance instanceof self){
			return false;
		}
		
		self::$instance = new self($dbOptions);
	}

    /*
    ================
    getMySQLiObject()
    ================
    */
	public static function getMySQLiObject()
    {
		return self::$instance->MySQLi;
	}

    /*
    ================
    query()
    ================
    */
	public static function query($q)
    {
		return self::$instance->MySQLi->query($q);
	}

    /*
    ================
    esc()
    ================
    */
	public static function esc($str)
    {
			return self::$instance->MySQLi->real_escape_string($str);
	}

    /*
    ================
    commit()
    ================
    */
	public static function commit()
    {
        self::$instance->MySQLi->commit();
    }
}
?>