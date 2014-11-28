<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Vault;

use Dcp\PgFetchArrayIterator;

require_once 'WHAT/Lib.Common.php';

Class VaultAnalyzerException extends \Dcp\Exception
{
}

class VaultAnalyzer
{
    const STMT_DELETE_ID_FILE = 'delete_id_file';
    
    protected $_conn = null;
    
    public function __construct()
    {
        $this->sqlConnect();
    }
    
    public function __destruct()
    {
        $this->sqlDisconnect();
    }
    
    public function summary()
    {
        $report = array();
        
        $res = $this->sqlQuery('SELECT count(id_file) AS count, sum(size) AS size, pg_size_pretty(sum(size)) AS size_pretty FROM vaultdiskstorage');
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $t = pg_fetch_array($res, NULL, PGSQL_ASSOC);
        if ($t === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $report['all'] = $t;
        
        $res = $this->sqlQuery('SELECT count(id_file) AS count, sum(size) AS size, pg_size_pretty(sum(size)) AS size_pretty FROM vaultdiskstorage WHERE NOT EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file)');
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $t = pg_fetch_array($res, NULL, PGSQL_ASSOC);
        if ($t === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $report['orphan'] = $t;
        
        $res = $this->sqlQuery('SELECT count(id_file) AS count, sum(size) AS size, pg_size_pretty(sum(size)) AS size_pretty FROM vaultdiskstorage WHERE EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file)');
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $t = pg_fetch_array($res, NULL, PGSQL_ASSOC);
        if ($t === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $report['used'] = $t;
        
        return $report;
    }
    
    public function analyzePhysicalFiles()
    {
        $report = array();
        
        $query = <<<'EOF'
WITH files AS (
SELECT id_file, vdfs.r_path AS vault_root, l_path || '/' || vds.id_file ||
	CASE WHEN name ~ E'\\.[^.]*$' THEN regexp_replace(name, E'.*(\\.[^.]*)$', E'\\1')
		ELSE '.nop'
	END AS filename
FROM vaultdiskfsstorage AS vdfs, vaultdiskdirstorage AS vdds, vaultdiskstorage AS vds
WHERE
	vds.id_fs = vdfs.id_fs
	AND vds.id_dir = vdds.id_dir
	AND vdds.id_fs = vds.id_fs
)
SELECT files.*, dvi.* FROM files LEFT OUTER JOIN docvaultindex AS dvi ON vaultid = id_file
ORDER BY id_file, docid
;
EOF;
        
        $res = $this->sqlQuery($query);
        $count = pg_num_rows($res);
        if ($count <= - 1) {
            throw new VaultAnalyzerException("Invalid result count '%s'.", $count);
        }
        
        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);
        
        return $report;
    }
    
    public function cleanDocVaultIndex()
    {
        $report = array();
        
        $res = $this->sqlQuery("DELETE FROM docvaultindex WHERE NOT EXISTS (SELECT 1 FROM doc WHERE id = docid) RETURNING *");
        $count = pg_num_rows($res);
        
        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);
        
        return $report;
    }
    
    public function deleteIdFile($vid)
    {
        $report = array();
        
        $res = $this->sqlExec(self::STMT_DELETE_ID_FILE, array(
            $vid
        ));
        $count = pg_num_rows($res);
        
        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);
        
        return $report;
    }
    
    public function analyzeOrphans()
    {
        $report = array();
        
        $query = <<<'EOF'
SELECT
    id_file, vdfs.r_path AS vault_root, l_path || '/' || vds.id_file ||
	    CASE WHEN name ~ E'\\.[^.]*$' THEN regexp_replace(name, E'.*(\\.[^.]*)$', E'\\1')
		    ELSE '.nop'
		END AS filename
	FROM vaultdiskfsstorage AS vdfs, vaultdiskdirstorage AS vdds, vaultdiskstorage AS vds
	WHERE
	    vds.id_fs = vdfs.id_fs
	    AND vds.id_dir = vdds.id_dir
	    AND vdds.id_fs = vds.id_fs
	    AND NOT EXISTS (SELECT 1 FROM docvaultindex WHERE vaultid = id_file)
;
EOF;
        
        $res = $this->sqlQuery($query);
        $count = pg_num_rows($res);
        if ($count <= - 1) {
            throw new VaultAnalyzerException("Invalid result count '%s'.", $count);
        }
        
        $report['count'] = $count;
        $report['iterator'] = new PgFetchArrayIterator($res);
        
        return $report;
    }
    
    protected function sqlDisconnect()
    {
        pg_close($this->_conn);
    }

    protected function sqlConnect()
    {
        $this->_conn = getDbId('');
        $this->sqlPrepare(self::STMT_DELETE_ID_FILE, 'DELETE FROM vaultdiskstorage WHERE id_file = $1 RETURNING *');
    }

    protected function sqlQuery($query)
    {
        $res = pg_query($this->_conn, $query);
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        return $res;
    }

    protected function sqlCount($query)
    {
        $res = pg_query($this->_conn, $query);
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        $row = pg_fetch_row($res);
        if ($row === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        return $row[0];
    }
    
    protected function sqlExec($stmt, $argv)
    {
        $res = pg_execute($this->_conn, $stmt, $argv);
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        return $res;
    }
    
    protected function sqlPrepare($stmt, $query)
    {
        $res = pg_prepare($this->_conn, $stmt, $query);
        if ($res === false) {
            throw new VaultAnalyzerException(pg_last_error($this->_conn));
        }
        return $res;
    }
}
