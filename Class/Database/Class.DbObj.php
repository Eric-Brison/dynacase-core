<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * This class is a generic DB Class that can be used to create objects
 * based on the description of a DB Table.
 *
 * @author Anakeen
 * @version $Id: Class.DbObj.php,v 1.58 2008/12/29 17:05:38 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.Log.php');
include_once ('Lib.Common.php');

$CLASS_DBOBJ_PHP = '$Id: Class.DbObj.php,v 1.58 2008/12/29 17:05:38 eric Exp $';
/**
 * This class is a generic DB Class that can be used to create objects
 * based on the description of a DB Table. More Complex Objects will
 * inherit from this basic Class.
 *
 */
class DbObj
{
    /**
     * the database connection resource
     * @var resource
     */
    var $dbid = - 1;
    /**
     * coordinates to access to database
     * @var string
     */
    var $dbaccess = '';
    /**
     * array of SQL fields use for the object
     * @var array
     */
    var $fields = array(
        '*'
    );
    /**
     * name of the SQL table
     * @var string
     */
    var $dbtable = '';
    
    public $id_fields;
    
    var $criterias = array();
    /**
     * array of other SQL fields, not in attribute of object
     * @var array
     */
    var $sup_fields = array();
    var $sup_where = array();
    var $sup_tables = array();
    var $fulltextfields = array();
    /**
     * sql field to order
     * @var string
     */
    var $order_by = "";
    /**
     * indicates if fields has been affected
     * @var string
     * @see Affect()
     */
    var $isset = false; // indicate if fields has been affected (call affect methods)
    static $savepoint = array();
    static $lockpoint = array();
    static private $masterLock = false;
    static $sqlStrict = null;
    /**
     * @var string error message
     */
    public $msg_err = '';
    /**
     * @var int
     */
    public $err_code = '';
    /**
     * @var resource
     */
    public $res = '';
    /**
     * @var bool
     */
    public $debug = false;
    public $sqlcreate;
    public $sqlinit;
    /**
     * @var Log DbObj Log Object
     */
    public $log;
    //----------------------------------------------------------------------------
    
    /**
     * Database Object constructor
     *
     *
     * @param string $dbaccess database specification
     * @param int|array $id identifier of the object
     * @param array $res array of result issue to QueryDb {@link QueryDb::Query()}
     * @param resource $dbid the database connection resource
     * @return bool false if error occured
     */
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        if (!$dbaccess) $dbaccess = getDbAccess();
        $this->dbaccess = $dbaccess;
        $this->init_dbid();
        //global ${$this->oname};
        $this->log = new Log("", "DbObj", $this->dbtable);
        
        if ($this->dbid == 0) {
            $this->dbid = - 1;
        }
        
        $this->selectstring = "";
        // SELECTED FIELDS
        reset($this->fields);
        foreach ($this->fields as $k => $v) {
            $this->selectstring = $this->selectstring . $this->dbtable . "." . $v . ",";
            $this->$v = "";
        }
        
        reset($this->sup_fields);
        foreach ($this->sup_fields as $k => $v) {
            $this->selectstring = $this->selectstring . "" . $v . ",";
            $this->$v = "";
        }
        $this->selectstring = substr($this->selectstring, 0, strlen($this->selectstring) - 1);
        if (self::$sqlStrict === null) {
            self::$sqlStrict = (getParam('CORE_SQLSTRICT') != 'no');
        }
        // select with the id
        if (($id != '') || (is_array($id)) || (!isset($this->id_fields[0]))) {
            $ret = $this->Select($id);
            
            return ($ret);
        }
        // affect with a query result
        if (is_array($res)) {
            $this->Affect($res);
        }
        
