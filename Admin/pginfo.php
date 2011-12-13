<html>
<head>
<?php
print sprintf("<title>%s</title>", isset($_GET['q'])?htmlspecialchars($_GET['q']):'pginfo');
?>
<style type="text/css">
body {
  font-family: monospace;
}
div.menu {
  background-color: darkgray;
}
table > thead {
  background-color: lightgray;
}
</style>
</head>
<body>
<?php

define('PG_PAGE_SIZE', 8 * 1024);

include("../WHAT/Lib.Common.php");

$pgservice_core = getServiceCore();

$conn = pg_connect("service='$pgservice_core'");
if( $conn === false ) {
  print sprintf("Error: could not connect to pg service '%s'", $pgservice_core);
  exit(1);
}

$query = array();

$query['pg_settings'] = sprintf("SELECT name, setting, context, short_desc FROM pg_settings");

$query['pg_stat_activity'] = sprintf("SELECT datname, procpid, client_addr, client_port, waiting, query_start, now() - query_start AS time, current_query FROM pg_stat_activity WHERE procpid != pg_backend_pid()");

$query['tables'] = sprintf("SELECT s.schemaname, s.relname, c.oid, c.relfilenode, s.seq_scan, s.idx_scan, c.reltuples, c.relpages as pages FROM pg_stat_all_tables as s, pg_class as c WHERE s.relname = c.relname AND s.schemaname IN (SELECT nspname FROM pg_namespace WHERE oid = c.relnamespace) AND ( s.schemaname = 'public' OR s.schemaname = 'pg_toast' )");

$query['tables I/O'] = sprintf("SELECT schemaname, relname, heap_blks_read, heap_blks_hit, idx_blks_read, idx_blks_hit, toast_blks_read, toast_blks_hit, tidx_blks_read, tidx_blks_hit FROM pg_statio_all_tables WHERE schemaname IN ('public', 'pg_toast')");

$query['indexes'] = sprintf("SELECT s.schemaname, s.relname, s.indexrelname,  c.oid, c.relfilenode, s.idx_scan, s.idx_tup_read, s.idx_tup_fetch, c.reltuples, c.relpages as pages FROM pg_stat_all_indexes as s, pg_class as c WHERE s.indexrelname = c.relname AND s.schemaname IN (SELECT nspname FROM pg_namespace WHERE oid = c.relnamespace) AND ( s.schemaname = 'public' OR s.schemaname = 'pg_toast' )");

$query['indexes I/O'] = sprintf("SELECT schemaname, relname, indexrelname, idx_blks_read, idx_blks_hit FROM pg_statio_all_indexes WHERE schemaname IN ('public', 'pg_toast')");

$query['vacuums'] = sprintf("SELECT s.schemaname, s.relname, c.reltuples, s.n_tup_ins, s.n_tup_upd, s.n_tup_del, s.n_tup_hot_upd, s.n_live_tup, s.n_dead_tup, s.last_vacuum, s.last_autovacuum FROM pg_stat_all_tables as s, pg_class as c WHERE s.relname = c.relname AND s.schemaname IN (SELECT nspname FROM pg_namespace WHERE oid = c.relnamespace) AND ( s.schemaname = 'public' OR s.schemaname = 'pg_toast' )");

$query['pg_buffercache'] = sprintf("SELECT c.relname, count(*) AS pages FROM pg_buffercache b INNER JOIN pg_class c ON b.relfilenode = c.relfilenode AND b.reldatabase IN (0, (SELECT oid FROM pg_database WHERE datname = current_database())) GROUP BY c.relname");

$parms = array();
$parms['q'] = $_GET['q'];
$parms['orderby'] = $_GET['orderby'];
$parms['desc'] = $_GET['desc'];

if( ! isset($query[$parms['q']]) ) {
  $parms['q'] = key($query);
}
if( $parms['desc'] != 'desc' && $parms['desc'] != 'asc' ) {
  $parms['desc'] = 'desc';
}

$menu = array();
foreach( $query as $k => $v ) {
  $menu [] = '<a href="?q='.htmlspecialchars($k).'">'.htmlspecialchars($k).'</a>';
}
print '<div class="menu">&nbsp;'.join('&nbsp;|&nbsp;', $menu).'&nbsp;</div>';

show_query($conn, $query[$parms['q']], $parms);

function show_query(&$conn, &$query, &$parms) {
  $q = $parms['q'];
  $orderby = $parms['orderby'];
  $desc = $parms['desc'];

  $add = '';
  if( $orderby != '' ) {
    $add = sprintf('ORDER BY "%s"', pg_escape_string($orderby));
    if( $desc != '' ) {
      $add = sprintf('%s %s', $add, pg_escape_string($desc));
    }
  }

  $res = pg_prepare($conn, 'select', sprintf("%s %s", $query, pg_escape_string($add)));
  if( $res !== false ) {
    $res = pg_execute($conn, 'select', array());
  }
  if( $res === false ) {
    print '<div class="error">';
    print htmlspecialchars(pg_last_error($conn));
    print '</div>';
    return false;
  }

  $col_num = pg_num_fields($res);
  $line_num = pg_num_rows($res);

  $fields = array();
  for( $i = 0; $i < $col_num; $i++ ) {
    $fields[$i] = pg_field_name($res, $i);
  }
  $desc = ($desc == 'desc') ? 'asc' : 'desc';

  print '<div class="table">';
  print '<table>';
  print '<thead>';
  foreach( $fields as $i => $name ) {
    print '<td><a href="?q='.htmlspecialchars($q).'&orderby='.htmlspecialchars($name).'&desc='.htmlspecialchars($desc).'">'.htmlspecialchars($name).'</a></td>';
  }
  print '</thead>';
  print '<tbody>';
  while( $row = pg_fetch_row($res) ) {
    print '<tr>';
    foreach( $fields as $i => $name ) {
      $v = $row[$i];
      if( $name == 'pages' ) {
	$v = prettySize($v * PG_PAGE_SIZE);
      }
      print '<td>'.htmlspecialchars($v).'</td>';
    }
    print '</tr>';
  }
  print '</tbody>';
  print '</table>';
  print '</div>';
}
	
function prettySize($bytes, $precision = 2) {
  $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
  
  $bytes = max($bytes, 0); 
  $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
  $pow = min($pow, count($units) - 1); 
  
  $bytes = $bytes / pow(1024, $pow);
  
  return round($bytes, $precision) . ' ' . $units[$pow]; 
}

?>
</body>
</html>