<?php
/**
 *  Export 1NF class
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
 /**
 */

include_once("FDL/Class.SearchDoc.php");

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
class Form1NF {
	/**
	 * Parameters
	 * @var array
	 */
	private $params = array(
		'config' => '',              // XML config file name
		'outputtype' => 'sql',       // sql or pgservice
		'outputname' => '',          // sql file or output pgservice name
		'tmppgservice' => 'tmp_1nf', // temporary pg service
		'tmpschemaname' => '1nf',    // temporary schema name
		'tmpemptydb' => 'yes',       // whether the process empty the database before process
		'sqllog' => '',              // SQL log file
	);
	/**
	 *
	 * @var string
	 */
	public $sqlLogFileHandle = null;
	/**
	 *
	 * @var array
	 */
	public $dropSchemas = array('public', 'dav', 'family');
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
	public function __construct($params) {
		global $action;
		$this->action = $action;
		foreach($params as $key => $value) {
			if(array_key_exists($key, $this->params)) {
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
	private function getPgEscape($value) {
		if("$value" === "") return 'NULL';
		return "'".pg_escape_string($value)."'";
	}
	/**
	 *
	 */
	private function stdInfo() {
		$args = func_get_args();
		if(count($args) >= 2) {
			$msg = call_user_func_array('sprintf', $args);
		}
		else {
			$msg = $args[0];
		}
		$this->action->info(trim($msg));
	}
	/**
	 *
	 */
	private function stdError() {
		$args = func_get_args();
		if(count($args) >= 2) {
			$msg = call_user_func_array('sprintf', $args);
		}
		else {
			$msg = $args[0];
		}
		$this->action->error(trim($msg));
		$this->logSqlWrite($msg, true);
		//debug_print_backtrace();
		throw new Exception(trim($msg));
	}
	/**
	 *
	 */
	private function logSqlWrite($line, $comment=false) {
		if($comment) {
			$line = "\n--\n-- ".str_replace("\n", "\n-- ", str_replace("\r", "", $line))."\n--\n";
		}
		else {
			$line .= ';';
		}
		$line = str_replace('"'.$this->params['tmpschemaname'].'".', '', $line);
		if($this->sqlLogFileHandle !== null) {
			@fwrite($this->sqlLogFileHandle, $line."\n");
		}
	}
	/**
	 *
	 */
	private function logSqlClose() {
		if($this->sqlLogFileHandle !== null) {
			@fwrite($this->sqlLogFileHandle, "\n\n\n");
			@fclose($this->sqlLogFileHandle);
		}
		return true;
	}
	/**
	 *
	 */
	private function logSqlOpen() {
		try {
			if(empty($this->params['sqllog'])) {
				$tmp_dump = tempnam(null, 'sqllog.tmp_1nf');
				if ($tmp_dump === false) {
					$this->stdError(_("Error creating temp file for sql log output."));
				}
				$this->params['sqllog'] = $tmp_dump;
			}
			
			$this->sqlLogFileHandle = @fopen($this->params['sqllog'], 'w');
			if(!$this->sqlLogFileHandle) {
				$this->stdError(_("Error could not open sql log file '%s' for writing."), $this->params['sqllog']);
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
	public function run() {

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
				if(!$this->tmpDatabaseEmpty()) return false;

				// dump freedom
				$dumpFile = $this->databaseDump($this->freedom_pgservice);
				if ($dumpFile === false) return false;

				// load dump into tmp
				if(!$this->databaseLoad($dumpFile, $this->params['tmppgservice'])) return false;
			}

			// open log file
			if (!$this->logSqlOpen()) return false;

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
			if (!$this->logSqlClose()) return false;

			// output management
			if ($this->params['outputtype'] == 'sql') {
				if(!@copy($this->params['sqllog'], $this->params['outputname'])) {
					$this->stdError(_("Error copying output file '%s'"), $this->params['outputname']);
				}
			}
			else {
				if (!$this->databaseLoad($this->params['sqllog'], $this->params['outputname'])) return false;
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
	private function sqlExecute($requests, $log=true) {
		if(!is_array($requests)) {
			$requests = array($requests);
		}
		foreach ($requests as $sql) {
			if (preg_match("/^\s*--/", $sql)) {
				continue;
			}
			if($log) $this->logSqlWrite($sql);
			$res = @pg_query($this->tmp_conn, $sql);
			if(!$res) $this->checkErrorPostgresql($sql);
		}
		return true;
	}
	/**
	 * 
	 * @return bool
	 */
	private function sqlCreateSchema() {
		try {
			$this->stdInfo(_("Create Schema '%s' ..."), $this->params['tmpschemaname']);
			$this->sqlExecute('DROP SCHEMA IF EXISTS "'.$this->params['tmpschemaname'].'" CASCADE', false);
			$this->sqlExecute('CREATE SCHEMA "'.$this->params['tmpschemaname'].'"', false);
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
	private function sqlCreateTables() {
		try {
			$this->stdInfo(_("Create Tables ..."));
			foreach($this->config as $table) {

				$createSequence = false;
				$insertDefaults = true;
				$fields = array();
				switch($table->type) {
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
						$createSequence = true;
						break;
				}
				if($insertDefaults) {
					foreach($table->columns as $column) {
						$fields[] = sprintf('  "%s" %s', $column->name, $column->getPgType());
						$table->sqlFields[] = $column->name;
					}
					foreach($table->references as $reference) {
						$fields[] = sprintf('  "%s" %s', $reference->attributeName, $reference->type);
						$table->sqlFields[] = $reference->attributeName;
					}
				}

				$this->stdInfo(_("Create Table '%s'"), strtolower($table->name));
				$this->logSqlWrite(sprintf("Create table %s", strtolower($table->name)), true);

				$sql = sprintf('CREATE TABLE "%s"."%s" (', $this->params['tmpschemaname'], strtolower($table->name))."\n";
				$sql .= implode(",\n", $fields)."\n)";
				$this->sqlExecute($sql);

				if($createSequence) {
					$this->sqlExecute(sprintf('CREATE SEQUENCE "%s"."seq_%s"', $this->params['tmpschemaname'], strtolower($table->name)), false);
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
	private function sqlMakeReferences() {
		try {
			$this->stdInfo(_("Make references ..."));
			$this->logSqlWrite("Building References", true);

			foreach($this->config as $table) {
				foreach($table->references as $reference) {
					$sql = sprintf(
						'ALTER TABLE "%s"."%s" ADD CONSTRAINT "%s" FOREIGN KEY ("%s") REFERENCES "%s"."%s" ("%s") MATCH SIMPLE',
						$this->params['tmpschemaname'], strtolower($table->name),
						$reference->attributeName, $reference->attributeName,
						$this->params['tmpschemaname'], strtolower($reference->foreignTable),
						$reference->foreignKey
					);

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
	private function sqlNextSequenceId($tableName) {
		$res = pg_exec($this->tmp_conn, 'select nextval (\'"'.$this->params['tmpschemaname'].'"."seq_'.strtolower($tableName).'"\')');
		$arr = pg_fetch_array ($res, 0);
		return intval($arr[0]);
	}
	/**
	 *
	 * @param int $docid 
	 */
	private function sqlGetValidDocId($docid) {
		$sql = sprintf("SELECT id from docread where initid=(select initid from docread where id=%d) and locked != -1 limit 1;", $docid);
		$res = @pg_query($this->tmp_conn, $sql);
		if($res) {
			$row = @pg_fetch_row($res);
			if($row) {
				return $row[0];
			}
		}
		return 'NULL';
	}
	/**
	 *
	 * @return bool
	 */
	private function sqlFillTables() {
		try {
			$this->stdInfo(_("Fill Tables ..."));

			// indexes table arrays to improve performance
			$tablesByName = array();
			foreach($this->config as $table) {
				$tablesByName[strtolower($table->name)] = $table;
			}

			// export family tables
			foreach($this->config as $table) {
				if($table->type == 'family') {
					$this->stdInfo(_("Filling family table '%s' and relatives"), strtolower($table->name));
					$this->logSqlWrite(sprintf("Filling table %s and relatives", strtolower($table->name)), true);

					// search documents
					$s = new SearchDoc($this->tmp_dbaccess, $table->name);
					$s->setObjectReturn();
					//$s->latest = false;
					//$s->trash = 'also';
					$s->search();

					// get field names
					$sql = 'INSERT INTO "'.$this->params['tmpschemaname'].'"."'.strtolower($table->name).'" ("'.implode('","', $table->sqlFields).'") VALUES ';

					// get field values
					while($doc = $s->nextDoc()) {

						// document required fields (id, title)
						$fieldValues = array(
							$doc->id,
							"'".pg_escape_string($doc->getTitle())."'",
						);

						// fields
						foreach($table->columns as $column) {
							$fieldValues[] = $column->getPgEscape($doc->getValue($column->attribute->id));
						}

						// foreign keys
						foreach($table->references as $reference) {
							$fTable = strtolower($reference->foreignTable);
							if(!array_key_exists($fTable, $tablesByName)) {
								$this->stdError(_("Table '%s' unknown !"), $reference->foreignTable);
							}

							switch($tablesByName[$fTable]->type) {
								case 'family': // docid
									$value = $doc->getValue($reference->attributeName);
									$fieldValues[] = $this->sqlGetValidDocId($value);
									break;
								case 'enum':
								case 'enum_multiple':
								case 'enum_inarray':
									$value = $doc->getValue($reference->attributeName);
									$tablesByName[$fTable]->checkEnumValue($value);
									$fieldValues[] = $this->getPgEscape($value);
									break;
								default:
									$fieldValues[] = "''";
									break;
							}
						}

						$this->sqlExecute($sql."(".implode(',', $fieldValues).")");

						// manage linked tables :
						//  \_ enum_multiple_link
						//  \_ docid_multiple_link
						//  \_ array
						//      \_ docid_multiple_inarray_link
						foreach($table->linkedTables as $type => $linkedTables) {
							foreach($linkedTables as $data) {
								$sql2 = 'INSERT INTO "'.$this->params['tmpschemaname'].'"."'.strtolower($data['table']->name).'" ("'.implode('","', $data['table']->sqlFields).'") VALUES ';
								switch($type) {

									case 'enum_multiple_link':
										$values = $doc->getTValue($data['attribute']->id);
										foreach($values as $value) {
											if(isset($data['enumtable'])) {
												$data['enumtable']->checkEnumValue($value);
											}
											$this->sqlExecute($sql2."('".pg_escape_string($value)."',".$doc->id.")");
										}
										break;

									case 'docid_multiple_link':
										$values = $doc->getTValue($data['attribute']->id);
										foreach($values as $value) {
											$this->sqlExecute($sql2."(".$this->sqlGetValidDocId($value).",".$doc->id.")");
										}
										break;

									case 'array':
										// load all array
										$array = $doc->getAValues($data['table']->arrayName);

										// for each row of array
										foreach($array as $iRow => $row) {

											$arrayId = $this->sqlNextSequenceId($data['table']->name);

											// init with auto increment id
											$fieldValues = array();
											$fieldValues[] = $arrayId;

											// get values
											foreach($data['table']->columns as $col) {
												$fieldValues[] = $col->getPgEscape($row[$col->attribute->id]);
											}

											// foreign keys value
											foreach($data['table']->references as $reference) {
												$fTable = strtolower($reference->foreignTable);
												if(!array_key_exists($fTable, $tablesByName)) {
													$this->stdError(_("Table '%s' unknown !"), $reference->foreignTable);
												}
												if($tablesByName[$fTable]->type == 'family' &&
												   strtolower($reference->attributeName) == strtolower($reference->foreignTable)) {
													// link to family
													$fieldValues[] = $doc->id;
													continue;
												}
												// other attributes
												$value = $row[$reference->attributeName];
												if($tablesByName[$fTable]->type == 'family') { // docid
													$fieldValues[] = $this->sqlGetValidDocId($value);
												}
												elseif(in_array($tablesByName[$fTable]->type, array('enum', 'enum_multiple', 'enum_inarray'))) {
													$tablesByName[$fTable]->checkEnumValue($value);
													$fieldValues[] = $this->getPgEscape($value);
												}
												else {
													$fieldValues[] = "'".pg_escape_string($value)."'";
												}
											}

											// insert
											$this->sqlExecute($sql2."(".implode(',', $fieldValues).")");

											// docid multiple in array
											foreach($data['linkedTables'] as $data2) {
												$sql3 = 'INSERT INTO "'.$this->params['tmpschemaname'].'"."'.strtolower($data2['table']->name).'" ("'.implode('","', $data2['table']->sqlFields).'") VALUES ';
												$values = $doc->_val2array(str_replace('<BR>', "\n", $row[$data2['attribute']->id]));
												foreach($values as $val) {
													$this->sqlExecute($sql3."(".$this->sqlGetValidDocId($val).",".$arrayId.")");
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
			foreach($this->config as $table) {
				if ($table->type == 'enum' || $table->type == 'enum_multiple' || $table->type == 'enum_inarray') {
					$this->stdInfo(_("Filling enum table '%s'"), $table->name);
					$this->logSqlWrite(sprintf("Filling table %s", strtolower($table->name)), true);
					foreach($table->datas as $key => $value) {
						$sql = 'INSERT INTO "'.$this->params['tmpschemaname'].'"."'.$table->name.'" ("id","title") VALUES (\''.pg_escape_string($key).'\',\''.pg_escape_string($value).'\')';
						$this->sqlExecute($sql);
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
	 * freedom checks and load
	 * @return bool
	 */
	private function configLoad() {

		if(!$this->configLoadFamilies()) return false;
		if(!$this->configLoadAttributes()) return false;
		if(!$this->configLoadExplodeContainers()) return false;
		if(!$this->configLoadTables()) return false;
		if(!$this->configLoadCheck()) return false;

		return true;
	}
	/**
	 *
	 * @param Form1NF_Table $family
	 * @param string $arrayId 
	 * @return array
	 */
	private function getArrayColumns($family, $arrayId) {
		$arrayColumns = array();
		foreach($family->columns as $i => $column) {
			if(is_object($column->attribute) && is_object($column->attribute->fieldSet) && $column->attribute->fieldSet->id == $arrayId) {
				$arrayColumns[$i] = $column;
			}
		}
		return $arrayColumns;
	}
	/**
	 * freedom checks and load
	 * @return bool
	 */
	private function configLoadCheck() {
		try {
			foreach($this->config as $iFamily => $family) {

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
	private function configLoadDocid($format, $attributeName, $family) {
		if(!empty($format)) return $format;
		$found = '';
		foreach($family->famAttributes as $attribute) {
			$phpfunc = $attribute->phpfunc;
			if(!empty($phpfunc)) {
				if(preg_match('/lfamill?y\([a-z]+\s*,\s*([a-z0-9_]+).*\):'.$attributeName.'/si', $phpfunc, $m)) {
					$found = $m[1];
					break;
				}
			}
		}
		// break process if not found
		if(empty($found)) {
			$this->stdError(_("Attribute Error: impossible to found the family for docid attribute '%s'"), $attributeName);
		}
		// test if family exists
		$fam = new_Doc($this->tmp_dbaccess, $found, true);
		if (!is_object($fam) || !$fam->isAlive()) {
			$this->stdError(_("Attribute Error: family '%s' is not valid or alive for docid '%s'."), $found, $attributeName);
		}
		return $found;
	}
	/**
	 * freedom checks and load
	 * @return bool
	 */
	private function configLoadTables() {
		try {
			foreach($this->config as $iFamily => $family) {

				if($family->type != 'family') continue;

				$delete = array();

				foreach($family->columns as $iColumn => $column) {
					if(in_array($iColumn, $delete)) continue;
					$columnType = $column->getType();
					switch($columnType) {
						case 'simple': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
							// nothing to do
							break;

						case 'enum': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
							$tableName = $this->getUniqueTableName($column->name);
							$newTable = new Form1NF_Table('enum', $tableName, $column->rename);
							$newTable->datas = $column->attribute->getEnum();
							$family->references[] = new Form1NF_Reference($tableName, $column->name, 'text');
							$this->config[] = $newTable;
							$delete[] = $iColumn;
							break;

						case 'docid': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
							if($column->attribute->id == $column->name) {
								// real docid
								$table = $this->getConfigTable($this->configLoadDocid($column->attribute->format, $column->attribute->id, $family), true);
								$family->references[] = new Form1NF_Reference($table->name, $column->name);
							}
							else {
								// added column
								$table = $this->getConfigTable($column->attribute->docname, true);
								if(!$table->hasColumn($column->attribute->id)) {
									$newColumn = new Form1NF_Column($table, $column->attribute->id, $column->rename);
									$newColumn->attribute = $column->attribute;
									$table->columns[] = $newColumn;
								}
							}
							$table->type = 'family';
							$delete[] = $iColumn;
							break;

						case 'enum_multiple': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
							$tableNameLink = $this->getUniqueTableName($family->name.'_'.$column->name);
							$newTableLink = new Form1NF_Table('enum_multiple_link', $tableNameLink, empty($column->rename) ? '' : $column->rename.'_link');

							$tableNameEnum = $this->getUniqueTableName($column->name);
							$newTableEnum = new Form1NF_Table('enum_multiple', $tableNameEnum, $column->rename);
							$newTableEnum->datas = $column->attribute->getEnum();

							$newTableLink->references[] = new Form1NF_Reference($tableNameEnum, 'idenum', 'text');
							$newTableLink->references[] = new Form1NF_Reference($family->name); // idfamille

							$family->linkedTables['enum_multiple_link'][] = array(
								'table' => $newTableLink,
								'enumtable' => $newTableEnum,
								'attribute' => $column->attribute,
							);

							$this->config[] = $newTableEnum;
							$this->config[] = $newTableLink;
							$delete[] = $iColumn;
							break;

						case 'docid_multiple': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
							$tableNameLink = $this->getUniqueTableName($family->name.'_'.$column->name);
							$newTableLink = new Form1NF_Table('docid_multiple_link', $tableNameLink, empty($column->rename) ? '' : $column->rename.'_link');

							$newTableDocid = $this->getConfigTable($this->configLoadDocid($column->attribute->format, $column->attribute->id, $family), true);

							$newTableLink->references[] = new Form1NF_Reference($newTableDocid->name, 'iddoc');
							$newTableLink->references[] = new Form1NF_Reference($family->name); // idfamille

							$family->linkedTables['docid_multiple_link'][] =  array(
								'table' => $newTableLink,
								'attribute' => $column->attribute,
							);

							$this->config[] = $newTableLink;
							$delete[] = $iColumn;
							break;

						case 'simple_inarray': // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
						case 'enum_inarray':
						case 'docid_inarray':
						case 'docid_multiple_inarray':

							// get all columns of array
							$arrayColumns = $this->getArrayColumns($family, $column->attribute->fieldSet->id);
							if($arrayColumns === false) {
								$this->stdError(_("Error: No column found in array '%s'"), $column->attribute->fieldSet->id);
							}

							// get or create table
							$tableArrayName = $family->name.'_'.$column->attribute->fieldSet->id;
							$tableArray = $this->getConfigTable($tableArrayName, true);
							$tableArray->type = 'array';
							$tableArray->arrayName = $column->attribute->fieldSet->id;
							$tableArray->references[] = new Form1NF_Reference($family->name); // link to father table

							$linkedTable = array(
								'table' => $tableArray,
								'linkedTables' => array(),
							);

							// manage each array field
							foreach($arrayColumns as $i => $col) {
								$colType = $col->getType();
								switch($colType) {
									case 'simple_inarray':
										if(!$tableArray->hasColumn($col->name)) {
											$newColumn = new Form1NF_Column($tableArray, $col->attribute->id, $col->rename);
											$newColumn->attribute = $col->attribute;
											$tableArray->columns[] = $newColumn;
										}
										break;

									case 'enum_inarray':
										$tableEnumName = $this->getUniqueTableName($col->name);
										$newTable = new Form1NF_Table('enum_inarray', $tableEnumName, $col->rename);
										$newTable->datas = $col->attribute->getEnum();
										$tableArray->references[] = new Form1NF_Reference($tableEnumName, $col->name, 'text');
										$this->config[] = $newTable;
										break;

									case 'docid_inarray':
										$tableDocid = $this->getConfigTable($this->configLoadDocid($col->attribute->format, $col->attribute->id, $family), true);
										$tableArray->references[] = new Form1NF_Reference($tableDocid->name, $col->name);
										$tableDocid->type = 'family';
										break;

									case 'docid_multiple_inarray':
										$tableNameLink = $this->getUniqueTableName($tableArrayName.'_'.$col->name);
										$newTableLink = new Form1NF_Table('docid_multiple_inarray_link', $tableNameLink, empty($col->rename) ? '' : $col->rename.'_link');

										$newTableDocid = $this->getConfigTable($this->configLoadDocid($col->attribute->format, $col->attribute->id, $family), true);

										$newTableLink->references[] = new Form1NF_Reference($newTableDocid->name); // idfamille
										$newTableLink->references[] = new Form1NF_Reference($tableArrayName, 'idarray');

										$linkedTable['linkedTables'][] = array(
											'table' => $newTableLink,
											'attribute' => $col->attribute,
										);

										$this->config[] = $newTableLink;
										break;

									default:
										$this->stdError(_("Incoherent column type '%s' in array '%s'."), $colType, $column->attribute->fieldSet->id);
										break;
								}
								$delete[] = $i;
							}
							$family->linkedTables['array'][] = $linkedTable;
							break;

						default:
							$this->stdError(_("Column type '%s' is not managed for export."), $columnType);
							break;
					}
				}
				foreach($delete as $iColumn) unset($this->config[$iFamily]->columns[$iColumn]);
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
	private function configLoadFamilies() {
		try {
			foreach($this->config as $family) {

				// load the family object
				$family->famId = getFamIdFromName($this->tmp_dbaccess, $family->name);
				if ($family->famId === 0) {
					// try to lower the name
					$family->famId = getFamIdFromName($this->tmp_dbaccess, strtolower($family->name));
					if ($family->famId === 0) {
						// try to upper the name
						$family->famId = getFamIdFromName($this->tmp_dbaccess, strtoupper($family->name));
						if ($family->famId === 0) {
							$this->stdError(_("Could not get family id for '%s'."), $family->name);
						}
					}
				}

				// try to load family id
				$family->fam = new_Doc($this->tmp_dbaccess, $family->famId, true);
				if (!is_object($family->fam) || !$family->fam->isAlive()) {
					$this->stdError(_("Family '%s' is not valid or alive."), $family->famId);
				}

				// load all attributes
				$family->famAttributes = $family->fam->GetAttributes();
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
	private function configLoadExplodeContainers() {
		try {
			foreach($this->config as $family) {
				// explode TABS, ARRAY or FRAMES columns with their attributes
				$delete = array();
				foreach($family->columns as $i => $column) {
					if(in_array($i, $delete)) continue;
					if($column->attribute->type == 'tab' || $column->attribute->type == 'frame' || $column->attribute->type == 'array') {
						$columns = $family->getChildAttributes($column->attribute->id);
						if($columns === false) {
							$this->stdError($family->error);
						}
						foreach($columns as $col) {
							$newColumn = new Form1NF_Column($family, $col->id);
							$newColumn->attribute = $col;
							$family->columns[] = $newColumn;
						}
						$delete[] = $i;
					}
				}
				foreach($delete as $i) unset($family->columns[$i]);
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
	private function configLoadAttributes() {
		try {
			foreach($this->config as $family) {

				// load each column attribute
				foreach($family->columns as $column) {
					if(strpos($column->name, ':') === false) {
						$attributeFound = null;
						foreach($family->famAttributes as $attribute) {
							if($attribute->id == $column->name) {
								$attributeFound = $attribute;
								break;
							}
						}

						if($attributeFound === null) {
							$this->stdError(_("Could not find attribute '%s' in family '%s'."), $column->name, $family->name);
						}

						$column->attribute = $attributeFound;
					}
					else { // <docid:attribute> syntax
						list($columnDocid, $columnName) = explode(':', $column->name);

						$attributeFound = null;
						foreach($family->famAttributes as $attribute) {
							if($attribute->id == $columnDocid) {
								$attributeFound = $attribute;
								break;
							}
						}

						if($attributeFound === null) {
							$this->stdError(_("Could not find attribute '%s' in family '%s'."), $column->name, $family->name);
						}

						if($attributeFound->type != 'docid') {
							$this->stdError(_("Attribute '%s' should reference a docid attribute in family '%s'."), $column->name, $family->name);
						}

						if(empty($attributeFound->format)) {
							$this->stdError(_("Attribute format should not be empty on attribute '%s' in family '%s'."), $column->name, $family->name);
						}

						$doc = new_Doc($this->tmp_dbaccess, $attributeFound->format);

						if (!is_object($doc) || !$doc->isAlive()) {
							$this->stdError(_("Family '%s' is not valid or alive."), $attributeFound->format);
						}

						$attribute = $doc->getAttribute($columnName);

						if(!is_object($attribute) || empty($attribute)) {
							$this->stdError(_("Attribute '%s' could not be found in family '%s'."), $columnName, $attributeFound->format);
						}
						$column->name = $columnDocid;
						$column->attribute = $attribute;
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
	private function getConfigTable($name, $autocreate=false, $rename='') {
		$return = false;
		if(empty($name)) {
			$this->stdError(_("Name of table cannot be empty"));
		}
		foreach($this->config as $table) {
			if(strtolower($table->name) == strtolower($name)) {
				$return = $table;
			}
		}
		if(empty($return) && $autocreate) {
			$table = new Form1NF_Table('', $name);
			$this->config[] = $table;
			$return = $table;
		}
		if(!empty($rename) && !empty($return) && empty($return->rename)) {
			$return->rename = $rename;
		}
		return $return;
	}
	/**
	 *
	 * @param string $name
	 */
	private function getUniqueTableName($name) {
		$i = 1;
		$newName = $name;
		while(($found = $this->getConfigTable($newName)) !== false) {
			$newName = $name.$i;
			$i++;
		}
		return strtolower($newName);
	}
	/**
	 *
	 * @return string
	 */
	private function checkErrorPostgresql($str='') {
		if($this->tmp_conn) {
			$err = pg_last_error($this->tmp_conn);
			if(!empty($err)) {
				$this->stdError('PG Error: '.$err.(!empty($str) ? "\n".$str : ''));
			}
		}
	}
	/**
	 * 
	 * @return string
	 */
	private function checkErrorLibXML() {
		$err = libxml_get_last_error();
		if(is_object($err)) {
			$this->stdError('XML Error: '.$err->message);
		}
		return true;
	}
	/**
	 * parse XML file : no freedom checks, only xml checks
	 */
	private function configParse() {

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
			foreach($tableNodes as $tableNode) {

				// create table
				$familyName = $tableNode->getAttribute('family');
				$familyRename = $tableNode->getAttribute('name');
				if (empty($familyName)) {
					$this->stdError(_("XML Error: no 'family' attribute on <table/> node."));
				}
				$table = new Form1NF_Table('family', $familyName, $familyRename);

				// column list
				$columnNodes = @$tableNode->getElementsByTagName('column');
				$this->checkErrorLibXML();
				foreach($columnNodes as $columnNode) {

					// create column
					$columnName = strtolower($columnNode->getAttribute('attribute'));
					$columnRename = strtolower($columnNode->getAttribute('name'));
					if (empty($columnName)) {
						$this->stdError(_("XML Error: no 'attribute' attribute on <column/> node."));
					}
					$column = new Form1NF_Column($table, $columnName, $columnRename);

					$table->columns[] = $column;

				}

				$this->config[] = $table;

			}
			if(empty($this->config)) {
				$this->stdError(_("XML Error: no table defined."));
			}
			$this->stdInfo(_("XML config parsed OK !"));
			
		} catch (Exception $e) {
			return false;
		}
		
		return true;
	}
	/**
	 * Dump the "Freedom" source database
	 *
	 * @return false on error
	 */
	private function databaseDump($pgservice) {
		try {
			$this->stdInfo(_("Dump pgservice '%s' ..."), $pgservice);

			$tmp_dump = tempnam(null, 'pg_dump.tmp_1nf');
			if ($tmp_dump === false) {
				$this->stdError(_("Error creating temp file for pg_dump output."));
			}

			$pg_dump_cmd = LibSystem::getCommandPath('pg_dump');
			if ($pg_dump_cmd === false) {
				$this->stdError(_("Could not find pg_dump command in PATH."));
			}

			$ret = LibSystem::ssystem(
				array($pg_dump_cmd, '-f', $tmp_dump),
				array(
					'closestdin' => true,
					'closestdout' => true,
					'envs' => array(
						'PGSERVICE' => $pgservice,
					)
				)
			);

			if ($ret != 0) {
				$this->stdError(_("Dump to '%s' returned with exitcode %s"), $tmp_dump, $ret);
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
	private function databaseLoad($dumpFile, $pgservice) {
		try {
			$this->stdInfo(_("Load dump file into '%s' ..."), $pgservice);

			$psql_cmd = LibSystem::getCommandPath('psql');
			if ($psql_cmd === false) {
				$this->stdError(_("Could not find psql command in PATH."));
			}

			$ret = LibSystem::ssystem(
				array($psql_cmd, '-f', $dumpFile),
				array(
					'closestdin' => true,
					'closestdout' => true,
					'envs' => array(
						'PGSERVICE' => $pgservice
					)
				)
			);
			if ($ret != 0) {
				$this->stdError(_("Loading of dump '%s' returned with exitcode %s"), $dumpFile, $ret);
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
	private function freedomDatabaseConnection() {
		try {
			$this->conn = @pg_connect($this->freedom_dbaccess);
			if ($this->conn === false) {
				$this->stdError(_("PG Error: Connection to freedom pg service '%s' failed !"), $this->freedom_pgservice);
			}
		}
		catch(Exception $e) {
			return false;
		}
		$this->stdInfo(_("Connection to freedom pg service '%s' OK !"), $this->freedom_pgservice);
		return $this->conn;
	}
	/**
	 * Connect to the "Freedom" source database
	 *
	 * @return false on error
	 */
	private function tmpDatabaseConnection() {
		try {
			$this->tmp_conn = @pg_connect($this->tmp_dbaccess);
			if ($this->tmp_conn === false) {
				$this->stdError(_("PG Error: Connection to temporary pg service '%s' failed !"), $this->params['tmppgservice']);
			}
		}
		catch(Exception $e) {
			return false;
		}
		$this->stdInfo(_("Connection to temporary pg service '%s' OK !"), $this->params['tmppgservice']);
		return $this->tmp_conn;
	}
	/**
	 *
	 * @return bool
	 */
	private function tmpDatabaseEmpty() {
		try {
			$this->stdInfo(_("Emptying temporary database '%s' ..."), $this->params['tmppgservice']);
			foreach($this->dropSchemas as $schema) {
				$this->sqlExecute(sprintf('DROP SCHEMA IF EXISTS "%s" CASCADE', $schema), false);
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
class Form1NF_Reference {
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
	public function __construct($foreignTable, $attributeName='', $type='integer', $foreignKey='id'){
		$this->foreignKey = $foreignKey;
		$this->foreignTable = $foreignTable;
		$this->attributeName = empty($attributeName) ? strtolower($foreignTable) : strtolower($attributeName);
		$this->type = $type;
	}
}
/**
 *
 */
class Form1NF_Table {
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
	 * @var string
	 */
	public $rename = '';
	/**
	 *
	 * @var Doc
	 */
	public $fam = null;
	/**
	 *
	 * @var string
	 */
	public $famId = '';
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
	public $datas = array();
	/**
	 *
	 * @var array[int]Form1NF_Column
	 */
	public $columns = array();
	/**
	 *
	 * @var array[int]Form1NF_Column
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
	 * @param string $rename 
	 */
	public function __construct($type, $name, $rename = '') {
		$this->type = $type;
		$this->name = $name;
		$this->rename = $rename;
	}
	/**
	 * this is usefull for enum free : add dynamically key/value pairs during
	 * family table filling
	 * @param string $value
	 */
	public function checkEnumValue($value) {
		if("$value" === "") return false;
		if(!array_key_exists($value, $this->datas)) {
			$this->datas[$value] = $value;
		}
	}
	/**
	 *
	 * @param string $name
	 * @return array
	 */
	public function getChildAttributes($name) {

		$columns = array();
		foreach($this->famAttributes as $attribute) {
			if($attribute->type == 'array') continue;
			if($attribute->type == 'frame') continue;
			if($attribute->type == 'tab') continue;
			if(is_object($attribute->fieldSet) && $this->parentFoundInFieldSet($attribute->fieldSet, $name)) {
				$columns[] = $attribute;
			}
		}
		/*
		if(empty($columns)) {
			$this->error = _("Incoherent structure !");
			return false;
		}
		*/

		// check duplicate columns
		foreach($columns as $column1) {
			foreach($this->columns as $column2) {
				if($column1->id == $column2->attribute->id) {
					$this->error = sprintf(_("Duplicate attributes '%s' via '%s' container !"), $column1->id, $name);
					return false;
				}
			}
		}
		return $columns;
	}
	/**
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasColumn($name) {
		foreach($this->columns as $column) {
			if(strtolower($column->name) == strtolower($name)) return $column;
		}
		return false;
	}
	/**
	 *
	 * @param object $fieldset
	 * @param string $name
	 * @return bool
	 */
	private function parentFoundInFieldSet($fieldset, $name) {
		if(!is_object($fieldset)) {
			return false;
		}
		if($fieldset->id == $name) {
			return true;
		}
		elseif(property_exists($fieldset, 'fieldSet')) {
			return $this->parentFoundInFieldSet($fieldset->fieldSet, $name);
		}
	}
	/**
	 *
	 * @return string
	 */
	public function getName() {
		if(empty($this->rename)) {
			return $this->Name;
		}
		return $this->rename;
	}
}
/**
 *
 */
class Form1NF_Column {
	/**
	 *
	 * @var string
	 */
	public $name = '';
	/**
	 *
	 * @var string
	 */
	public $rename = '';
	/**
	 *
	 * @var Attribute
	 */
	public $attribute = null;
	/**
	 *
	 * @var Form1NF_Table
	 */
	public $parent = null;
	/**
	 *
	 * @var string
	 */
	public $type = '';
	/**
	 *
	 * @param Form1NF_Table $parent
	 * @param string $name
	 * @param object $attribute
	 * @param string $rename
	 */
	public function __construct(&$parent, $name, $rename = '', $type = null) {
		$this->parent = $parent;
		$this->name = $name;
		$this->rename = $rename;
		if($type !== null) $this->type = $type;
	}
	/**
	 *
	 * @return bool
	 */
	public function isMultiple() {
		if($this->attribute->getOption('multiple') == 'yes') {
			return true;
		}
		return false;
	}
	/**
	 *
	 * @return bool
	 */
	public function isEnum() {
		if($this->attribute->type == 'enum') {
			return true;
		}
		return false;
	}
	/**
	 *
	 * @return bool
	 */
	public function isDocid() {
		if($this->attribute->type == 'docid') {
			return true;
		}
		return false;
	}
	/**
	 *
	 * @return bool
	 */
	public function inArray() {
		if($this->attribute->inArray()) {
			return true;
		}
		return false;
	}
	/**
	 *
	 * @return string
	 */
	public function getFreedomType() {
		if(!empty($this->type)) return $this->type;
		if(is_object($this->attribute)) return $this->attribute->type;
		return false;
	}
	/**
	 *
	 * @param mixed $value
	 * @return string;
	 */
	public function getPgEscape($value, $pgType=null) {
		if($pgType === null) {
			$pgType = $this->getPgType();
		}
		switch($pgType) {
			case 'double precision':
			case 'integer':
				if("$value" === "") $value = 'NULL';
				break;
			default:
				$value = "'".pg_escape_string($value)."'";
				break;
		}
		return $value;
	}
	/**
	 *
	 * @param string $freedom_type
	 * @return string
	 */
	public function getPgType($freedom_type=null) {
		if($freedom_type === null) {
			$freedom_type = $this->getFreedomType();
		}
		switch($freedom_type) {
			case 'date': return 'date';
			case 'time': return 'time without time zone';
			case 'timestamp': return 'timestamp without time zone';
			case 'integer':
			case 'int': return 'integer';
			case 'double':
			case 'money': return 'double precision';
			case 'tab':
			case 'frame':
			case 'docid':
			case 'enum':
			case 'array': return false;
			case 'text':
			case 'longtext':
			case 'htmltext':
			case 'password':
			case 'file':
			case 'image':
			case 'color':
			case 'ifile':
			default: return 'text';
		}
	}
	/**
	 * 
	 * @return string
	 */
	public function getType() {
		$type = 'simple';
		if($this->isEnum()) $type = 'enum';
		elseif($this->isDocid()) $type = 'docid';
		elseif($this->attribute->id != $this->name) {
			$linkedColumn = $this->parent->hasColumn($this->name);
			if($linkedColumn && $linkedColumn->attribute->type == 'docid') $type = 'docid';
		}

		if($this->isMultiple()) $type .= '_multiple';
		if($this->inArray()) $type .= '_inarray';

		return $type;
	}
	/**
	 *
	 * @return string
	 */
	public function getName() {
		if(empty($this->rename)) {
			return $this->Name;
		}
		return $this->rename;
	}
}
?>