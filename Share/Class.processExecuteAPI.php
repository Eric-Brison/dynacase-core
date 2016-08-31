<?php
class processExecuteAPIException extends \Exception
{
}

class processExecuteAPIAlreadyRunningException extends processExecuteAPIException
{
}

class processExecuteAPI
{
    public static $debug = false;
    
    public static function run(Action & $action)
    {
        include_once ("FDL/Class.DocFam.php");
        include_once ("FDL/Class.DocTimer.php");
        include_once ("FDL/Class.SearchDoc.php");
        
        $usage = new ApiUsage();
        $usage->setDefinitionText("Execute Dynacase Processes when needed");
        $doctimerId = $usage->addOptionalParameter('doctimer-id', 'Doctimer identifier', null, null);
        $execId = $usage->addOptionalParameter('exec-id', 'Exec identifier', null, null);
        self::$debug = ($usage->addEmptyParameter('debug', 'Enable debugging verbose output') !== false);
        $usage->verify();
        
        if ($doctimerId !== null && $execId !== null) {
            throw new processExecuteAPIException("Error: only one of '--doctimer-id' or '--exec-id'' should be used.\n");
        }
        
        if ($doctimerId !== null) {
            self::execute_doctimer($action, $doctimerId);
        } elseif ($execId !== null) {
            self::execute_exec($action, $execId);
        } else {
            try {
                self::execute_all($action);
            }
            catch(processExecuteAPIAlreadyRunningException $e) {
                /* Skip execution and silently ignore already running processes */
            }
        }
    }
    
    protected static function lock(Action & $action)
    {
        self::debug(sprintf("Locking exclusive execution..."));
        $i1 = unpack("i", "PROC") [1];
        $i2 = unpack("i", "EXEC") [1];
        simpleQuery($action->dbaccess, sprintf("SELECT pg_try_advisory_lock(%d, %d)", $i1, $i2) , $res, true, true, true);
        if ($res !== 't') {
            $msg = sprintf("A 'processExecute' API script is already running.");
            self::debug($msg);
            throw new processExecuteAPIAlreadyRunningException($msg);
        }
        self::debug(sprintf("Exclusive execution locked."));
        return $res;
    }
    
    protected static function unlock(Action & $action, $lock)
    {
        /* Unlock will be performed when the process exits and the Postgres connection is torn down. */
    }
    
    static function debug($msg)
    {
        if (self::$debug) {
            error_log($msg);
        }
    }
    
    static function execute_all($action)
    {
        $lock = self::lock($action);
        try {
            self::verifyExecDocuments($action);
            self::verifyTimerDocuments($action);
        }
        catch(\Exception $e) {
            self::unlock($action, $lock);
            throw $e;
        }
        self::unlock($action, $lock);
    }
    
    static function execute_doctimer(Action & $action, $doctimerId)
    {
        $dt = new DocTimer($action->dbaccess, $doctimerId);
        $time_start = microtime(true);
        $err = $dt->executeTimerNow();
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if ($err) {
            $action->log->error(sprintf("Error while executing timer %s (%d): %s in %.03f seconds", $dt->title, $dt->id, $err, $time));
            print sprintf("Error while executing timer %s (%d): %s in %.03f seconds", $dt->title, $dt->id, $err, $time);
        } else {
            $action->log->info(sprintf("Timer %s (%d) executed in %.03f seconds", $dt->title, $dt->id, $time));
            print sprintf("Timer %s (%d) executed in %.03f seconds", $dt->title, $dt->id, $time);
        }
    }
    /**
     * @param Action $action
     * @param \Dcp\Family\Exec|string $exec
     */
    static function execute_exec(Action & $action, $exec)
    {
        if (is_scalar($exec)) {
            /**
             * @var \Dcp\Family\Exec $exec
             */
            $exec = new_Doc($action->dbaccess, $exec, true);
        }
        if (!is_object($exec) || !is_a($exec, '\Dcp\Family\Exec') || !$exec->isAlive()) {
            return;
        }
        $exec->executeNow();
    }
    
    static function verifyExecDocuments(Action & $action)
    {
        // Verify EXEC document
        $now = Doc::getTimeDate();
        
        $s = new SearchDoc($action->dbaccess, "EXEC");
        $s->setObjectReturn();
        $s->addFilter(sprintf("exec_nextdate < %s", pg_escape_literal($now)));
        $s->addFilter("exec_status is null or exec_status = 'none'");
        //  $s->setDebugMode();
        $s->search();
        
        while ($de = $s->getNextDoc()) {
            $de->setValue("exec_status", "waiting");
            $de->modify(true, array(
                "exec_status"
            ) , true);
        }
        
        $s = new SearchDoc($action->dbaccess, "EXEC");
        $s->setObjectReturn();
        $s->addFilter(sprintf("exec_nextdate < %s", pg_escape_literal($now)));
        $s->addFilter("exec_status != 'progressing'");
        //$s->setDebugMode();
        $s->search();
        //print_r2($s->getDebugInfo());
        self::debug(__METHOD__ . " " . sprintf("Found %d documents to execute.", $s->count()));
        if ($s->count() <= 0) {
            return;
        }
        
        while ($de = $s->getNextDoc()) {
            /**
             * @var \Dcp\Core\ExecProcessus $de
             */
            self::debug(__METHOD__ . " " . sprintf("Executing document '%s' (%d).", $de->getTitle() , $de->id));
            self::execute_exec($action, $de);
        }
        unset($exec);
        return;
    }
    
    static function verifyTimerDocuments(Action & $action)
    {
        // Verify EXEC document
        $dt = new DocTimer($action->dbaccess);
        $ate = $dt->getActionsToExecute();
        
        self::debug(__METHOD__ . " " . sprintf("Found %d doctimers.", count($ate)));
        foreach ($ate as $k => $v) {
            try {
                $tmpfile = tempnam(getTmpDir() , __METHOD__);
                if ($tmpfile === false) {
                    throw new \Exception("Error: could not create temporary file.");
                }
                $cmd = sprintf("%s/wsh.php --api=processExecute --doctimer-id=%s > %s 2>&1", DEFAULT_PUBDIR, escapeshellarg($v['id']) , escapeshellarg($tmpfile));
                self::debug(__METHOD__ . " " . sprintf("Running '%s'", $cmd));
                system($cmd, $ret);
                $out = file_get_contents($tmpfile);
                unlink($tmpfile);
                if ($ret !== 0) {
                    throw new \Exception(sprintf("Process '%s' returned with error (%d): %s", $cmd, $ret, $out));
                }
            }
            catch(\Exception $e) {
                $errMsg = formatErrorLogException($e);
                
                error_log($errMsg);
                if (!isInteractiveCLI()) {
                    $expand = array(
                        'm' => preg_replace('/^([^\n]*).*/s', '\1', $e->getMessage())
                    );
                    _wsh_send_error($errMsg, $expand);
                }
            }
        }
    }
}
