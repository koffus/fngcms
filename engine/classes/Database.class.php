<?php

class Database
{
    //use Singleton; NOT to PDO

    //protected $dbh;

    public $dbh;
    public $errorCode;
    public $errorInfo;

    private $dbhost;
    private $dbuser;
    private $dbpasswd;
    private $dbname;

    protected $queries;
    public $query_list;
    protected $error;
    protected $queryTimer;

    public function __construct()
    {
    }

    public function connect($host, $user, $pass, $db = '', $noerror = 0)
    {
        $timer = MicroTimer::instance();

        $this->dbhost = $host;
        $this->dbuser = $user;
        $this->dbpasswd = $pass;
        $this->dbname = $db;

        $this->queries = 0;
        $this->query_list = array();
        $this->error = 0;
        $this->queryTimer = (isset($timer) and (method_exists($timer, 'stop')));

        // Connect to the database
        try {
            $this->dbh = new PDO("mysql:host=" . $this->dbhost . ";charset=utf8;dbname=" . $this->dbname, $this->dbuser, $this->dbpasswd);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh->exec("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
            return true;
        } catch (\PDOException $e) {
            if (!$noerror) {
                $this->errorReport('connect', 'new PDO', $e);
                die('<h1>An Error Occurred</h1><hr />' . $e->getCode() . '!');
            }
            $this->error = 1;
            return false;
        }
    }

    public function db_select($dbname)
    {
        return $this->query("use $dbname");
    }

    public function query($sql)
    {
        $timer = MicroTimer::instance();

        if ($this->queryTimer) {
            $tX = $timer->stop(4);
        }

        $this->queries++;

        try {
            $query = $this->dbh->prepare($sql);
            $query->execute();
        } catch (\PDOException $e) {
            $this->errorReport('query', $sql, $e);
            return array();
        }

        if ($this->queryTimer) {
            $tX = '[ ' . round($timer->stop(4) - $tX, 4) . ' ] ';
        } else {
            $tX = '';
        }

        array_push($this->query_list, $tX . $sql);

        return $query;
    }

    public function select($sql, $assocMode = 1)
    {

        /*// Достаем из кеша
		if (!defined('ADMIN')) {
			$fname = 'sql' . DS . md5($sql) . '.txt';
			$result = cacheRetrieveFile($fname, '300');
			if (false !== $result) {
				return json_decode($result, true);
			}
		}*/

        try {
            $query = $this->query($sql);
        } catch (\PDOException $e) {
            $this->errorReport('select', $sql, $e);
            return array();
        }

        $result = array();

        switch ($assocMode) {
            case -1:
                $am = PDO::FETCH_NUM;
                break;
            case 1:
                $am = PDO::FETCH_ASSOC;
                break;
            case 0:
            default:
                $am = PDO::FETCH_BOTH;
        }

        if ($query) {
            while ($item = $query->fetch($am)) {
                $result[] = $item;
            }
        }

        /*// Сохраняем в кеше
		if (!defined('ADMIN'))
			cacheStoreFile($fname, json_encode($result));*/

        return $result;
    }

    public function record($sql, $assocMode = 1)
    {

        try {
            $query = $this->query($sql);
        } catch (\PDOException $e) {
            $this->errorReport('record', $sql, $e);
            return array();
        }

        switch ($assocMode) {
            case -1:
                $am = PDO::FETCH_NUM;
                break;
            case 1:
                $am = PDO::FETCH_ASSOC;
                break;
            case 0:
            default:
                $am = PDO::FETCH_BOTH;
        }

        $item = NULL;

        if (count($query))
            $item = $query->fetch($am);

        return $item;
    }

    public function result($sql)
    {
        try {
            $query = $this->query($sql);
        } catch (\PDOException $e) {
            $this->errorReport('result', $sql, $e);
            return false;
        }

        if ($query) {
            $datarow = $query->fetch();
            return $datarow[0];
        }
    }

    public function num_fields($query)
    {
        if (!$query) return false;

        return $query->columnCount();
    }

    public function field_name($query, $field_offset)
    {
        if (!$query) return false;

        return $query->getColumnMeta($field_offset)['name'];
    }

    public function field_type($query, $field_offset)
    {
        if (!$query) return false;

        return $query->getColumnMeta($field_offset)['native_type'];
    }

    public function field_len($query, $field_offset)
    {
        if (!$query) return false;

        $result = $query->getColumnMeta($field_offset)['len'];

        return $result;
    }

    public function num_rows($query)
    {
        if (!$query) return false;

        return $query->rowCount();
    }

    public function fetch_row($query)
    {
        if (!$query) return array();

        return $query->fetch(PDO::FETCH_NUM);
    }

    // check if database exists
    public function db_exists($dbname)
    {
        return (false == $this->query("SHOW DATABASES LIKE " . $this->dbh->quote($dbname))->fetchColumn(0)) ? 0 : 1;
    }

    // check if table exists
    public function table_exists($table, $forceReload = 0)
    {
        return (is_array($this->record("SHOW TABLES LIKE " . $this->dbh->quote($table)))) ? 1 : 0;
    }

    public function qcnt()
    {
        return $this->queries;
    }

    public function lastid($table = '')
    {
        if (trim($table)) {
            $row = $this->record("SHOW TABLE STATUS LIKE " . $this->dbh->quote(prefix . "_" . $table));
            return ($row['Auto_increment'] - 1);
        } else {
            return $this->dbh->lastInsertId();
        }
    }

    public function db_errno()
    {
        return $this->errorCode;
    }

    public function db_error()
    {
        return $this->errorInfo;
    }

    public function db_quote($string)
    {
        return $this->dbh->quote($string);
    }

    public function mysql_version()
    {
        return $this->dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    // Report an SQL error
    // $type	- query type
    // $query	- text of the query
    private function errorReport($type, $query, $e)
    {
        global $userROW, $config;

        $this->errorCode = $e->getCode();
        $this->errorInfo = $e->getMessage();

        if (($config['sql_error_show'] == 2) or
            (($config['sql_error_show'] == 1) and (is_array($userROW))) or
            (($config['sql_error_show'] == 0) and (is_array($userROW)) and ($userROW['status'] == 1))
        ) {
            print "<div style='font: 12px verdana; background-color: #EEEEEE; border: #ABCDEF 1px solid; margin: 1px; padding: 3px;'><span style='color: red;'>MySQL ERROR [" . $type . "]: " . $query . "</span><br/><span style=\"font: 9px arial;\">" . $e->getMessage() . '</span></div>';
        } else {
            print "<div style='font: 12px verdana; background-color: #EEEEEE; border: #ABCDEF 1px solid; margin: 1px; padding: 3px;'><span style='color: red;'>MySQL ERROR [" . $type . "]: *** (you don't have a permission to see this error) ***</span></span></div>";
        }
    }

    public function close()
    {
        $this->dbh = NULL;
    }
}
