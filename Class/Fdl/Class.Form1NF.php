<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 *  Export 1NF class
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("FDL/Class.SearchDoc.php");
/*
 * foreach($this->doc->fields as $k=>$v) {
                if (is_numeric($k)) $props[$v] = $this->doc->$v;
            }
 *
 *
 * $this->infofields
 *
 *
 * select max(id) from docread where initid=%initid%
*/
/**
 *
 */
class Form1NF
{
    /**
     * Parameters
     * @var array
     */
    private $params = array(
        'config' => '', // XML config file name
        'outputsql' => '', // output file name
        'outputpgservice' => '', // output pgservice name
        'tmppgservice' => 'tmp_1nf', // temporary pg service
        'tmpschemaname' => '1nf', // temporary schema name
        'tmpemptydb' => 'yes', // whether the process empty the database before process
        'sqllog' => '', // SQL log file
        
    );
    /**
     *
     * @var string
     */
    private $sqlStandardLogHandle = null;
    /**
     *
     * @var int
     */
    private $sqlStandardLogCounter = 0;
    /**
     *
     * @var int
     */
    private $sqlStandardLogBufferSize = 300;
    /**
     *
     * @var string
     */
    private $sqlStandardLogBuffer = '';
    /**
     *
     * @var string
     */
    private $sqlPostgresLogHandle = null;
    /**
     *
     * @var string
     */
    private $sqlPostgresFileName = '';
    /**
     *
     * @var int
     */
    private $sqlPostgresLogCounter = 0;
    /**
     *
     * @var int
     */
    private $sqlPostgresLogBufferSize = 300;
    /**
     *
     * @var string
     */
    private $sqlPostgresLogBuffer = '';
    /**
     *
     * @var array
     */
    private $sqlSequences = array();
    /**
     *
     * @var int
     */
    private $sqlInsertCounter = 0;
    /**
     *
     * @var array
     */
    private $sqlInsertBuffer = array();
    /**
     *
     * @var int
     */
    private $sqlInsertBufferSize = 300;
    /**
     *
     * @var array
     */
    public $dropSchemas = array(
        'public',
        'dav',
        'family'
    );
    /**
     *
     * @var string
     */
    public $freedom_pgservice = null;
    /**
     *
     * @var string
     */
    public $freedom_dbaccess = null;
    /**
     * Action
     * @var Action
     */
    public $action = null;
    /**
     * Error message
     * @var string
     */
    public $errmsg = '';
    /**
     *
     * @var array[int]Form1NF_Table
     */
    private $config = array();
    /**
     *
     * @var resource
     */
    private $conn = null;
    /**
     *
     * @var resource
     */
    private $tmp_conn = null;
    /**
     *
     * @var string
     */
    public $tmp_dbaccess = null;
    /**
     *
     * @param array $params
     */
    public function __construct($params)
    {
        global $action;
        $this->action = $action;
        foreach ($params as $key => $value) {
            if (array_key_exists($key, $this->params)) {
                $this->params[$key] = $value;
            }
        }
        
        $this->freedom_dbaccess = $action->getParam('FREEDOM_DB');
        if ($this->freedom_dbaccess == "") {
            $action->error(_("Error: empty FREEDOM_DB"));
        }
        $this->freedom_pgservice = getServiceFreedom();
        
        $this->tmp_dbaccess = sprintf("service=%s", $this->params['tmppgservice']);
    }
    /**
     *
     * @param mixed $value
     * @return string;
     */
    private function getPgEscape($value, $nullAllowed = true)
    {
        if ($nullAllowed && "$value" === "") return 'NULL';
        return "'" . pg_escape_string($value) . "'";
    }
    /**
     *
     * @param mixed $value
     * @return string;
     */
    private function getPgEscapeCopy($value, $nullAllowed = true)
    {
        if ($nullAllowed && "$value" === "") return "\\N";
        $value = pg_escape_string($value);
        $value = str_replace(array(
            "\r",
            "\n",
            "\t"
        ) , array(
            "\\r",
            "\\n",
            "\\t"
        ) , $value);
        return $value;
    }
    /**
     *
     */
    private function stdInfo()
    {
        $args = func_get_args();
        if (count($args) >= 2) {
            $msg = call_user_func_array('sprintf', $args);
        } else {
            $msg = $args[0];
        }
        $this->action->info(trim($msg));
    }
    /**
     *
     */
    private function stdError()
    {
        $args = func_get_args();
        if (count($args) >= 2) {
            $msg = call_user_func_array('sprintf', $args);
        } else {
            $msg = $args[0];
        }
        $this->action->error(trim($msg));
        $this->sqlLogWrite($msg, true);
        $this->sqlLogFlush();
        //debug_print_backtrace();
        throw new Exception(trim($msg));
    }
    /**
     *
     */
    private function sqlLogFlush()
    {
        $this->sqlPostgresLogFlush();
        $this->sqlStandardLogFlush();
        return true;
    }
    /**
     *
     */
    private function sqlLogOpen()
    {
        if (!$this->sqlPostgresLogOpen()) return false;
        if (!$this->sqlStandardLogOpen()) return false;
        return true;
    }
    /**
     *
     */
    private function sqlLogClose()
    {
        if (!$this->sqlPostgresLogClose()) return false;
        if (!$this->sqlStandardLogClose()) return false;
        return true;
    }
    /**
     *
     */
    private function sqlLogWrite($line, $comment = false)
    {
        $this->sqlPostgresLogWrite($line, $comment);
        $this->sqlStandardLogWrite($line, $comment);
    }
    /**
     *
     */
    private function sqlStandardLogFlush()
    {
        if ($this->sqlStandardLogHandle !== null) {
            @fwrite($this->sqlStandardLogHandle, $this->sqlStandardLogBuffer);
            $this->sqlStandardLogBuffer = '';
            $this->sqlStandardLogCounter = 0;
        }
    }
    /**
     *
     */
    private function sqlStandardLogWrite($line, $comment = false, $comma = true)
    {
        if ($this->sqlStandardLogHandle !== null) {
            if ($comment) {
                $line = "\n--\n-- " . str_replace("\n", "\n-- ", str_replace("\r", "", $line)) . "\n--\n";
            } elseif ($comma && substr($line, -1) != ';') {
                $line.= ';';
            }
            $line = str_replace('"' . $this->params['tmpschemaname'] . '".', '', $line) . "\n";
            $this->sqlStandardLogBuffer.= $line;
            $this->sqlStandardLogCounter++;
            if ($this->sqlStandardLogCounter >= $this->sqlStandardLogBufferSize) {
                $this->sqlStandardLogFlush();
            }
        }
    }
    /**
     *
     */
    private function sqlStandardLogClose()
    {
        if ($this->sqlStandardLogHandle !== null) {
            $this->sqlStandardLogFlush();
            @fwrite($this->sqlStandardLogHandle, "\n\n\n");
            @fclose($this->sqlStandardLogHandle);
        }
        return true;
    }
    /**
     *
     */
    private function sqlStandardLogOpen()
    {
        try {
            if (!empty($this->params['outputsql'])) {
                $this->sqlStandardLogHandle = @fopen($this->params['outputsql'], 'w');
                if (!$this->sqlStandardLogHandle) {
                    $this->stdError(_("Error could not open output log file '%s' for writing.") , $this->params['outputsql']);
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     */
    private function sqlPostgresLogFlush()
    {
        if ($this->sqlPostgresLogHandle !== null) {
            @fwrite($this->sqlPostgresLogHandle, $this->sqlPostgresLogBuffer);
            $this->sqlPostgresLogBuffer = '';
            $this->sqlPostgresLogCounter = 0;
        }
    }
    /**
     *
     */
    private function sqlPostgresLogWrite($line, $comment = false, $comma = true)
    {
        if ($this->sqlPostgresLogHandle !== null) {
            if ($comment) {
                $line = "\n--\n-- " . str_replace("\n", "\n-- ", str_replace("\r", "", $line)) . "\n--\n";
            } elseif ($comma && substr($line, -1) != ';') {
                $line.= ';';
            }
            $line = str_replace('"' . $this->params['tmpschemaname'] . '".', '', $line) . "\n";
            $this->sqlPostgresLogBuffer.= $line;
            $this->sqlPostgresLogCounter++;
            if ($this->sqlPostgresLogCounter >= $this->sqlPostgresLogBufferSize) {
                $this->sqlPostgresLogFlush();
            }
        }
    }
    /**
     *
     */
    private function sqlPostgresLogClose()
    {
        if ($this->sqlPostgresLogHandle !== null) {
            $this->sqlPostgresLogFlush();
            @fwrite($this->sqlPostgresLogHandle, "\n\n\n");
            @fclose($this->sqlPostgresLogHandle);
        }
        return true;
    }
    /**
     *
     */
    private function sqlPostgresLogOpen()
    {
        include_once ('WHAT/Lib.Common.php');
        
        try {
            if (!empty($this->params['outputpgservice']) || !empty($this->params['sqllog'])) {
                if (empty($this->params['sqllog'])) {
                    $this->sqlPostgresFileName = tempnam(getTmpDir() , 'sqlPostgres.tmp.1nf.');
                    if ($this->sqlPostgresFileName === false) {
                        $this->stdError(_("Error creating temp file for sql log output."));
                    }
                } else {
                    $this->sqlPostgresFileName = $this->params['sqllog'];
                }
                
                $this->sqlPostgresLogHandle = @fopen($this->sqlPostgresFileName, 'w');
                if (!$this->sqlPostgresLogHandle) {
                    $this->stdError(_("Error could not open sql log file '%s' for writing.") , $this->sqlPostgresFileName);
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return bool
     */
    public function run()
    {
        
        try {
            // parse xml config
            if (!$this->configParse()) return false;
            // check freedom connection
            if (!$this->freedomDatabaseConnection()) return false;
            // check tmp connection
            if (!$this->tmpDatabaseConnection()) return false;
            // whether to empty tmp db or not
            if ($this->params['tmpemptydb'] == 'yes') {
                // empty database
                if (!$this->tmpDatabaseEmpty()) return false;
                // dump freedom
                $dumpFile = $this->databaseDump($this->freedom_pgservice);
                if ($dumpFile === false) return false;
                // load dump into tmp
                if (!$this->databaseLoad($dumpFile, $this->params['tmppgservice'])) return false;
                // delete dump file
                @unlink($dumpFile);
            }
            // open log file
            if (!$this->sqlLogOpen()) return false;
            // load config
            if (!$this->configLoad()) return false;
            // create temporary schema
            if (!$this->sqlCreateSchema()) return false;
            // create tables
            if (!$this->sqlCreateTables()) return false;
            // fill tables
            if (!$this->sqlFillTables()) return false;
            // make references
            if (!$this->sqlMakeReferences()) return false;
            // close log file
            if (!$this->sqlLogClose()) return false;
            // output management
            if (!empty($this->params['outputpgservice'])) {
                if (!$this->databaseLoad($this->sqlPostgresFileName, $this->params['outputpgservice'])) return false;
            }
            // temporary file
            if (!empty($this->sqlPostgresFileName) && empty($this->params['sqllog'])) {
                @unlink($this->sqlPostgresFileName);
            }
        }
        catch(Exception $e) {
            return false;
        }
        
        $this->stdInfo(_("Export 1NF done"));
        return true;
    }
    /**
     *
     * @param mixed $requests
     * @return bool
     */
    private function sqlExecute($requests, $log = true)
    {
        if (!is_array($requests)) {
            $requests = array(
                $requests
            );
        }
        foreach ($requests as $sql) {
            if (preg_match("/^\s*--/", $sql)) {
                continue;
            }
            if ($log) $this->sqlLogWrite($sql);
            $res = @pg_query($this->tmp_conn, $sql);
            if (!$res) $this->checkErrorPostgresql($sql);
        }
        return true;
    }
    /**
     *
     * @param mixed $requests
     * @return bool
     */
    private function sqlInsertFlush()
    {
        foreach ($this->sqlInsertBuffer as $tableName => $rows) {
            $sql = 'COPY "' . $this->params['tmpschemaname'] . '"."' . $tableName . '" FROM STDIN;';
            $this->sqlPostgresLogWrite($sql, false, false);
            $res = @pg_query($this->tmp_conn, $sql);
            if (!$res) $this->checkErrorPostgresql($sql);
            foreach ($rows as $row) {
                $this->sqlPostgresLogWrite($row, false, false);
                $res = @pg_put_line($this->tmp_conn, $row . "\n");
                if (!$res) $this->checkErrorPostgresql("ROW $row");
            }
            $this->sqlPostgresLogWrite("\\.\n", false, false);
            $res = @pg_put_line($this->tmp_conn, "\\.\n");
            if (!$res) $this->checkErrorPostgresql();
            $res = @pg_end_copy($this->tmp_conn);
            if (!$res) $this->checkErrorPostgresql();
        }
        $this->sqlInsertBuffer = array();
        $this->sqlInsertCounter = 0;
    }
    /**
     *
     * @param mixed $requests
     * @return bool
     */
    private function sqlInsert($table, $fields, $values, $escapedValues)
    {
        $this->sqlStandardLogWrite('INSERT INTO "' . $this->params['tmpschemaname'] . '"."' . $table . '" ("' . implode('","', $fields) . '") VALUES (' . implode(',', $escapedValues) . ');');
        
        if (!isset($this->sqlInsertBuffer[$table])) {
            $this->sqlInsertBuffer[$table] = array();
        }
        $this->sqlInsertBuffer[$table][] = implode("\t", $values);
        $this->sqlInsertCounter++;
        
        if ($this->sqlInsertCounter >= $this->sqlInsertBufferSize) {
            $this->sqlInsertFlush();
        }
        return true;
    }
    /**
     *
     * @return bool
     */
    private function sqlCreateSchema()
    {
        try {
            $this->stdInfo(_("Create Schema '%s' ...") , $this->params['tmpschemaname']);
            $this->sqlExecute('DROP SCHEMA IF EXISTS "' . $this->params['tmpschemaname'] . '" CASCADE', false);
            $this->sqlExecute('CREATE SCHEMA "' . $this->params['tmpschemaname'] . '"', false);
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return bool
     */
    private function sqlCreateTables()
    {
        try {
            $this->stdInfo(_("Create Tables ..."));
            foreach ($this->config as $table) {
                
                $insertDefaults = true;
                $fields = array();
                switch ($table->type) {
                    case 'enum':
                    case 'enum_multiple':
                    case 'enum_inarray':
                        $fields[] = sprintf('  "%s" %s PRIMARY KEY', 'id', 'text');
                        $fields[] = sprintf('  "%s" %s', 'title', 'text');
                        $table->sqlFields[] = 'id';
                        $table->sqlFields[] = 'title';
                        $insertDefaults = false;
                        break;

                    case 'family':
                        $fields[] = sprintf('  "%s" %s PRIMARY KEY', 'id', 'integer');
                        $fields[] = sprintf('  "%s" %s', 'title', 'text');
                        $table->sqlFields[] = 'id';
                        $table->sqlFields[] = 'title';
                        break;

                    case 'array':
                        $fields[] = sprintf('  "%s" %s PRIMARY KEY', 'id', 'integer');
                        $table->sqlFields[] = 'id';
                        break;
                }
                if ($insertDefaults) {
                    foreach ($table->columns as $column) {
                        $fields[] = sprintf('  "%s" %s', $column->name, $column->pgType);
                        $table->sqlFields[] = $column->name;
                    }
                    foreach ($table->properties as $property) {
                        $fields[] = sprintf('  "%s" %s', $property->name, $property->pgType);
                        $table->sqlFields[] = $property->name;
                    }
                    foreach ($table->references as $reference) {
                        $fields[] = sprintf('  "%s" %s', $reference->attributeName, $reference->type);
                        $table->sqlFields[] = $reference->attributeName;
                    }
                }
                
                $this->stdInfo(_("Create Table '%s'") , strtolower($table->name));
                $this->sqlLogWrite(sprintf("Create table %s", strtolower($table->name)) , true);
                
                $sql = sprintf('CREATE TABLE "%s"."%s" (', $this->params['tmpschemaname'], strtolower($table->name)) . "\n";
                $sql.= implode(",\n", $fields) . "\n)";
                $this->sqlExecute($sql);
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return bool
     */
    private function sqlMakeReferences()
    {
        try {
            
            $this->stdInfo(_("Make references ..."));
            $this->sqlLogWrite("Building References", true);
            
            foreach ($this->config as $table) {
                foreach ($table->references as $reference) {
                    $sql = sprintf('ALTER TABLE "%s"."%s" ADD CONSTRAINT "%s" FOREIGN KEY ("%s") REFERENCES "%s"."%s" ("%s") MATCH SIMPLE', $this->params['tmpschemaname'], strtolower($table->name) , $reference->attributeName, $reference->attributeName, $this->params['tmpschemaname'], strtolower($reference->foreignTable) , $reference->foreignKey);
                    
                    $this->sqlExecute($sql);
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        
        return true;
    }
    /**
     *
     * @param string $tableName
     * @return int
     */
    private function sqlNextSequenceId($tableName)
    {
        if (isset($this->sqlSequences[$tableName])) {
            $this->sqlSequences[$tableName]++;
            return $this->sqlSequences[$tableName];
        } else {
            $this->sqlSequences[$tableName] = 1;
            return 1;
        }
    }
    /**
     *
     * @param int $docid
     */
    private function sqlGetValidDocId($docid)
    {
        $sql = sprintf("SELECT id from docread where initid=(select initid from docread where id=%d) and locked != -1 limit 1;", $docid);
        $res = @pg_query($this->tmp_conn, $sql);
        if ($res) {
            $row = @pg_fetch_row($res);
            if ($row) {
                return $row[0];
            }
        }
        return 'NULL';
    }
    /**
     *
     * @return bool
     */
    private function sqlFillTables()
    {
        try {
            $this->stdInfo(_("Fill Tables ..."));
            // indexes table arrays to improve performance
            $tablesByName = array();
            foreach ($this->config as $table) {
                $tablesByName[strtolower($table->name) ] = $table;
            }
            // export family tables
            foreach ($this->config as $table) {
                if ($table->type == 'family') {
                    $this->stdInfo(_("Filling family table '%s' and relatives") , strtolower($table->name));
                    $this->sqlLogWrite(sprintf("Filling table %s and relatives", strtolower($table->name)) , true);
                    // search documents
                    $s = new SearchDoc($this->tmp_dbaccess, $table->name);
                    $s->setObjectReturn();
                    //$s->latest = false;
                    //$s->trash = 'also';
                    $s->search();
                    // get field values
                    while ($doc = $s->nextDoc()) {
                        // document required fields (id, title)
                        $fieldValues = array(
                            $doc->id,
                            $this->getPgEscape($doc->getTitle()) ,
                        );
                        $fieldCopyValues = array(
                            $doc->id,
                            $this->getPgEscapeCopy($doc->getTitle()) ,
                        );
                        // fields
                        foreach ($table->columns as $column) {
                            $fieldValues[] = $column->getPgEscape($doc->getValue($column->name));
                            $fieldCopyValues[] = $column->getPgEscapeCopy($doc->getValue($column->name));
                        }
                        // properties
                        foreach ($table->properties as $property) {
                            $propertyName = $property->name;
                            $fieldValues[] = $property->getPgEscape($doc->$propertyName);
                            $fieldCopyValues[] = $property->getPgEscapeCopy($doc->$propertyName);
                        }
                        // foreign keys
                        foreach ($table->references as $reference) {
                            $fTable = strtolower($reference->foreignTable);
                            if (!array_key_exists($fTable, $tablesByName)) {
                                $this->stdError(_("Table '%s' unknown !") , $reference->foreignTable);
                            }
                            
                            switch ($tablesByName[$fTable]->type) {
                                case 'family': // docid
                                    $value = $doc->getValue($reference->attributeName);
                                    $id = $this->sqlGetValidDocId($value);
                                    $fieldValues[] = $id;
                                    $fieldCopyValues[] = $id == 'NULL' ? "\\N" : $id;
                                    break;

                                case 'enum':
                                case 'enum_multiple':
                                case 'enum_inarray':
                                    $value = $doc->getValue($reference->attributeName);
                                    $tablesByName[$fTable]->checkEnumValue($value);
                                    $fieldValues[] = $this->getPgEscape($value);
                                    $fieldCopyValues[] = $this->getPgEscapeCopy($value);
                                    break;

                                default:
                                    $fieldValues[] = "''";
                                    $fieldCopyValues[] = "";
                                    break;
                            }
                        }
                        
                        $this->sqlInsert(strtolower($table->name) , $table->sqlFields, $fieldCopyValues, $fieldValues);
                        // manage linked tables :
                        //  \_ enum_multiple_link
                        //  \_ docid_multiple_link
                        //  \_ array
                        //      \_ docid_multiple_inarray_link
                        foreach ($table->linkedTables as $type => $linkedTables) {
                            foreach ($linkedTables as $data) {
                                switch ($type) {
                                    case 'enum_multiple_link':
                                        $values = $doc->getTValue($data['column']->name);
                                        foreach ($values as $value) {
                                            if (isset($data['enumtable'])) {
                                                $data['enumtable']->checkEnumValue($value);
                                            }
                                            $this->sqlInsert(strtolower($data['table']->name) , $data['table']->sqlFields, array(
                                                $this->getPgEscapeCopy($value) ,
                                                $doc->id
                                            ) , array(
                                                $this->getPgEscapeCopy($value) ,
                                                $doc->id
                                            ));
                                        }
                                        break;

                                    case 'docid_multiple_link':
                                        $values = $doc->getTValue($data['column']->name);
                                        foreach ($values as $value) {
                                            $id = $this->sqlGetValidDocId($value);
                                            $this->sqlInsert(strtolower($data['table']->name) , $data['table']->sqlFields, array(
                                                $id == 'NULL' ? "\\N" : $id,
                                                $doc->id
                                            ) , array(
                                                $id,
                                                $doc->id
                                            ));
                                        }
                                        break;

                                    case 'array':
                                        // load all array
                                        $array = $doc->getAValues($data['table']->arrayName);
                                        // for each row of array
                                        foreach ($array as $iRow => $row) {
                                            
                                            $arrayId = $this->sqlNextSequenceId($data['table']->name);
                                            // init with auto increment id
                                            $fieldValues = array();
                                            $fieldValues[] = $arrayId;
                                            $fieldCopyValues = array();
                                            $fieldCopyValues[] = $arrayId;
                                            // get values
                                            foreach ($data['table']->columns as $col) {
                                                $fieldValues[] = $col->getPgEscape($row[$col->name]);
                                                $fieldCopyValues[] = $col->getPgEscapeCopy($row[$col->name]);
                                            }
                                            // foreign keys value
                                            foreach ($data['table']->references as $reference) {
                                                $fTable = strtolower($reference->foreignTable);
                                                if (!array_key_exists($fTable, $tablesByName)) {
                                                    $this->stdError(_("Table '%s' unknown !") , $reference->foreignTable);
                                                }
                                                if ($tablesByName[$fTable]->type == 'family' && strtolower($reference->attributeName) == strtolower($reference->foreignTable)) {
                                                    // link to family
                                                    $fieldValues[] = $doc->id;
                                                    $fieldCopyValues[] = $doc->id;
                                                    continue;
                                                }
                                                // other attributes
                                                $value = $row[$reference->attributeName];
                                                if ($tablesByName[$fTable]->type == 'family') { // docid
                                                    $id = $this->sqlGetValidDocId($value);
                                                    $fieldValues[] = $id;
                                                    $fieldCopyValues[] = $id == 'NULL' ? "\\N" : $id;
                                                } elseif (in_array($tablesByName[$fTable]->type, array(
                                                    'enum',
                                                    'enum_multiple',
                                                    'enum_inarray'
                                                ))) {
                                                    $tablesByName[$fTable]->checkEnumValue($value);
                                                    $fieldValues[] = $this->getPgEscape($value);
                                                    $fieldCopyValues[] = $this->getPgEscapeCopy($value);
                                                } else {
                                                    $fieldValues[] = $this->getPgEscape($value);
                                                    $fieldCopyValues[] = $this->getPgEscapeCopy($value);
                                                }
                                            }
                                            // insert
                                            $this->sqlInsert(strtolower($data['table']->name) , $data['table']->sqlFields, $fieldCopyValues, $fieldValues);
                                            // docid multiple in array
                                            foreach ($data['linkedTables'] as $data2) {
                                                $values = $doc->_val2array(str_replace('<BR>', "\n", $row[$data2['column']->name]));
                                                foreach ($values as $val) {
                                                    $id = $this->sqlGetValidDocId($val);
                                                    $this->sqlInsert(strtolower($data2['table']->name) , $data2['table']->sqlFields, array(
                                                        $id == 'NULL' ? "\\N" : $id,
                                                        $arrayId
                                                    ) , array(
                                                        $id,
                                                        $arrayId
                                                    ));
                                                }
                                            }
                                        }
                                        break;
                                } // end switch
                                
                            }
                        } // end foreach linkedTabled
                        
                    } // end while nextDoc
                    
                } // end if
                
            } // end foreach family
            // export enum tables
            foreach ($this->config as $table) {
                if ($table->type == 'enum' || $table->type == 'enum_multiple' || $table->type == 'enum_inarray') {
                    $this->stdInfo(_("Filling enum table '%s'") , $table->name);
                    $this->sqlLogWrite(sprintf("Filling enum table %s", strtolower($table->name)) , true);
                    foreach ($table->enumDatas as $key => $value) {
                        $this->sqlInsert($table->name, array(
                            'id',
                            'title'
                        ) , array(
                            $this->getPgEscapeCopy($key, false) ,
                            $this->getPgEscapeCopy($value)
                        ) , array(
                            $this->getPgEscape($key, false) ,
                            $this->getPgEscape($value)
                        ));
                    }
                }
            }
            // should flush insert datas before leaving !!
            $this->sqlInsertFlush();
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     * freedom checks and load
     * @return bool
     */
    private function configLoad()
    {
        
        if (!$this->configLoadFamilies()) return false;
        if (!$this->configLoadAttributes()) return false;
        if (!$this->configLoadExplodeContainers()) return false;
        if (!$this->configLoadTables()) return false;
        if (!$this->configLoadCheck()) return false;
        
        return true;
    }
    /**
     *
     * @param Form1NF_Table $family
     * @param string $arrayId
     * @return array
     */
    private function getArrayColumns($family, $arrayName)
    {
        $arrayColumns = array();
        foreach ($family->columns as $i => $column) {
            if ($column->arrayName == $arrayName) $arrayColumns[$i] = $column;
        }
        return $arrayColumns;
    }
    /**
     * freedom checks and load
     * @return bool
     */
    private function configLoadCheck()
    {
        try {
            foreach ($this->config as $iFamily => $family) {
                // check duplicate columns
                $delete = array();
                foreach ($family->columns as $i => $column1) {
                    if (in_array($i, $delete)) continue; // avoid if already deleted
                    foreach ($family->columns as $j => $column2) {
                        if ($i == $j) continue; // avoid check on same column
                        if ($column1->name == $column2->name) {
                            $delete[] = $j;
                        }
                    }
                }
                foreach ($delete as $i) {
                    $this->stdInfo(_("Remove duplicate attribute '%s' in family '%s'") , $this->config[$iFamily]->columns[$i]->name, $family->name);
                    unset($this->config[$iFamily]->columns[$i]);
                }
                // check duplicate references
                $delete = array();
                foreach ($family->references as $i => $ref1) {
                    if (in_array($i, $delete)) continue; // avoid if already deleted
                    foreach ($family->references as $j => $ref2) {
                        if ($i == $j) continue; // avoid check on same column
                        if ($ref1->isSameAs($ref2)) {
                            $delete[] = $j;
                        }
                    }
                }
                foreach ($delete as $i) {
                    $this->stdInfo(_("Remove duplicate reference '%s' in family '%s'") , $this->config[$iFamily]->references[$i]->attributeName, $family->name);
                    unset($this->config[$iFamily]->references[$i]);
                }
                // the following checks are only for families
                if ($family->type != 'family') continue;
                // check duplicate property
                $delete = array();
                foreach ($family->properties as $i => $property1) {
                    if (in_array($i, $delete)) continue; // avoid if already deleted
                    foreach ($family->properties as $j => $property2) {
                        if ($i == $j) continue; // avoid check on same column
                        if ($property1->name == $property2->name) {
                            $delete[] = $j;
                        }
                    }
                }
                foreach ($delete as $i) {
                    $this->stdInfo(_("Remove duplicate property '%s' in family '%s'") , $this->config[$iFamily]->properties[$i]->name, $family->name);
                    unset($this->config[$iFamily]->properties[$i]);
                }
                // check special property
                $special = array(
                    'id',
                    'title'
                );
                $delete = array();
                foreach ($family->properties as $i => $property) {
                    if (in_array($property->name, $special)) {
                        $delete[] = $i;
                    }
                }
                foreach ($delete as $i) {
                    $this->stdInfo(_("Skip already added property '%s' in family '%s'") , $this->config[$iFamily]->properties[$i]->name, $family->name);
                    unset($this->config[$iFamily]->properties[$i]);
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @param string $format
     * @param Form1NF_Table $family
     */
    private function getDocidFamily($column, $table)
    {
        if (!empty($column->docidFamily)) return $column->docidFamily;
        $found = '';
        foreach ($table->famAttributes as $attribute) {
            if (!empty($attribute->phpfunc)) {
                if (preg_match('/lfamill?y\([a-z]+\s*,\s*([a-z0-9_]+).*\):' . $column->name . '/si', $attribute->phpfunc, $m)) {
                    $found = $m[1];
                    break;
                }
            }
        }
        // break process if not found
        if (empty($found)) {
            $this->stdError(_("Attribute Error: impossible to found the family for docid attribute '%s'") , $column->name);
        }
        // test if family exists
        $fam = new_Doc($this->tmp_dbaccess, $found, true);
        if (!is_object($fam) || !$fam->isAlive()) {
            $this->stdError(_("Attribute Error: family '%s' is not valid or alive for docid '%s'.") , $found, $column->name);
        }
        return $found;
    }
    /**
     * freedom checks and load
     * @return bool
     */
    private function configLoadTables()
    {
        try {
            foreach ($this->config as $iFamily => $family) {
                
                if ($family->type != 'family') continue;
                
                $delete = array();
                
                foreach ($family->columns as $iColumn => $column) {
                    if (in_array($iColumn, $delete)) continue;
                    switch ($column->fullType) {
                        case 'simple': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                            // nothing to do
                            break;

                        case 'enum': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                            $tableName = $this->getUniqueTableName($column->name);
                            $newTable = new Form1NF_Table('enum', $tableName);
                            $newTable->enumDatas = $column->enumDatas;
                            $family->references[] = new Form1NF_Reference($tableName, $column->name, 'text');
                            $this->config[] = $newTable;
                            $delete[] = $iColumn;
                            break;

                        case 'docid': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                            if (empty($column->docidLinkedColumn)) {
                                // real docid
                                $table = $this->getConfigTable($this->getDocidFamily($column, $family) , true);
                                $family->references[] = new Form1NF_Reference($table->name, $column->name);
                            } else {
                                // added column
                                $table = $this->getConfigTable($column->docidLinkedColumn->family, true);
                                if (!$table->hasColumn($column->docidLinkedColumn->name)) {
                                    $table->columns[] = $column->docidLinkedColumn;
                                }
                            }
                            $table->type = 'family';
                            $delete[] = $iColumn;
                            break;

                        case 'enum_multiple': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                            $tableNameLink = $this->getUniqueTableName($family->name . '_' . $column->name);
                            $newTableLink = new Form1NF_Table('enum_multiple_link', $tableNameLink);
                            
                            $tableNameEnum = $this->getUniqueTableName($column->name);
                            $newTableEnum = new Form1NF_Table('enum_multiple', $tableNameEnum);
                            $newTableEnum->enumDatas = $column->enumDatas;
                            
                            $newTableLink->references[] = new Form1NF_Reference($tableNameEnum, 'idenum', 'text');
                            $newTableLink->references[] = new Form1NF_Reference($family->name); // idfamille
                            $family->linkedTables['enum_multiple_link'][] = array(
                                'table' => $newTableLink,
                                'enumtable' => $newTableEnum,
                                'column' => $column,
                            );
                            
                            $this->config[] = $newTableEnum;
                            $this->config[] = $newTableLink;
                            
                            $delete[] = $iColumn;
                            break;

                        case 'docid_multiple': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                            $tableNameLink = $this->getUniqueTableName($family->name . '_' . $column->name);
                            $newTableLink = new Form1NF_Table('docid_multiple_link', $tableNameLink);
                            
                            $newTableDocid = $this->getConfigTable($this->getDocidFamily($column, $family) , true);
                            
                            $newTableLink->references[] = new Form1NF_Reference($newTableDocid->name, 'iddoc');
                            $newTableLink->references[] = new Form1NF_Reference($family->name); // idfamille
                            $family->linkedTables['docid_multiple_link'][] = array(
                                'table' => $newTableLink,
                                'column' => $column,
                            );
                            
                            $this->config[] = $newTableLink;
                            $newTableDocid->type = 'family';
                            $delete[] = $iColumn;
                            break;

                        case 'simple_inarray': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                            
                        case 'enum_inarray':
                        case 'docid_inarray':
                        case 'docid_multiple_inarray':
                            // get all columns of array
                            $arrayColumns = $this->getArrayColumns($family, $column->arrayName);
                            if ($arrayColumns === false) {
                                $this->stdError(_("Error: No column found in array '%s'") , $column->arrayName);
                            }
                            // get or create table
                            $tableArrayName = $family->name . '_' . $column->arrayName;
                            $tableArray = $this->getConfigTable($tableArrayName, true);
                            $tableArray->type = 'array';
                            $tableArray->arrayName = $column->arrayName;
                            $tableArray->references[] = new Form1NF_Reference($family->name); // link to father table
                            $linkedTable = array(
                                'table' => $tableArray,
                                'linkedTables' => array() ,
                            );
                            // manage each array field
                            foreach ($arrayColumns as $i => $col) {
                                switch ($col->fullType) {
                                    case 'simple_inarray':
                                        if (!$tableArray->hasColumn($col->name)) {
                                            $tableArray->columns[] = $col;
                                        }
                                        break;

                                    case 'enum_inarray':
                                        $tableEnumName = $this->getUniqueTableName($col->name);
                                        $newTable = new Form1NF_Table('enum_inarray', $tableEnumName);
                                        $newTable->enumDatas = $col->enumDatas;
                                        $tableArray->references[] = new Form1NF_Reference($tableEnumName, $col->name, 'text');
                                        $this->config[] = $newTable;
                                        break;

                                    case 'docid_inarray':
                                        $tableDocid = $this->getConfigTable($this->getDocidFamily($col, $family) , true);
                                        $tableArray->references[] = new Form1NF_Reference($tableDocid->name, $col->name);
                                        $tableDocid->type = 'family';
                                        break;

                                    case 'docid_multiple_inarray':
                                        $tableNameLink = $this->getUniqueTableName($tableArrayName . '_' . $col->name);
                                        $newTableLink = new Form1NF_Table('docid_multiple_inarray_link', $tableNameLink);
                                        
                                        $newTableDocid = $this->getConfigTable($this->getDocidFamily($col, $family) , true);
                                        
                                        $newTableLink->references[] = new Form1NF_Reference($newTableDocid->name); // idfamille
                                        $newTableLink->references[] = new Form1NF_Reference($tableArrayName, 'idarray');
                                        
                                        $linkedTable['linkedTables'][] = array(
                                            'table' => $newTableLink,
                                            'column' => $col,
                                        );
                                        
                                        $newTableDocid->type = 'family';
                                        $this->config[] = $newTableLink;
                                        break;

                                    default:
                                        $this->stdError(_("Incoherent column type '%s' in array '%s'.") , $colType, $column->arrayName);
                                        break;
                                }
                                $delete[] = $i;
                            }
                            $family->linkedTables['array'][] = $linkedTable;
                            break;

                        default:
                            $this->stdError(_("Column type '%s' is not managed for export.") , $column->fullType);
                            break;
                    }
                }
                foreach ($delete as $iColumn) unset($this->config[$iFamily]->columns[$iColumn]);
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return bool
     */
    private function configLoadFamilies()
    {
        try {
            foreach ($this->config as $family) {
                // load the family object
                $famId = getFamIdFromName($this->tmp_dbaccess, $family->name);
                if ($famId === 0) {
                    // try to lower the name
                    $famId = getFamIdFromName($this->tmp_dbaccess, strtolower($family->name));
                    if ($famId === 0) {
                        // try to upper the name
                        $famId = getFamIdFromName($this->tmp_dbaccess, strtoupper($family->name));
                        if ($famId === 0) {
                            $this->stdError(_("Could not get family id for '%s'.") , $family->name);
                        }
                    }
                }
                // try to load family id
                $fam = new_Doc($this->tmp_dbaccess, $famId, true);
                if (!is_object($fam) || !$fam->isAlive()) {
                    $this->stdError(_("Family '%s' is not valid or alive.") , $famId);
                }
                // load all attributes
                $famAttributes = $fam->GetAttributes();
                // load attributes
                foreach ($famAttributes as $attribute) {
                    $family->famAttributes[] = new Form1NF_Column($attribute);
                }
                // checks properties
                $allowedProperties = array_keys($fam->infofields);
                foreach ($family->properties as $property) {
                    if (!in_array($property->name, $allowedProperties)) {
                        $this->stdError(_("Property '%s' is not valid (family '%s').") , $property->name, $family->name);
                    }
                    // load correct type
                    $property->setType($fam->infofields[$property->name]['type']);
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return bool
     */
    private function configLoadExplodeContainers()
    {
        try {
            foreach ($this->config as $family) {
                // explode TABS, ARRAY or FRAMES columns with their attributes
                $delete = array();
                foreach ($family->columns as $i => $column) {
                    if (in_array($i, $delete)) continue;
                    if ($column->isContainer) {
                        $columns = $family->getChildAttributes($column->name);
                        if ($columns === false) {
                            $this->stdError($family->error);
                        }
                        foreach ($columns as $col) {
                            $family->columns[] = $col;
                        }
                        $delete[] = $i;
                    }
                }
                foreach ($delete as $i) unset($family->columns[$i]);
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @return bool
     */
    private function configLoadAttributes()
    {
        try {
            foreach ($this->config as $family) {
                // load each column attribute
                foreach ($family->columns as $column) {
                    
                    $columnName = $column->name;
                    $columnLinkedName = '';
                    
                    if (strpos($columnName, ':') !== false) {
                        // <docid:attribute> syntax
                        list($columnName, $columnLinkedName) = explode(':', $columnName);
                    }
                    // search attribute
                    $attributeFound = null;
                    foreach ($family->famAttributes as $attribute) {
                        if ($attribute->name == $columnName) {
                            $attributeFound = $attribute;
                            break;
                        }
                    }
                    
                    if ($attributeFound === null) {
                        $this->stdError(_("Could not find attribute '%s' in family '%s'.") , $column->name, $family->name);
                    }
                    
                    $column->copyFromColumn($attributeFound);
                    // search linked docid attribute
                    if (!empty($columnLinkedName)) {
                        
                        if ($attributeFound->type != 'docid') {
                            $this->stdError(_("Attribute '%s' should reference a docid attribute in family '%s'.") , $column->name, $family->name);
                        }
                        
                        if (empty($attributeFound->docidFamily)) {
                            $this->stdError(_("Attribute format should not be empty on attribute '%s' in family '%s'.") , $column->name, $family->name);
                        }
                        
                        $doc = new_Doc($this->tmp_dbaccess, $attributeFound->docidFamily);
                        
                        if (!is_object($doc) || !$doc->isAlive()) {
                            $this->stdError(_("Family '%s' is not valid or alive.") , $attributeFound->docidFamily);
                        }
                        
                        $attribute = $doc->getAttribute($columnLinkedName);
                        
                        if (!is_object($attribute) || empty($attribute)) {
                            $this->stdError(_("Attribute '%s' could not be found in family '%s'.") , $columnLinkedName, $attributeFound->docidFamily);
                        }
                        $column->docidLinkedColumn = new Form1NF_Column($attribute);
                    }
                }
            }
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
    /**
     *
     * @param string $name
     * @param bool $autocreate
     * @return mixed
     */
    private function getConfigTable($name, $autocreate = false)
    {
        $return = false;
        if (empty($name)) {
            $this->stdError(_("Name of table cannot be empty"));
        }
        foreach ($this->config as $table) {
            if (strtolower($table->name) == strtolower($name)) {
                $return = $table;
            }
        }
        if (empty($return) && $autocreate) {
            $table = new Form1NF_Table('', $name);
            $this->config[] = $table;
            $return = $table;
        }
        return $return;
    }
    /**
     *
     * @param string $name
     */
    private function getUniqueTableName($name)
    {
        $i = 1;
        $newName = $name;
        while (($found = $this->getConfigTable($newName)) !== false) {
            $newName = $name . $i;
            $i++;
        }
        return strtolower($newName);
    }
    /**
     *
     * @return string
     */
    private function checkErrorPostgresql($str = '')
    {
        if ($this->tmp_conn) {
            $err = pg_last_error($this->tmp_conn);
            if (!empty($err)) {
                $this->stdError('PG Error: ' . $err . (!empty($str) ? "\n" . $str : ''));
            }
        }
    }
    /**
     *
     * @return string
     */
    private function checkErrorLibXML()
    {
        $err = libxml_get_last_error();
        if (is_object($err)) {
            $this->stdError('XML Error: ' . $err->message);
        }
        return true;
    }
    /**
     * parse XML file : no freedom checks, only xml checks
     */
    private function configParse()
    {
        
        $msgerr = '';
        
        try {
            // load xml
            $xml = new DOMDocument();
            $ret = @$xml->load($this->params['config']);
            $this->checkErrorLibXML();
            // init
            $this->config = array();
            // search database root
            $databaseNodes = @$xml->getElementsByTagName('database');
            $this->checkErrorLibXML();
            if ($databaseNodes->length == 0) {
                $this->stdError(_("XML Error: no <database/> root node."));
            }
            if ($databaseNodes->length > 1) {
                $this->stdError(_("XML Error: only one <database/> root node allowed."));
            }
            // get database root
            $databaseNode = $databaseNodes->item(0);
            // table list
            $tableNodes = @$databaseNode->getElementsByTagName('table');
            $this->checkErrorLibXML();
            foreach ($tableNodes as $tableNode) {
                // create table
                $familyName = $tableNode->getAttribute('family');
                if (empty($familyName)) {
                    $this->stdError(_("XML Error: no 'family' attribute on <table/> node."));
                }
                $table = new Form1NF_Table('family', $familyName);
                // column list
                $columnNodes = @$tableNode->getElementsByTagName('column');
                $this->checkErrorLibXML();
                foreach ($columnNodes as $columnNode) {
                    // create column
                    $columnName = strtolower($columnNode->getAttribute('attribute'));
                    $propertyName = strtolower($columnNode->getAttribute('property'));
                    
                    if (empty($columnName) && empty($propertyName)) {
                        $this->stdError(_("XML Error: no property or attribute on <column/> node."));
                    }
                    
                    if (!empty($columnName)) { // attribute
                        $table->columns[] = new Form1NF_Column($columnName);
                    } else { // property
                        $column = new Form1NF_Column($propertyName);
                        $column->isProperty = true;
                        $table->properties[] = $column;
                    }
                }
                $this->config[] = $table;
            }
            if (empty($this->config)) {
                $this->stdError(_("XML Error: no table defined."));
            }
            $this->stdInfo(_("XML config parsed OK !"));
        }
        catch(Exception $e) {
            return false;
        }
        
        return true;
    }
    /**
     * Dump the "Freedom" source database
     *
     * @return false on error
     */
    private function databaseDump($pgservice)
    {
        include_once ('WHAT/Lib.Common.php');
        
        try {
            $this->stdInfo(_("Dump pgservice '%s' ...") , $pgservice);
            
            $tmp_dump = tempnam(getTmpDir() , 'pg_dump.tmp.1nf.');
            if ($tmp_dump === false) {
                $this->stdError(_("Error creating temp file for pg_dump output."));
            }
            
            $pg_dump_cmd = LibSystem::getCommandPath('pg_dump');
            if ($pg_dump_cmd === false) {
                $this->stdError(_("Could not find pg_dump command in PATH."));
            }
            
            $ret = LibSystem::ssystem(array(
                $pg_dump_cmd,
                '--disable-triggers',
                '-f',
                $tmp_dump
            ) , array(
                'closestdin' => true,
                'closestdout' => true,
                'envs' => array(
                    'PGSERVICE' => $pgservice,
                )
            ));
            
            if ($ret != 0) {
                $this->stdError(_("Dump to '%s' returned with exitcode %s") , $tmp_dump, $ret);
            }
        }
        catch(Exception $e) {
            return false;
        }
        return $tmp_dump;
    }
    /**
     * Load the database from the dump
     *
     * @return false on error
     */
    private function databaseLoad($dumpFile, $pgservice)
    {
        try {
            $this->stdInfo(_("Load dump file into '%s' ...") , $pgservice);
            
            $psql_cmd = LibSystem::getCommandPath('psql');
            if ($psql_cmd === false) {
                $this->stdError(_("Could not find psql command in PATH."));
            }
            
            $ret = LibSystem::ssystem(array(
                $psql_cmd,
                '--quiet',
                '-f',
                $dumpFile
            ) , array(
                'closestdin' => true,
                'closestdout' => true,
                'envs' => array(
                    'PGSERVICE' => $pgservice
                )
            ));
            if ($ret != 0) {
                $this->stdError(_("Loading of dump '%s' returned with exitcode %s") , $dumpFile, $ret);
            }
        }
        catch(Exception $e) {
            return false;
        }
        return $dumpFile;
    }
    /**
     * Connect to the "Freedom" source database
     *
     * @return false on error
     */
    private function freedomDatabaseConnection()
    {
        try {
            $this->conn = @pg_connect($this->freedom_dbaccess);
            if ($this->conn === false) {
                $this->stdError(_("PG Error: Connection to freedom pg service '%s' failed !") , $this->freedom_pgservice);
            }
        }
        catch(Exception $e) {
            return false;
        }
        $this->stdInfo(_("Connection to freedom pg service '%s' OK !") , $this->freedom_pgservice);
        return $this->conn;
    }
    /**
     * Connect to the "Freedom" source database
     *
     * @return false on error
     */
    private function tmpDatabaseConnection()
    {
        try {
            $this->tmp_conn = @pg_connect($this->tmp_dbaccess);
            if ($this->tmp_conn === false) {
                $this->stdError(_("PG Error: Connection to temporary pg service '%s' failed !") , $this->params['tmppgservice']);
            }
        }
        catch(Exception $e) {
            return false;
        }
        $this->stdInfo(_("Connection to temporary pg service '%s' OK !") , $this->params['tmppgservice']);
        return $this->tmp_conn;
    }
    /**
     *
     * @return bool
     */
    private function tmpDatabaseEmpty()
    {
        try {
            $this->stdInfo(_("Emptying temporary database '%s' ...") , $this->params['tmppgservice']);
            foreach ($this->dropSchemas as $schema) {
                $this->sqlExecute(sprintf('DROP SCHEMA IF EXISTS "%s" CASCADE', $schema) , false);
            }
            $this->sqlExecute('CREATE SCHEMA "public"', false);
        }
        catch(Exception $e) {
            return false;
        }
        return true;
    }
}
/**
 *
 */
class Form1NF_Reference
{
    /**
     *
     * @var string
     */
    public $foreignTable = '';
    /**
     *
     * @var string
     */
    public $foreignKey = '';
    /**
     *
     * @var string
     */
    public $attributeName = '';
    /**
     *
     * @var string
     */
    public $type = '';
    /**
     *
     */
    public function __construct($foreignTable, $attributeName = '', $type = 'integer', $foreignKey = 'id')
    {
        $this->foreignKey = $foreignKey;
        $this->foreignTable = $foreignTable;
        $this->attributeName = empty($attributeName) ? strtolower($foreignTable) : strtolower($attributeName);
        $this->type = $type;
    }
    /**
     *
     * @param Form1NF_Reference $ref
     */
    public function isSameAs($ref)
    {
        if ($this->attributeName != $ref->attributeName) return false;
        if ($this->foreignTable != $ref->foreignTable) return false;
        if ($this->foreignKey != $ref->foreignKey) return false;
        if ($this->type != $ref->type) return false;
        return true;
    }
}
/**
 *
 */
class Form1NF_Table
{
    /**
     *
     * @var string
     */
    public $error = '';
    /**
     *
     * @var string
     */
    public $type = 'family'; // family, array, enum, enum_multiple, docid, docid_multiple
    
    /**
     *
     * @var string
     */
    public $arrayName = '';
    /**
     *
     * @var string
     */
    public $name = '';
    /**
     *
     * @var array
     */
    public $famAttributes = array();
    /**
     *
     * @var array
     */
    public $sqlFields = array();
    /**
     * only for enums
     * @var array
     */
    public $enumDatas = array();
    /**
     *
     * @var array[int]Form1NF_Column
     */
    public $columns = array();
    /**
     *
     * @var array[int]Form1NF_Column
     */
    public $properties = array();
    /**
     *
     * @var array[int]Form1NF_Reference
     */
    public $references = array();
    /**
     *
     * @var array
     */
    public $linkedTables = array();
    /**
     *
     * @param string $type
     * @param string $name
     */
    public function __construct($type, $name)
    {
        $this->type = $type;
        $this->name = $name;
    }
    /**
     * this is usefull for enum free : add dynamically key/value pairs during
     * family table filling
     * @param string $value
     */
    public function checkEnumValue($value)
    {
        if ("$value" === "") return false;
        if (!array_key_exists($value, $this->enumDatas)) {
            $this->enumDatas[$value] = $value;
        }
    }
    /**
     *
     * @param string $name
     * @return array
     */
    public function getChildAttributes($name)
    {
        
        $columns = array();
        foreach ($this->famAttributes as $attribute) {
            if ($attribute->type == 'array') continue;
            if ($attribute->type == 'frame') continue;
            if ($attribute->type == 'tab') continue;
            if (in_array($name, $attribute->containers)) {
                $columns[] = $attribute;
            }
        }
        
        return $columns;
    }
    /**
     *
     * @param string $name
     * @return bool
     */
    public function hasColumn($name)
    {
        foreach ($this->columns as $column) {
            if (strtolower($column->name) == strtolower($name)) return $column;
        }
        return false;
    }
}
/**
 *
 */
class Form1NF_Column
{
    /**
     *
     * @var string
     */
    public $name = null;
    /**
     *
     * @var string
     */
    public $type = null;
    /**
     *
     * @var string
     */
    public $fullType = null;
    /**
     *
     * @var string
     */
    public $pgType = null;
    /**
     *
     * @var string
     */
    public $docidLinkedColumn = null;
    /**
     *
     * @var string
     */
    public $docidFamily = '';
    /**
     *
     * @var string
     */
    public $arrayName = '';
    /**
     *
     * @var string
     */
    public $family = '';
    /**
     *
     * @var string
     */
    public $phpfunc = '';
    /**
     *
     * @var bool
     */
    public $isMultiple = false;
    /**
     *
     * @var bool
     */
    public $isProperty = false;
    /**
     *
     * @var bool
     */
    public $isEnum = false;
    /**
     *
     * @var bool
     */
    public $isDocid = false;
    /**
     *
     * @var bool
     */
    public $isContainer = false;
    /**
     *
     * @var bool
     */
    public $inArray = false;
    /**
     *
     * @var array
     */
    public $containers = array();
    /**
     *
     * @var array
     */
    public $enumDatas = array();
    /**
     *
     * @param Form1NF_Table $parent
     * @param string $name
     * @param object $attribute
     */
    public function __construct($name = null, $type = null)
    {
        if (is_object($name)) {
            $this->loadFromAttribute($name);
        } else {
            if ($name !== null) $this->name = strtolower($name);
            if ($type !== null) $this->type = $type;
        }
    }
    /**
     *
     * @param object $attribute
     */
    public function loadFromAttribute($attribute)
    {
        $this->name = $attribute->id;
        $this->type = $attribute->type;
        $this->family = $attribute->docname;
        $this->phpfunc = $attribute->phpfunc;
        $this->docidFamily = $attribute->format;
        $this->isMultiple = ($attribute->getOption('multiple') == 'yes');
        $this->inArray = ($attribute->inArray());
        
        if ($this->type == 'thesaurus') {
            $this->type = 'docid';
            $this->docidFamily = 'THCONCEPT';
        }
        
        $this->isEnum = ($this->type == 'enum');
        $this->isDocid = ($this->type == 'docid');
        $this->isContainer = ($this->type == 'tab' || $this->type == 'frame' || $this->type == 'array');
        
        if ($this->isEnum) $this->enumDatas = $attribute->getEnum();
        
        $this->pgType = $this->getPgType();
        $this->fullType = $this->getFullType();
        $this->loadContainers($attribute->fieldSet);
    }
    /**
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        $this->pgType = $this->getPgType();
        $this->fullType = $this->getFullType();
    }
    /**
     *
     * @param object $attribute
     */
    public function copyFromColumn($column)
    {
        foreach ($this as $ppt => $value) {
            $this->$ppt = $column->$ppt;
        }
    }
    /**
     *
     * @param object $fieldset
     */
    private function loadContainers($fieldset)
    {
        if (!is_object($fieldset)) return;
        if ($fieldset->id == 'FIELD_HIDDENS') return;
        if (empty($this->arrayName) && $fieldset->type == 'array') $this->arrayName = $fieldset->id;
        $this->containers[] = $fieldset->id;
        if (property_exists($fieldset, 'fieldSet')) {
            $this->loadContainers($fieldset->fieldSet);
        }
    }
    /**
     *
     * @param mixed $value
     * @return string;
     */
    public function getPgEscape($value, $pgType = null)
    {
        if ($pgType === null) {
            $pgType = $this->getPgType();
        }
        if ("$value" === "") return 'NULL';
        if ($pgType == 'integer' || $pgType == 'double precision') return $value;
        if ($this->isProperty && $this->name == 'revdate') $value = date('Y-m-d H:i:s', $value);
        return "'" . pg_escape_string($value) . "'";
    }
    /**
     *
     * @param mixed $value
     * @return string;
     */
    public function getPgEscapeCopy($value, $pgType = null)
    {
        if ("$value" === "") return "\\N";
        if ($this->isProperty && $this->name == 'revdate') $value = date('Y-m-d H:i:s', $value);
        $value = pg_escape_string($value);
        $value = str_replace(array(
            "\r",
            "\n",
            "\t"
        ) , array(
            "\\r",
            "\\n",
            "\\t"
        ) , $value);
        return $value;
    }
    /**
     *
     * @param string $freedom_type
     * @return string
     */
    private function getPgType($freedom_type = null)
    {
        if ($freedom_type === null) {
            $freedom_type = $this->type;
        }
        switch ($freedom_type) {
            case 'date':
                return 'date';
            case 'time':
                return 'time without time zone';
            case 'timestamp':
                return 'timestamp without time zone';
            case 'integer':
            case 'docid':
            case 'thesaurus':
            case 'uid':
            case 'int':
                return 'integer';
            case 'double':
            case 'money':
                return 'double precision';
            case 'tab':
            case 'frame':
            case 'array':
                return false;
            case 'enum':
            case 'text':
            case 'longtext':
            case 'htmltext':
            case 'password':
            case 'file':
            case 'image':
            case 'color':
            case 'ifile':
            default:
                return 'text';
        }
    }
    /**
     *
     * @return string
     */
    private function getFullType()
    {
        $type = 'simple';
        if ($this->isEnum) $type = 'enum';
        elseif ($this->isDocid) $type = 'docid';
        
        if ($this->isMultiple) $type.= '_multiple';
        if ($this->inArray) $type.= '_inarray';
        
        return $type;
    }
}
?>