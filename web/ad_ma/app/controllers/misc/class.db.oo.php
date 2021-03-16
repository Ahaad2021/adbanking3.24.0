<?php

//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
//ini_set("display_errors", "on");

/**
 * This class uses PDO class to provide database connection
 *
 * @author danilo
 */
class DBC { // extends PDO

    /** @type object|null Should contain an instance of the database connection */

    private $_instance = NULL;

    /** @type string Should contain the database parameters */
    private $_db_name = "";
    private $_db_username = "";
    private $_db_password = "";
    private $_db_host = "";
    private $_db_port = "";
    private $_db_driver = "";
    private $_db_persistent = true;
    private $_db_timeout = 3;

    /**
     * Instantiation of this class
     */
    public function __construct($db_name = DB_NAME, $db_username = DB_USERNAME, $db_password = DB_PASSWORD, $db_host = DB_HOST, $db_port = DB_PORT, $db_driver = DB_DRIVER, $db_persistent = false, $db_timeout = 3) {

        $this->_db_name = $db_name;
        $this->_db_username = $db_username;
        $this->_db_password = $db_password;
        $this->_db_host = $db_host;
        $this->_db_port = $db_port;
        $this->_db_driver = $db_driver;
        $this->_db_persistent = (bool) $db_persistent;
        $this->_db_timeout = $db_timeout;
    }

    /**
     * Destroy this class
     */
    public function __destruct() {
        $this->_instance = NULL;
    }

    /**
     * Copy of this object
     */
    public function __clone() {
        
    }

    /**
     * Call this method to get singleton for database initialization
     *
     * @return PDO object
     */
    private function getInstance() {

        if ($this->_instance === NULL) {  //or if( is_null(self::$_instance) ) or if( !is_object(self::$_instance) )
            try {
                $this->_instance = new PDO($this->_db_driver . ':host=' . $this->_db_host . ';port=' . $this->_db_port . ';dbname=' . $this->_db_name, $this->_db_username, $this->_db_password, array(PDO::ATTR_PERSISTENT => $this->_db_persistent, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => $this->_db_timeout));
                // echo "IN<br />";
            } catch (PDOException $e) {
                die("PDO CONNECTION ERROR: " . $e->getMessage() . "<br/>");
            }
        }/* else {
          echo "OUT<br />";
          } */

        return $this->_instance;
    }

    /**
     * Ping database connection to check response time
     * 
     * @param string $db_host Nom de domaine / adresse IP
     * @param int $db_port Numéro du port
     * @param int $timeout Temps de connexion
     *
     * @return int $status Statut de la connexion
     */
    public static function pingConnection($db_obj, $timeout = 2) {

        try {
            $pdo_obj = new PDO('pgsql:host=' . $db_obj['app_db_host'] . ';port=' . $db_obj['app_db_port'] . ';dbname=' . $db_obj['app_db_name'], $db_obj['app_db_username'], $db_obj['app_db_password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => $timeout));

            if (is_object($pdo_obj)) {
                $connectionStatus = TRUE;
            }
        } catch (PDOException $e) {
            $connectionStatus = FALSE;
        }

        return $connectionStatus;
    }

    /**
     * Get current database error
     *
     * @return null|PDO::Error
     */
    public function getError() {

        if (self::getInstance() !== NULL) {
            $errorInfo = self::getInstance()->errorInfo();
            return sprintf("%s %s", $errorInfo[1], $errorInfo[2]);
        }

        return NULL;
    }

    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value) {
        return self::getInstance()->setAttribute($attribute, $value);
    }

    /**
     * Initiates a transaction
     *
     * @return bool
     */
    public function beginTransaction() {
        if (!self::getInstance()->inTransaction()) {
            return self::getInstance()->beginTransaction();
        }
    }

    /**
     * Checks if inside a transaction
     *
     * @return bool
     */
    public function inTransaction() {
        return self::getInstance()->inTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    public function commit() {
        if (self::getInstance()->inTransaction()) {
            return self::getInstance()->commit();
        }
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public function rollBack() {
        if (self::getInstance()->inTransaction()) {
            return self::getInstance()->rollBack();
        }
    }

    /**
     * Prepares a statement for execution and returns a statement object 
     *
     * @param string $statement A valid SQL statement for the target database server
     * @param array $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj returned  
     * @return PDOStatement
     */
    public function prepare($statement, $driver_options = false) {
        if (!$driver_options) {
            $driver_options = array();
        }
        return self::getInstance()->prepare($statement, $driver_options);
    }

    /**
     * Executes a statement and returns a statement object 
     *
     * @param string $sql A valid SQL statement for the target database server
     * @param array $params Array of one or more key=>value pairs to set bind parameters values
     * @return ResultSet
     */
    public function execute($sql, $params = "") {
        if (!$params || !is_array($params)) {
            $params = array();
        }

        $stmt = self::prepare($sql);
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param => $var) {
                $stmt->bindValue($param, $var);
            }
        }

        $stmt->execute();

        return $stmt;
    }

    /**
     * Returns the number of rows affected by the last SQL statement
     *
     * @param PDOStatement $statement A valid statement object
     * @return integer
     */
    /*
      public static function rowCount($statement) {

      return $statement->rowCount();
      }
     */

    /**
     * Prepares and executes a statement and returns the next row from a result set
     *
     * @param string $sql A valid SQL statement for the target database server
     * @param array $params Array of one or more key=>value pairs to set bind parameters values
     * @return ResultSet
     */
    public function prepareFetchRow($sql, $params = "", $fetch_style = PDO::FETCH_ASSOC) {

        $stmt = self::execute($sql, $params);

        $resultSet = $stmt->fetch($fetch_style);

        $stmt->closeCursor();

        return $resultSet;
    }

    /**
     * Prepares and executes a statement and returns a single column from the next row of a result set
     *
     * @param string $sql A valid SQL statement for the target database server
     * @param array $params Array of one or more key=>value pairs to set bind parameters values
     * @return ResultSet
     */
    public function prepareFetchColumn($sql, $params = "", $column_number = 0) {

        $stmt = self::execute($sql, $params);

        $resultSet = $stmt->fetchColumn($column_number);

        $stmt->closeCursor();

        return $resultSet;
    }

    /**
     * Prepares and executes a statement and returns an array containing all of the result set rows
     *
     * @param string $sql A valid SQL statement for the target database server
     * @param array $params Array of one or more key=>value pairs to set bind parameters values
     * @return ResultSet
     */
    public function prepareFetchAll($sql, $params = "", $fetch_style = PDO::FETCH_ASSOC) {

        $stmt = self::execute($sql, $params);

        $resultSet = $stmt->fetchAll($fetch_style);

        $stmt->closeCursor();

        return $resultSet;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function query($statement) {
        return self::getInstance()->query($statement);
    }

    /**
     * Execute query and return all rows in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchAllAssoc($statement) {
        return self::getInstance()->query($statement)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute query and return one row in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchRowAssoc($statement) {
        return self::getInstance()->query($statement)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Execute query and select one column only 
     *
     * @param string $statement
     * @return mixed
     */
    public function queryFetchColAssoc($statement) {
        return self::getInstance()->query($statement)->fetchColumn();
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     */
    public function exec($statement) {
        return self::getInstance()->exec($statement);
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string $name Name of the sequence object from which the ID should be returned.
     * @return string
     */
    public function lastInsertId($name = null) {
        return self::getInstance()->lastInsertId($name);
    }

    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public function errorInfo() {
        return self::getInstance()->errorInfo();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     * 
     * @return string 
     */
    public function errorCode() {
        return self::getInstance()->errorCode();
    }

}