        return TRUE;
    }
    /**
     * Select object from its fields
     * if fields has more then one variable, need to use an array
     * @param int|array $id
     * @return bool|string
     */
    function Select($id)
    {
        if (!$id) return false;
        if ($this->dbid == - 1) return FALSE;
        
        $msg = $this->PreSelect($id);
        if ($msg != '') return $msg;
        
        if ($this->dbtable == '') {
            return ("error : No Tables");
        }
        $fromstr = "{$this->dbtable}";
        if (is_array($this->sup_tables)) {
            reset($this->sup_tables);
            foreach ($this->sup_tables as $k => $v) {
                $fromstr.= "," . $v;
            }
        }
        $sql = "select {$this->selectstring} from {$fromstr} ";
        
        $count = 0;
        if (is_array($id)) {
            $count = 0;
            $wherestr = " where ";
            reset($this->id_fields);
            foreach ($this->id_fields as $k => $v) {
                if ($count > 0) {
                    $wherestr = $wherestr . " AND ";
                }
                $wherestr = $wherestr . "( " . $this->dbtable . "." . $v . "=E'" . pg_escape_string($id[$k]) . "' )";
                $count = $count + 1;
                //$this->$v = $id[$k];
                
            }
        } else {
            if (isset($this->id_fields[0])) {
                $k = $this->id_fields[0];
                //$this->$k = $id;
                $wherestr = "where " . $this->dbtable . "." . $this->id_fields[0] . "=E'" . pg_escape_string($id) . "'";
            } else {
                $wherestr = "";
            }
        }
        if (is_array($this->sup_where)) {
            reset($this->sup_where);
            foreach ($this->sup_where as $k => $v) {
                $wherestr = $wherestr . " AND ";
                $wherestr = $wherestr . "( " . $v . " )";
                $count = $count + 1;
            }
        }
        
        $sql = $sql . " " . $wherestr;
        
        $resultat = $this->exec_query($sql);
        
        if ($this->numrows() > 0) {
            $res = $this->fetch_array(0);
            $this->Affect($res);
        } else {
            return FALSE;
        }
        $msg = $this->PostSelect($id);
        if ($msg != '') return $msg;
        return TRUE;
    }
    /**
     * get all values in indexed array
     * @return array
     */
    function getValues()
    {
        $r = array();
        foreach ($this->fields as $k => $v) {
            $r[$v] = $this->$v;
        }
        return $r;
    }
    /**
     * affect object specific column values from this item
     * use only when object is already affected
     * @param array $fields sql field to affect
     * @return bool true if OK false else
     */
    function affectColumn($fields, $reset = true)
    {
        if ($this->dbid == - 1) return FALSE;
        
        if (!$this->isAffected()) return false;
        if (count($fields) == 0) return true;
        if ($this->dbtable == '') {
            return ("error : No Tables");
        }
        $fromstr = $this->dbtable;
        $w = array();
        foreach ($this->id_fields as $id) {
            $w[] = "($id = E'" . pg_escape_string($this->$id) . "') ";
        }
        $sqlwhere = implode("and", $w);
        $sqlselect = implode(",", $fields);
        
        $sql = "select $sqlselect from $fromstr where $sqlwhere";
        
        $resultat = $this->exec_query($sql);
        
        if ($this->numrows() > 0) {
            $res = $this->fetch_array(0);
            $this->affect($res, false, $reset);
        } else {
            return FALSE;
        }
        return TRUE;
    }
    /**
     * affect object with a set of values
     * @param array $array indexed array of values , index if the column attribute
     */
    function affect($array, $more = false, $reset = true)
    {
        foreach ($array as $k => $v) {
            if (!is_integer($k)) {
                $this->$k = $v;
            }
        }
        $this->Complete();
        $this->isset = true;
    }
    /**
     * verify that the object exists in database
     * test if object has fields id set
     * if true values of the object has been set
     * @æpi test if object if affected
     * @see affect
     * @return bool
     */
    function isAffected()
    {
        return $this->isset;
    }
    /**
     * @see affect
     */
    function Complete()
    {
        // This function should be replaced by the Child Class
        
    }
    /** 
     * Method use before Add method
     * This method should be replaced by the Child Class
     * if return error message, modify is aborded
     * @return string error message, if no error empty string
     * @see Add()
     */
    public function preInsert()
    {
        // This function should be replaced by the Child Class
        return '';
    }
    /** 
     * Method use after Add method
     * This method should be replaced by the Child Class
     *
     * @return string error message, if no error empty string, if message
     * error not empty the Add method is not completed
     * @see Add()
     */
    public function postInsert()
    {
        // This function should be replaced by the Child Class
        return '';
    }
    /** 
     * Method use before Modify method
     * This method should be replaced by the Child Class
     * if return error message, modify is aborded
     *
     * @return string error message, if no error empty string
     * @see Modify()
     */
    public function preUpdate()
    {
        // This function should be replaced by the Child Class
        return '';
    }
    /** 
     * Method use after Modify method
     * This method should be replaced by the Child Class
     *
     * @return string error message, if no error empty string, if message
     * error not empty the Modify method is not completed
     * @see Modify()
     */
    public function postUpdate()
    {
        return '';
        // This function should be replaced by the Child Class
        
    }
    /**
     * if return error message, deletion is aborded
     * @see delete
     * @return string
     */
    public function preDelete()
    {
        return '';
        // This function should be replaced by the Child Class
        
    }
    /**
     * Method use after delete method
     * @see delete
     * @return string
     */
    public function postDelete()
    {
        return '';
        // This function should be replaced by the Child Class
        
    }
    /**
     * Method use before select method
     * @param mixed $id the id use by select
     * @see select
     * @return string
     */
    public function preSelect($id)
    {
        // This function should be replaced by the Child Class
        return '';
    }
    /**
     * Method use after select method
     * @param mixed $id the id use by select
     * @see select
     * @return string
     */
    public function postSelect($id)
    {
        return '';
        // This function should be replaced by the Child Class
        
    }
    /**
     * Add the object to the database
     * @param bool $nopost PostInsert method not apply if true
     * @param bool $nopre PreInsert method not apply if true
     * @return string error message, if no error empty string
     * @see PreInsert()
     * @see PostInsert()
     */
    function Add($nopost = false, $nopre = false)
    {
        if ($this->dbid == - 1) return FALSE;
        $msg = '';
        if (!$nopre) $msg = $this->PreInsert();
        if ($msg != '') return $msg;
        
        $sfields = implode(",", $this->fields);
        $sql = "insert into " . $this->dbtable . "($sfields) values (";
        
        $valstring = "";
        reset($this->fields);
        foreach ($this->fields as $k => $v) {
            $valstring = $valstring . $this->lw(isset($this->$v) ? $this->$v : '') . ",";
        }
        $valstring = substr($valstring, 0, strlen($valstring) - 1);
        $sql = $sql . $valstring . ")";
        // requery execution
        $msg_err = $this->exec_query($sql);
        
        if ($msg_err != '') {
            return $msg_err;
        }
        $this->isset = true;
        if (!$nopost) $msg = $this->PostInsert();
        return $msg;
    }
    /** 
     * Save the object to the database
     * @param bool $nopost PostUpdate() and method not apply if true
     * @param string $sfields only this column will ne updated if empty all fields
     * @param bool $nopre PreUpdate() method not apply if true
     * @return string error message, if no error empty string
     * @see PreUpdate()
     * @see PostUpdate()
     */
    public function modify($nopost = false, $sfields = "", $nopre = false)
    {
        $msg = '';
        if ($this->dbid == - 1) return FALSE;
        if (!$nopre) $msg = $this->PreUpdate();
        if ($msg != '') return $msg;
        $sql = "update " . $this->dbtable . " set ";
        
        $nb_keys = 0;
        
        if (!is_array($sfields)) $fields = $this->fields;
        else {
            $fields = $sfields;
            foreach ($this->id_fields as $k => $v) $fields[] = $v;
        }
        
        $wstr = "";
        foreach ($this->id_fields as $k => $v) {
            $notset[$v] = "Y";
            $nb_keys++;
            $val = pg_escape_string($this->$v);
            $wstr = $wstr . " " . $v . "=E'" . $val . "' AND";
        }
        
        $setstr = "";
        foreach ($fields as $k => $v) {
            if (!isset($notset[$v])) {
                $setstr = $setstr . " " . $v . "=" . $this->lw(isset($this->$v) ? $this->$v : '') . ",";
            }
        }
        $setstr = substr($setstr, 0, strlen($setstr) - 1);
        $wstr = substr($wstr, 0, strlen($wstr) - 3);
        $sql.= $setstr;
        if ($nb_keys > 0) {
            $sql.= " where " . $wstr . ";";
        }
        
        $msg_err = $this->exec_query($sql);
        // sortie
        if ($msg_err != '') {
            return $msg_err;
        }
        
        if (!$nopost) $msg = $this->PostUpdate();
        
        return $msg;
    }
    /**
     * Delete the object on the database
     * @param bool $nopost PostUpdate() and method not apply if true
     * @return string error message, if no error empty string
     */
    public function delete($nopost = false)
    {
        $msg = $this->PreDelete();
        if ($msg != '') return $msg;
        $wherestr = "";
        $count = 0;
        
        reset($this->id_fields);
        foreach ($this->id_fields as $k => $v) {
            if ($count > 0) {
                $wherestr = $wherestr . " AND ";
            }
            $wherestr = $wherestr . "( " . $v . "=E'" . pg_escape_string($this->$v) . "' )";
            $count++;
        }
        // suppression de l'enregistrement
        $sql = "delete from " . $this->dbtable . " where " . $wherestr . ";";
        
        $msg_err = $this->exec_query($sql);
        
        if ($msg_err != '') {
            return $msg_err;
        }
        
        if (!$nopost) $msg = $this->PostDelete();
        return $msg;
    }
    /** 
     * Add several objects to the database
     * no post neither preInsert are called
     * @param bool $nopost PostInsert method not apply if true
     * @return string error message, if no error empty string
     * @see PreInsert()
     * @see PostInsert()
     */
    function Adds(&$tcopy, $nopost = false)
    {
        if ($this->dbid == - 1) return FALSE;
        if (!is_array($tcopy)) return FALSE;
        $msg = '';
        $sfields = implode(",", $this->fields);
        $sql = "copy " . $this->dbtable . "($sfields) from STDIN;\n";
        
        $trow = array();
        foreach ($tcopy as $kc => $vc) {
            $trow[$kc] = "";
            foreach ($this->fields as $k => $v) {
                $trow[$kc].= "" . ((isset($vc[$v])) ? $vc[$v] : ((($this->$v) != '') ? $this->$v : '\N')) . "\t";
                //$trow[$kc][$k] .= ((isset($vc[$v]))?$vc[$v]:$this->$v);
                
            }
            $trow[$kc] = substr($trow[$kc], 0, -1);
        }
        // query execution
        $berr = pg_copy_from($this->dbid, $this->dbtable, $trow, "\t");
        
        if (!$berr) return sprintf(_("DbObj::Adds error in multiple insertion"));
        
        if (!$nopost) $msg = $this->PostInsert();
        return $msg;
    }
    function lw($prop)
    {
        $result = (($prop == '') && ($prop !== 0)) ? "null" : "E'" . pg_escape_string($prop) . "'";
        return $result;
    }
    function CloseConnect()
    {
        pg_close("$this->dbid");
        return TRUE;
    }
    
    function Create($nopost = false)
    {
        $msg = "";
        if (isset($this->sqlcreate)) {
            // step by step
            if (is_array($this->sqlcreate)) {
                foreach ($this->sqlcreate as $k => $sqlquery) {
                    $msg.= $this->exec_query($sqlquery, 1);
                }
            } else {
                $sqlcmds = explode(";", $this->sqlcreate);
                foreach ($sqlcmds as $k => $sqlquery) {
                    $msg.= $this->exec_query($sqlquery, 1);
                }
            }
            $this->log->debug("DbObj::Create : " . print_r($this->sqlcreate, true));
        }
        if (isset($this->sqlinit)) {
            $msg = $this->exec_query($this->sqlinit, 1);
            $this->log->debug("Init : {$this->sqlinit}");
        }
        if ($msg != '') {
            $this->log->info("DbObj::Create $msg");
            return $msg;
        }
        if (!$nopost) $this->PostInit();
        return ($msg);
    }
    
    public function postInit()
    {
    }
    
    function init_dbid()
    {
        
        if ($this->dbaccess == "") {
            // don't test if file exist or must be searched in include_path
            $this->dbaccess = getDbAccess();
        }
        $this->dbid = getDbid($this->dbaccess);
        if ($this->dbid == 0) error_log(__METHOD__ . "null dbid");
        return $this->dbid;
    }
    
    protected function tryCreate()
    {
        
        $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);
        
        $action_needed = "";
        
        if ($this->err_code != "") {
            // http://www.postgresql.org/docs/8.3/interactive/errcodes-appendix.html
            switch ($this->err_code) {
                case "42P01":
                    // UNDEFINED TABLE
                    $action_needed = "create";
                    break;

                case "42703":
                    // UNDEFINED COLUMN
                    $action_needed = "update";
                    break;

                case "42P07":
                    // DUPLICATE TABLE
                    $action_needed = "none";
                    break;

                default:
                    break;
            }
            // error_log(__METHOD__ . sprintf('[%s]%s {%s} - %s', $this->msg_err, $this->err_code, $action_needed, $this->dbtable));
            //print_r2(getDebugStack());print $sql;
            //trigger_error('<pre>'.$this->msg_err."\n".$sql.'</pre>');
            
        }
        
        $originError = $this->msg_err;
        switch ($action_needed) {
            case "create":
                $st = $this->Create();
                if ($st == "") {
                    return true;
                } else {
                    
                    $err = ErrorCode::getError('DB0003', $this->dbtable, $st);
                    $this->msg_err = $originError . "\n" . $err;
                }
                break;

            case "update":
                return false;
                // no more auto update
                /*
                $st = $this->Update();
                if ($st == "") {
                    return true;
                } else {
                    
                    $err = ErrorCode::getError('DB0004', $this->dbtable, $st);
                    $this->msg_err = $originError . "\n" . $err;
                }
                */
                break;

            case "none":
                $this->msg_err = "";
                return true;
                break;

            default:
                break;
        }
        return false;
    }
    /**
     * Send a request to database
     * @param string $sql the query
     * @param int $lvl level set to 0 (internal purpose only)
     * @param bool $prepare set to true to use pg_prepare, restrict to use single query
     * @throw Dcp\Db\Exception if query fail
     * @return string error message if not strict mode
     */
    function exec_query($sql, $lvl = 0, $prepare = false)
    {
        global $SQLDELAY, $SQLDEBUG;
        
        if ($sql == "") return '';
        $sqlt1 = '';
        if ($SQLDEBUG) $sqlt1 = microtime(); // to test delay of request
        $this->init_dbid();
        $this->log->debug("exec_query : $sql");
        $this->msg_err = $this->err_code = '';
        if ($prepare) {
            if (pg_send_prepare($this->dbid, '', $sql) === false) {
                $this->msg_err = ErrorCode::getError('DB0006', pg_last_error($this->dbid));
                error_log(__METHOD__ . " " . $this->msg_err);
                return $this->msg_err;
            }
            $this->res = pg_get_result($this->dbid);
            $err = pg_result_error($this->res);
            if ($err) {
                $this->msg_err = ErrorCode::getError('DB0005', $err);
                $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);
            }
            
            if ($this->msg_err == "") {
                if (pg_send_execute($this->dbid, '', array()) === false) {
                    
                    $this->msg_err = ErrorCode::getError('DB0007', pg_last_error($this->dbid));
                    $this->setError($sql);
                    
                    return $this->msg_err;
                }
                $this->res = pg_get_result($this->dbid);
                $err = pg_result_error($this->res);
                if ($err) {
                    $this->msg_err = ErrorCode::getError('DB0002', $err);
                    $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);
                }
            }
        } else {
            if (pg_send_query($this->dbid, $sql) === false) {
                $this->msg_err = ErrorCode::getError('DB0008', pg_last_error($this->dbid));
                
                $this->setError($sql);
                return $this->msg_err;
            }
            $this->res = pg_get_result($this->dbid);
            while (pg_get_result($this->dbid)); // purge following queries
            $err = pg_result_error($this->res);
            if ($err) {
                $this->msg_err = ErrorCode::getError('DB0001', $err);
                $this->err_code = pg_result_error_field($this->res, PGSQL_DIAG_SQLSTATE);
            }
        }
        
        if ($this->msg_err && ($lvl == 0)) {
            $orierr = $this->msg_err;
            try {
                if ($this->tryCreate()) {
                    // redo the query if create table is done
                    $this->msg_err = $this->exec_query($sql, 1, $prepare);
                }
            }
            catch(Exception $e) {
                $this->msg_err = $orierr;
            }
        }
        if ($this->msg_err != "") {
            $this->log->warning("exec_query :" . $sql);
            $this->log->warning("PostgreSQL Error : " . $this->msg_err);
            //trigger_error('<pre>'.$this->msg_err."\n".$sql.'</pre>');
            // throw new Exception($this->msg_err);
            $this->setError($sql);
        }
        
        if ($SQLDEBUG) {
            global $TSQLDELAY;
            $SQLDELAY+= microtime_diff(microtime() , $sqlt1); // to test delay of request
            $TSQLDELAY[] = array(
                "t" => sprintf("%.04f", microtime_diff(microtime() , $sqlt1)) ,
                "s" => str_replace(array(
                    "from",
                    'where'
                ) , array(
                    "\nfrom",
                    "\nwhere"
                ) , $sql) ,
                "st" => getDebugStack(1)
            );
        }
        
        return ($this->msg_err);
    }
    /**
     * number of return rows after exec_query
     * @see exec_query
     * @return int
     */
    public function numrows()
    {
        if ($this->msg_err == "") {
            return (pg_num_rows($this->res));
        } else {
            return (0);
        }
    }
    
    public function fetch_array($c, $type = PGSQL_ASSOC)
    {
        
        return (pg_fetch_array($this->res, $c, $type));
    }
    
    public function update()
    {
        $err = ErrorCode::getError('DB0009', $this->dbtable);
        
        return $err;
    }
    
    public function setError($moreerr = '')
    {
        if ($moreerr == '') $err = $this->msg_err;
        $err = $this->msg_err . "\n" . $moreerr . "\n";
        
        logDebugStack(2);
        error_log($err);
        if (self::$sqlStrict) {
            throw new Dcp\Db\Exception($err);
        }
    }
    /**
     * @deprecated not used now
     * @return string
     */
    public function autoUpdate()
    {
        print $this->msg_err;
        print (" - need update table " . $this->dbtable);
        $this->log->error("need Update table " . $this->dbtable);
        
        $this->log->info("Update table " . $this->dbtable);
        // need to exec altering queries
        $objupdate = new DbObj($this->dbaccess);
        // ------------------------------
        // first : save table to updated
        $dumpfile = uniqid(getTmpDir() . "/" . $this->dbtable);
        $err = $objupdate->exec_query("COPY " . $this->dbtable . "  TO '" . $dumpfile . "'");
        $this->log->info("Dump table " . $this->dbtable . " in " . $dumpfile);
        
        if ($err != "") return ($err);
        // ------------------------------
        // second : rename table to save data
        //$err = $objupdate-> exec_query("CREATE  TABLE ".$this->dbtable."_old ( ) INHERITS (".$this->dbtable.")",1);
        //$err = $objupdate-> exec_query("COPY ".$this->dbtable."_old FROM '".$dumpfile."'",				1 );
        $err = $objupdate->exec_query("ALTER TABLE " . $this->dbtable . " RENAME TO " . $this->dbtable . "_old", 1);
        
        if ($err != "") return ($err);
        // remove index : will be recreated in the following step (create)
        $err = $this->exec_query("select indexname from pg_indexes where tablename='" . $this->dbtable . "_old'", 1);
        $nbidx = $this->numrows();
        for ($c = 0; $c < $nbidx; $c++) {
            
            $row = $this->fetch_array($c, PGSQL_ASSOC);
            $err = $objupdate->exec_query("DROP INDEX " . $row["indexname"], 1);
        }
        // --------------------------------------------
        // third : Create new table with new attributes
        $this->Create(true);
        // ---------------------------------------------------
        // 4th : copy compatible data from old table to new table
        $first = true;
        $fields = '';
        $this->exec_query("SELECT * FROM " . $this->dbtable . "_old");
        $nbold = $this->numrows();
        for ($c = 0; $c < $nbold; $c++) {
            
            $row = $this->fetch_array($c, PGSQL_ASSOC);
            
            if ($first) {
                // compute compatible fields
                $inter_fields = array_intersect(array_keys($row) , $this->fields);
                reset($this->fields);
                $fields = "(";
                foreach ($inter_fields as $k => $v) {
                    $fields.= $v . ",";
                }
                $fields = substr($fields, 0, strlen($fields) - 1); // remove last comma
                $fields.= ")";
                $first = false;
            }
            // compute compatible values
            $values = "(";
            reset($inter_fields);
            foreach ($inter_fields as $k => $v) {
                $values.= "E'" . pg_escape_string($row[$v]) . "',";
            }
            $values = substr($values, 0, strlen($values) - 1); // remove last comma
            $values.= ")";
            // copy compatible values
            $sqlInsert = sprintf("INSERT INTO %s %s VALUES ", $this->dbtable, $fields, $values);
            $err = $objupdate->exec_query($sqlInsert, 1);
            if ($err != "") return ($err);
        }
        // ---------------------------------------------------
        // 5th :delete old table (has been saved before - dump file)
        $err = $objupdate->exec_query("DROP TABLE " . $this->dbtable . "_old", 1);
        
        return ($err);
    }
    /**
     * set a database transaction save point
     * @param string $point point identifier
     * @throws \Dcp\Exception
     * @return string error message
     */
    public function savePoint($point)
    {
        if (!$this->dbid) {
            $err = sprintf("dbid is null cannot save point %s", $point);
            error_log(__METHOD__ . ":$err");
            return $err;
        }
        if ($this->debug) error_log('[DBG]' . 'BEFORE' . __METHOD__ . $this->dbid);
        $err = '';
        
        $idbid = intval($this->dbid);
        
        if (empty(self::$savepoint[$idbid])) {
            self::$savepoint[$idbid] = array(
                $point
            );
            $err = $this->exec_query("begin");
            if ($this->debug) error_log('[DBG]' . __METHOD__ . "add(1) $point");
        } else {
            self::$savepoint[$idbid][] = $point;
            if ($this->debug) error_log('[DBG]' . __METHOD__ . "add(2) $point");
        }
        
        if (!$err) {
            $err = $this->exec_query(sprintf('savepoint "%s"', pg_escape_string($point)));
        }
        
        if ($this->debug) error_log('[DBG]' . 'AFTER' . __METHOD__ . $idbid . ":$point:" . implode(',', self::$savepoint[$idbid]));
        if ($err) error_log(__METHOD__ . ":$err");
        return $err;
    }
    /**
     * Set a database transaction advisory lock
     *
     * - A transaction advisory lock can only be used within an existing
     *   transaction.  So, a transaction must have been explicitly opened
     *   by a call to DbObj::savePoint() before using DbObj::lockPoint().
     * - The lock is automatically released when the transaction is
     *   commited or rolled back.
     *
     * @param int $exclusiveLock Lock's identifier as a signed integer in the int32 range
     *                           (i.e. in the range [-2147483648, 2147483647]).
     * @param string $exclusiveLockPrefix Lock's prefix string limited up to 4 bytes.
     * @see Dbobj::savePoint()
     * @throws \Dcp\Exception DB0010, DB0011, and DB0012
     * @return string error message
     */
    public function lockPoint($exclusiveLock, $exclusiveLockPrefix = '')
    {
        if (($exclusiveLock_int32 = \Dcp\Utils\Types::to_int32($exclusiveLock)) === false) {
            throw new \Dcp\Db\Exception("DB0012", var_export($exclusiveLock, true));
        }
        $exclusiveLock = $exclusiveLock_int32;
        if (!$this->dbid) {
            $err = sprintf("dbid is null cannot add lock %s-%s", $exclusiveLock, $exclusiveLockPrefix);
            error_log(__METHOD__ . ":$err");
            return $err;
        }
        $err = '';
        
        $idbid = intval($this->dbid);
        if (empty(self::$savepoint[$idbid])) {
            throw new \Dcp\Db\Exception("DB0011", $exclusiveLock, $exclusiveLockPrefix);
        }
        
        if ($exclusiveLockPrefix) {
            if (strlen($exclusiveLockPrefix) > 4) {
                throw new \Dcp\Db\Exception("DB0010", $exclusiveLockPrefix);
            }
            $prefixLockId = unpack("i", str_pad($exclusiveLockPrefix, 4)) [1];
        } else {
            $prefixLockId = 0;
        }
        if (self::$masterLock === false) {
            $err = $this->exec_query(sprintf('select pg_advisory_lock(0), pg_advisory_unlock(0), pg_advisory_xact_lock(%d,%d);', $exclusiveLock, $prefixLockId));
        }
        if ($err) {
            return $err;
        }
        self::$lockpoint[$idbid][sprintf("%d-%s", $exclusiveLock, $exclusiveLockPrefix) ] = array(
            $exclusiveLock,
            $prefixLockId
        );
        
        return $err;
    }
    /**
     * set a database  master lock
     * the lock is free when explicit call with false parameter.
     * When a master lock is set,
     * @param bool $useLock set lock (true) or unlock (false)
     * @see Dbobj::lockPoint()
     * @return string error message
     */
    public function setMasterLock($useLock)
    {
        if (!$this->dbid) {
            $err = sprintf("dbid is null cannot add master lock ");
            error_log(__METHOD__ . ":$err");
            return $err;
        }
        
        if ($useLock) {
            $err = $this->exec_query('select pg_advisory_lock(0)');
        } else {
            $err = $this->exec_query('select pg_advisory_unlock(0)');
        }
        if ($err) {
            return $err;
        }
        self::$masterLock = (bool)$useLock;
        return '';
    }
    /**
     * revert to transaction save point
     * @param string $point revert point
     * @return string error message
     */
    public function rollbackPoint($point)
    {
        if (!$this->dbid) {
            $err = sprintf("dbid is null cannot save point %s", $point);
            error_log(__METHOD__ . ":$err");
            return $err;
        }
        $idbid = intval($this->dbid);
        if (isset(self::$savepoint[$idbid])) $lastPoint = array_search($point, self::$savepoint[$idbid]);
        else $lastPoint = false;
        if ($lastPoint !== false) {
            
            self::$savepoint[$idbid] = array_slice(self::$savepoint[$idbid], 0, $lastPoint);
            $err = $this->exec_query(sprintf('rollback to savepoint "%s"', pg_escape_string($point)));
            if ((!$err) && (count(self::$savepoint[$idbid]) == 0)) {
                $err = $this->exec_query("commit");
            }
        } else {
            $err = sprintf("cannot rollback unsaved point : %s", $point);
        }
        
        if ($this->debug) error_log('[DBG]' . __METHOD__ . ":$point:" . implode(',', self::$savepoint[$idbid]));
        if ($err) error_log(__METHOD__ . ":$err");
        return $err;
    }
    /**
     * commit transaction save point
     * @param string $point
     * @return string error message
     */
    public function commitPoint($point)
    {
        if (!$this->dbid) {
            $err = sprintf("dbid is null cannot save point %s", $point);
            error_log(__METHOD__ . ":$err");
            return $err;
        }
        $idbid = intval($this->dbid);
        if ($this->debug) error_log('[DBG]' . __METHOD__ . ":$point:" . implode(',', self::$savepoint[$idbid]));
        
        $lastPoint = array_search($point, self::$savepoint[$idbid]);
        
        if ($lastPoint !== false) {
            self::$savepoint[$idbid] = array_slice(self::$savepoint[$idbid], 0, $lastPoint);
            $err = $this->exec_query(sprintf('release savepoint "%s"', pg_escape_string($point)));
            if ((!$err) && (count(self::$savepoint[$idbid]) == 0)) {
                $err = $this->exec_query("commit");
            }
        } else {
            $err = sprintf("cannot commit unsaved point : %s", $point);
        }
        if ($err) error_log(__METHOD__ . ":$err");
        return $err;
    }
    // FIN DE CLASSE
    
}
