<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Retrieve and store file in Vault
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */
include_once ('WHAT/Lib.Common.php');
include_once ('FDL/freedom_util.php');
include_once ('FDL/Class.SearchDoc.php');

ini_set('memory_limit', '1G');

$dbaccess_freedom = $action->getParam('FREEDOM_DB');
if ($dbaccess_freedom == "") {
    error_log(__FILE__ . " " . sprintf("Error: empty FREEDOM_DB"));
    exit(1);
}

$dbaccess_core = $action->getParam('CORE_DB');
if ($dbaccess_core == "") {
    error_log(__FILE__ . " " . sprintf("Error: empty CORE_DB"));
    exit(1);
}

$default_famid = 0;
$default_word = 'test';
$default_limit = 10;
$default_resetcperm = 'no';

$parms = array();
$parms['famid'] = getHttpVars('famid', $default_famid);
if (!is_numeric($parms['famid'])) {
    $id = getFamIdFromName($dbaccess_freedom, $parms['famid']);
    if ($id === 0) {
        error_log(__FILE__ . " " . sprintf("Error: unknown family with name '%s'", $parms['famid']));
        exit(1);
    }
}

$parms['word'] = getHttpVars('word', $default_word);
if ($parms['word'] == "") {
    $parms['word'] = $default_word;
}

$parms['limit'] = getHttpVars('limit', $default_limit);
if (!is_numeric($parms['limit']) || $parms['limit'] <= 0) {
    error_log(__FILE__ . " " . sprintf("Error: limit should be numeric and >= 1"));
    exit(1);
}

$parms['resetcperm'] = getHttpVars('resetcperm', 'no');
if ($parms['resetcperm'] != 'yes' && $parms['resetcperm'] != 'no') {
    error_log(__FILE__ . " " . sprintf("Error: resetcperm '%s' should be 'yes' or 'no'", $parms['resetcperm']));
    exit(1);
}

if ($parms['resetcperm'] == 'yes') {
    $ret = resetcperm();
    if ($ret === false) {
        error_log(__FILE__ . " " . sprintf("Error resetting docperm.\n"));
        exit(1);
    }
}

$stat = array();
$ret = bench($stat);
if ($ret === false) {
    exit(1);
}

print_stat($stat);

exit(0);

function resetcperm()
{
    global $dbaccess_core;
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $delete = "";
    $delete.= "delete from docperm where upacl=0 and unacl=0;";
    $delete.= "update docperm set cacl=0 where cacl != 0;";
    
    $vacuum = "vacuum full docperm;";
    
    $q = pg_query(getDbId($dbaccess_freedom) , $delete);
    if ($q === false) {
        return false;
    }
    
    $q = pg_query(getDbId($dbaccess_freedom) , $vacuum);
    if ($q === false) {
        return false;
    }
    
    return true;
}

function print_stat(&$stat)
{
    print_stat_search($stat);
    print_stat_search_distinct($stat);
    print_stat_regex($stat);
    print_stat_regex_distinct($stat);
    print_stat_fulltext($stat);
    print_stat_fulltext_distinct($stat);
}
/**
 * Print Search stat
 */

function print_stat_search($stat)
{
    $table = array(
        'lineformat' => " %32s | %6s | %6s | %7s | %8s | %8s | %8s | %8s\n"
    );
    
    echo "\n\n\n=== Search ===\n\n\n";
    
    echo sprintf($table['lineformat'], "Name", "Id", "Count", "Profile", "^O", "O(title)", "O(cdate)", "O(id)");
    echo sprintf($table['lineformat'], "---", "---", "---", "---", "---", "---", "---", "---");
    
    foreach ($stat['search'] as $fam) {
        print_stat_search_fam($stat, $fam, $table);
    }
}

function print_stat_search_fam(&$stat, &$fam, &$table)
{
    echo sprintf($table['lineformat'],
    // sprintf("%s%s", $fam['name'], (strlen($fam['inherit'])==0)?'':sprintf(" (%s)", $fam['inherit'])),
    sprintf("%s%s", $fam['name'], (strlen($fam['inherit']) == 0) ? '' : ' *') , $fam['id'], $stat['search'][$fam['id']]['count'], $stat['search'][$fam['id']]['profile'], $stat['search'][$fam['id']]['no_order']['time'], $stat['search'][$fam['id']]['order_by_title']['time'], $stat['search'][$fam['id']]['order_by_cdate']['time'], $stat['search'][$fam['id']]['order_by_id']['time']);
    
    if ($fam['fromid'] != 0) {
        echo sprintf($table['lineformat'], $fam['name'] . " <>", $fam['id'], $stat['search'][$fam['id']]['only']['count'], $stat['search'][$fam['id']]['profile'], $stat['search'][$fam['id']]['only']['no_order']['time'], $stat['search'][$fam['id']]['only']['order_by_title']['time'], $stat['search'][$fam['id']]['only']['order_by_cdate']['time'], $stat['search'][$fam['id']]['only']['order_by_id']['time']);
    }
}

function print_stat_search_distinct($stat)
{
    $table = array(
        'lineformat' => " %32s | %6s | %6s | %7s | %8s | %8s | %8s | %8s\n"
    );
    
    echo "\n\n\n=== Search DISTINCT ===\n\n\n";
    
    echo sprintf($table['lineformat'], "Name", "Id", "Count", "Profile", "^O", "O(title)", "O(cdate)", "O(id)");
    echo sprintf($table['lineformat'], "---", "---", "---", "---", "---", "---", "---", "---");
    
    foreach ($stat['search'] as $fam) {
        print_stat_search_fam_distinct($stat, $fam, $table);
    }
}

function print_stat_search_fam_distinct(&$stat, &$fam, &$table)
{
    echo sprintf($table['lineformat'],
    // sprintf("%s%s", $fam['name'], (strlen($fam['inherit'])==0)?'':sprintf(" (%s)", $fam['inherit'])),
    sprintf("%s%s", $fam['name'], (strlen($fam['inherit']) == 0) ? '' : ' *') , $fam['id'], $stat['search'][$fam['id']]['count'], $stat['search'][$fam['id']]['profile'], $stat['search'][$fam['id']]['no_order']['distinct']['time'], $stat['search'][$fam['id']]['order_by_title']['distinct']['time'], $stat['search'][$fam['id']]['order_by_cdate']['distinct']['time'], $stat['search'][$fam['id']]['order_by_id']['distinct']['time']);
    
    if ($fam['fromid'] != 0) {
        echo sprintf($table['lineformat'], $fam['name'] . " <>", $fam['id'], $stat['search'][$fam['id']]['only']['count'], $stat['search'][$fam['id']]['profile'], $stat['search'][$fam['id']]['only']['no_order']['distinct']['time'], $stat['search'][$fam['id']]['only']['order_by_title']['distinct']['time'], $stat['search'][$fam['id']]['only']['order_by_cdate']['distinct']['time'], $stat['search'][$fam['id']]['only']['order_by_id']['distinct']['time']);
    }
}
/**
 * Print Regex stat
 */

function print_stat_regex($stat)
{
    $table = array(
        'lineformat' => " %32s | %6s | %6s | %7s | %8s | %8s | %8s | %8s\n"
    );
    
    echo "\n\n\n=== Regex ===\n\n\n";
    
    echo sprintf($table['lineformat'], "Name", "Id", "Count", "Profile", "^O", "O(title)", "O(cdate)", "O(id)");
    echo sprintf($table['lineformat'], "---", "---", "---", "---", "---", "---", "---", "---");
    
    foreach ($stat['search'] as $fam) {
        print_stat_regex_fam($stat, $fam, $table);
    }
}

function print_stat_regex_fam(&$stat, &$fam, &$table)
{
    echo sprintf($table['lineformat'],
    // sprintf("%s%s", $fam['name'], (strlen($fam['inherit'])==0)?'':sprintf(" (%s)", $fam['inherit'])),
    sprintf("%s%s", $fam['name'], (strlen($fam['inherit']) == 0) ? '' : ' *') , $fam['id'], $stat['search'][$fam['id']]['count'], $stat['search'][$fam['id']]['profile'], $stat['regex'][$fam['id']]['no_order']['time'], $stat['regex'][$fam['id']]['order_by_title']['time'], $stat['regex'][$fam['id']]['order_by_cdate']['time'], $stat['regex'][$fam['id']]['order_by_id']['time']);
}

function print_stat_regex_distinct($stat)
{
    $table = array(
        'lineformat' => " %32s | %6s | %6s | %7s | %8s | %8s | %8s | %8s\n"
    );
    
    echo "\n\n\n=== Regex DISTINCT ===\n\n\n";
    
    echo sprintf($table['lineformat'], "Name", "Id", "Count", "Profile", "^O", "O(title)", "O(cdate)", "O(id)");
    echo sprintf($table['lineformat'], "---", "---", "---", "---", "---", "---", "---", "---");
    
    foreach ($stat['search'] as $fam) {
        print_stat_regex_fam_distinct($stat, $fam, $table);
    }
}

function print_stat_regex_fam_distinct(&$stat, &$fam, &$table)
{
    echo sprintf($table['lineformat'],
    // sprintf("%s%s", $fam['name'], (strlen($fam['inherit'])==0)?'':sprintf(" (%s)", $fam['inherit'])),
    sprintf("%s%s", $fam['name'], (strlen($fam['inherit']) == 0) ? '' : ' *') , $fam['id'], $stat['search'][$fam['id']]['count'], $stat['search'][$fam['id']]['profile'], $stat['regex'][$fam['id']]['no_order']['distinct']['time'], $stat['regex'][$fam['id']]['order_by_title']['distinct']['time'], $stat['regex'][$fam['id']]['order_by_cdate']['distinct']['time'], $stat['regex'][$fam['id']]['order_by_id']['distinct']['time']);
}
/**
 * Print fulltext stat
 */

function print_stat_fulltext(&$stat)
{
    $table = array(
        'lineformat' => " %32s | %6s | %6s | %7s | %8s | %8s | %8s | %8s | %8s\n"
    );
    
    echo "\n\n\n=== Fulltext ===\n\n\n";
    
    echo sprintf($table['lineformat'], "Name", "Id", "Count", "Profile", "^O", "O(title)", "O(cdate)", "O(id)", "O(rank)");
    echo sprintf($table['lineformat'], "---", "---", "---", "---", "---", "---", "---", "---", "---");
    
    foreach ($stat['search'] as $fam) {
        print_stat_fulltext_fam($stat, $fam, $table);
    }
}

function print_stat_fulltext_fam(&$stat, &$fam, $table)
{
    echo sprintf($table['lineformat'],
    // sprintf("%s%s", $fam['name'], (strlen($fam['inherit'])==0)?'':sprintf(" (%s)", $fam['inherit'])),
    sprintf("%s%s", $fam['name'], (strlen($fam['inherit']) == 0) ? '' : ' *') , $fam['id'], $stat['search'][$fam['id']]['count'], $stat['search'][$fam['id']]['profile'], $stat['fulltext'][$fam['id']]['no_order']['time'], $stat['fulltext'][$fam['id']]['order_by_title']['time'], $stat['fulltext'][$fam['id']]['order_by_cdate']['time'], $stat['fulltext'][$fam['id']]['order_by_id']['time'], $stat['fulltext'][$fam['id']]['order_by_rank']['time']);
}

function print_stat_fulltext_distinct(&$stat)
{
    $table = array(
        'lineformat' => " %32s | %6s | %6s | %7s | %8s | %8s | %8s | %8s | %8s\n"
    );
    
    echo "\n\n\n=== Fulltext DISTINCT ===\n\n\n";
    
    echo sprintf($table['lineformat'], "Name", "Id", "Count", "Profile", "^O", "O(title)", "O(cdate)", "O(id)", "O(rank)");
    echo sprintf($table['lineformat'], "---", "---", "---", "---", "---", "---", "---", "---", "---");
    
    foreach ($stat['search'] as $fam) {
        print_stat_fulltext_fam_distinct($stat, $fam, $table);
    }
}

function print_stat_fulltext_fam_distinct(&$stat, &$fam, $table)
{
    echo sprintf($table['lineformat'],
    // sprintf("%s%s", $fam['name'], (strlen($fam['inherit'])==0)?'':sprintf(" (%s)", $fam['inherit'])),
    sprintf("%s%s", $fam['name'], (strlen($fam['inherit']) == 0) ? '' : ' *') , $fam['id'], $stat['search'][$fam['id']]['count'], $stat['search'][$fam['id']]['profile'], $stat['fulltext'][$fam['id']]['no_order']['distinct']['time'], $stat['fulltext'][$fam['id']]['order_by_title']['distinct']['time'], $stat['fulltext'][$fam['id']]['order_by_cdate']['distinct']['time'], $stat['fulltext'][$fam['id']]['order_by_id']['distinct']['time'], $stat['fulltext'][$fam['id']]['order_by_rank']['distinct']['time']);
}
/**
 * Bench functions
 */

function bench(&$stat)
{
    bench_info($stat);
    bench_search_fam_all($stat);
}

function bench_search_fam_all(&$stat)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['search'] = array();
    
    $s = new SearchDoc($dbaccess_freedom, -1);
    $s->setObjectReturn();
    $s->noViewControl();
    $s->addFilter("usefor != 'W'");
    $s->addFilter("usefor != 'S'");
    $s->search();
    
    $famList = array();
    while ($fam = $s->nextDoc()) {
        $fam->childs = array();
        $childFam = $fam->getChildFam($fam->id, false);
        $inherit = join(',', array_map(create_function('$v', 'global $dbaccess_freedom; return getNameFromId($dbaccess_freedom, $v);') , array_keys($childFam)));
        array_push($famList, array(
            'id' => $fam->id,
            'name' => $fam->name,
            'fromid' => $fam->fromid,
            'profid' => $fam->profid,
            'dprofid' => $fam->dprofid,
            'cprofid' => $fam->cprofid,
            'inherit' => $inherit
        ));
        
        if ($inherit != '') {
            echo sprintf("Childs of '%s' = [%s]\n", $fam->name, $inherit);
        }
    }
    /*
    $famList = array();
    $q = pg_query(getDbId($dbaccess_freedom), "SELECT id, name, title, fromid, profid, dprofid, cprofid FROM docfam WHERE usefor != 'W' AND usefor != 'S' ORDER BY id");
    if( $q === false ) {
    error_log(__FILE__." ".sprintf("Error: docfam id list query failed"));
    return false;
    }
    $famList = pg_fetch_all($q);
    */
    
    array_push($famList, array(
        'id' => 0,
        'name' => 'global',
        'fromid' => 0,
        'profid' => 'N/A',
        'dprofid' => 'N/A',
        'cprofid' => 'N/A'
    ));
    /*
    $fam_inherit = array();
    foreach( $famList as $fam ) {
    $fam_inherit[$fam['id']] = $fam['fromid'];
    }
    
    $inheritance = array();
    for( $idx_famList = 0; $idx_famList < count($famList); $idx_famList++ ) {
    $fam = $famList[$idx_famList];
    // echo sprintf("Processing family %s\n", $fam['id']);
    $inheritance[$fam['id']] = array();
    if( $fam['fromid'] != 0 ) {
      // echo sprintf("  Found a parent family %s\n", $fam['fromid']);
      array_push($inheritance[$fam['id']], $fam['fromid']);
      for( $i = 0; $i < count($inheritance[$fam['id']]); $i++ ) {
    $parent_id = $fam_inherit[ $inheritance[$fam['id']][$i] ];
    // echo sprintf("    Parent of %s is %s\n", $inheritance[$fam['id']][$i], $parent_id);
    if( $parent_id != 0 ) {
    // echo sprintf("      Adding %s\n", $parent_id);
    array_push($inheritance[$fam['id']], $parent_id);
    }
      }
      $famList[$idx_famList]['inherit'] = join(':', $inheritance[$fam['id']]);
      // echo sprintf("Inheritance of %s is %s\n", $fam['id'], $fam['inherit']);
    } else {
      $famList[$idx_famList]['inherit'] = '';
    }
    }
    // var_dump($famList);
    */
    
    echo sprintf("Found %s families to process.\n", count($famList));
    
    foreach ($famList as $fam) {
        bench_search_fam($stat, $fam);
    }
}

function bench_search_fam(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    if (!array_key_exists('search', $stat)) {
        return false;
    }
    
    $stat['search'][$fam['id']] = array();
    // id
    $stat['search'][$fam['id']]['id'] = $fam['id'];
    // name
    $stat['search'][$fam['id']]['name'] = $fam['name'];
    // fromid
    $stat['search'][$fam['id']]['fromid'] = $fam['fromid'];
    // doc count
    $q = pg_query(getDbId($dbaccess_freedom) , sprintf("SELECT count(id) FROM doc%s", pg_escape_string(($fam['id'] == 0) ? '' : $fam['id'])));
    if ($q === false) {
        error_log(__FILE__ . " " . sprintf("Error: doc count on family '%s' (%s) failed.", $fam['name'], $fam['id']));
        return false;
    }
    $d = pg_fetch_all($q);
    $stat['search'][$fam['id']]['count'] = $d[0]['count'];
    // inherit
    $stat['search'][$fam['id']]['inherit'] = $fam['inherit'];
    // profile
    if ($fam['profid'] == 0) {
        $stat['search'][$fam['id']]['profile'] = 'none';
    } else if ($fam['profid'] == $fam['id']) {
        $stat['search'][$fam['id']]['profile'] = 'dynamic';
    } else {
        $stat['search'][$fam['id']]['profile'] = 'static';
    }
    // search
    bench_search_fam_no_order($stat, $fam);
    bench_search_fam_order_by_title($stat, $fam);
    bench_search_fam_order_by_cdate($stat, $fam);
    bench_search_fam_order_by_id($stat, $fam);
    // regex
    bench_regex_fam_no_order($stat, $fam);
    bench_regex_fam_order_by_title($stat, $fam);
    bench_regex_fam_order_by_cdate($stat, $fam);
    bench_regex_fam_order_by_id($stat, $fam);
    // fulltext
    bench_fulltext_fam_no_order($stat, $fam);
    bench_fulltext_fam_order_by_title($stat, $fam);
    bench_fulltext_fam_order_by_cdate($stat, $fam);
    bench_fulltext_fam_order_by_id($stat, $fam);
    bench_fulltext_fam_order_by_rank($stat, $fam);
}
/**
 * Search
 */

function bench_search_fam_no_order(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['search'][$fam['id']]['no_order']['time'] = bench_searchdoc(array(
        'fam' => $fam['id']
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['no_order']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id']
        ));
    }
    
    $stat['search'][$fam['id']]['no_order']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'distinct' => true
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['no_order']['distinct']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id'],
            'distinct' => true
        ));
    }
}

function bench_search_fam_order_by_title(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['search'][$fam['id']]['order_by_title']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'title'
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['order_by_title']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id'],
            'orderby' => 'title'
        ));
    }
    
    $stat['search'][$fam['id']]['order_by_title']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'title',
        'distinct' => true
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['order_by_title']['distinct']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id'],
            'orderby' => 'title',
            'distinct' => true
        ));
    }
}

function bench_search_fam_order_by_cdate(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['search'][$fam['id']]['order_by_cdate']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'cdate'
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['order_by_cdate']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id'],
            'orderby' => 'cdate'
        ));
    }
    
    $stat['search'][$fam['id']]['order_by_cdate']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'cdate',
        'distinct' => true
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['order_by_cdate']['distinct']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id'],
            'orderby' => 'cdate',
            'distinct' => true
        ));
    }
}

function bench_search_fam_order_by_id(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['search'][$fam['id']]['order_by_id']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'id'
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['order_by_id']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id'],
            'orderby' => 'id'
        ));
    }
    
    $stat['search'][$fam['id']]['order_by_id']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'id',
        'distinct' => true
    ));
    
    if ($fam['fromid'] != '0') {
        $stat['search'][$fam['id']]['only']['order_by_id']['distinct']['time'] = bench_searchdoc(array(
            'fam' => - $fam['id'],
            'orderby' => 'id',
            'distinct' => true
        ));
    }
}
/**
 * Regex
 */

function bench_regex_fam_no_order(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['regex'][$fam['id']]['no_order']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word']))
    ));
    
    $stat['regex'][$fam['id']]['no_order']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word'])) ,
        'distinct' => true
    ));
}

function bench_regex_fam_order_by_title(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['regex'][$fam['id']]['order_by_title']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'title',
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word']))
    ));
    
    $stat['regex'][$fam['id']]['order_by_title']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'title',
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word'])) ,
        'distinct' => true
    ));
}

function bench_regex_fam_order_by_cdate(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['regex'][$fam['id']]['order_by_cdate']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'cdate',
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word']))
    ));
    
    $stat['regex'][$fam['id']]['order_by_cdate']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'cdate',
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word'])) ,
        'distinct' => true
    ));
}

function bench_regex_fam_order_by_id(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    $stat['regex'][$fam['id']]['order_by_id']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'id',
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word']))
    ));
    
    $stat['regex'][$fam['id']]['order_by_id']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'orderby' => 'id',
        'filter' => sprintf("svalues ~* '%s'", pg_escape_string($parms['word'])) ,
        'distinct' => true
    ));
}
/**
 * Fulltext
 */

function bench_fulltext_fam_no_order(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    include_once ('FDL/Class.DocSearch.php');
    
    $ret_sqlfilter = array();
    $ret_orderby = '';
    $ret_keys = '';
    DocSearch::getFullSqlFilters($parm['word'], $ret_filter, $ret_orderby, $ret_keys);
    
    $stat['fulltext'][$fam['id']]['no_order']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter
    ));
    
    $stat['fulltext'][$fam['id']]['no_order']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'distinct' => true
    ));
}

function bench_fulltext_fam_order_by_title(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    include_once ('FDL/Class.DocSearch.php');
    
    $ret_sqlfilter = array();
    $ret_orderby = '';
    $ret_keys = '';
    DocSearch::getFullSqlFilters($parm['word'], $ret_filter, $ret_orderby, $ret_keys);
    
    $stat['fulltext'][$fam['id']]['order_by_title']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => 'title'
    ));
    
    $stat['fulltext'][$fam['id']]['order_by_title']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => 'title',
        'distinct' => true
    ));
}

function bench_fulltext_fam_order_by_cdate(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    include_once ('FDL/Class.DocSearch.php');
    
    $ret_sqlfilter = array();
    $ret_orderby = '';
    $ret_keys = '';
    DocSearch::getFullSqlFilters($parm['word'], $ret_filter, $ret_orderby, $ret_keys);
    
    $stat['fulltext'][$fam['id']]['order_by_cdate']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => 'cdate'
    ));
    
    $stat['fulltext'][$fam['id']]['order_by_cdate']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => 'cdate',
        'distinct' => true
    ));
}

function bench_fulltext_fam_order_by_id(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    include_once ('FDL/Class.DocSearch.php');
    
    $ret_sqlfilter = array();
    $ret_orderby = '';
    $ret_keys = '';
    DocSearch::getFullSqlFilters($parm['word'], $ret_filter, $ret_orderby, $ret_keys);
    
    $stat['fulltext'][$fam['id']]['order_by_id']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => 'id'
    ));
    
    $stat['fulltext'][$fam['id']]['order_by_id']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => 'id',
        'distinct' => true
    ));
}

function bench_fulltext_fam_order_by_rank(&$stat, &$fam)
{
    global $dbaccess_freedom;
    global $action;
    global $parms;
    
    include_once ('FDL/Class.DocSearch.php');
    
    $ret_sqlfilter = array();
    $ret_orderby = '';
    $ret_keys = '';
    DocSearch::getFullSqlFilters($parm['word'], $ret_filter, $ret_orderby, $ret_keys);
    
    $stat['fulltext'][$fam['id']]['order_by_rank']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => $ret_orderby
    ));
    
    $stat['fulltext'][$fam['id']]['order_by_rank']['distinct']['time'] = bench_searchdoc(array(
        'fam' => $fam['id'],
        'filter' => $ret_filter,
        'orderby' => $ret_orderby,
        'distinct' => true
    ));
}
/**
 * Generic bench_searchdoc()
 */

function bench_searchdoc($opt)
{
    global $dbaccess_freedom;
    global $parms;
    
    if (!array_key_exists('dbaccess', $opt)) {
        $opt['dbaccess'] = $dbaccess_freedom;
    }
    
    $s = new SearchDoc($opt['dbaccess'], $opt['fam']);
    $s->setDebugMode();
    $s->setObjectReturn();
    
    $s->orderby = '';
    if (array_key_exists('orderby', $opt)) {
        $s->orderby = $opt['oderby'];
    }
    
    $s->slice = $parms['limit'];
    if (array_key_exists('limit', $opt)) {
        $s->slice = $opt['limit'];
    }
    
    if (array_key_exists('distinct', $opt)) {
        if ($opt['distinct'] === false || $opt['distinct'] === true) {
            $s->distinct = $opt['distinct'];
        }
    }
    
    if (array_key_exists('filter', $opt)) {
        if (is_array($opt['filter'])) {
            foreach ($opt['filter'] as $filter) {
                $s->addFilter($filter);
            }
        } else {
            $s->addFilter($opt['filter']);
        }
    }
    
    $s->search();
    
    $debugInfo = $s->getDebugInfo();
    return $debugInfo['delay'];
}
/**
 * Bench info header
 */

function bench_info($stat)
{
    global $dbaccess_freedom;
    global $dbaccess_core;
    global $action;
    global $parms;
    
    $stat['login'] = $action->user->login;
    
    $stat['uid'] = $action->user->id;
    
    $q = pg_query(getDbId($dbaccess_freedom) , "SELECT count(id) AS count FROM doc");
    if ($q === false) {
        error_log(__FILE__ . " " . sprintf("Error: count doc query failed"));
        return false;
    }
    $d = pg_fetch_all($q);
    $stat['doc_count'] = $d[0]['count'];
    
    $q = pg_query(getDbId($dbaccess_freedom) , "SELECT count(*) AS count FROM docperm");
    if ($q === false) {
        error_log(__FILE__ . " " . sprintf("Error: count docperm query failed"));
        return false;
    }
    $d = pg_fetch_all($q);
    $stat['docperm_count'] = $d[0]['count'];
    
    $parent_groups = $action->user->getGroupsId();
    $all_groups_list = array();
    foreach ($parent_groups as $group) {
        array_push($all_groups_list, $group);
    }
    $group_count = 0;
    for ($i = 0; $i < count($all_groups_list); $i++) {
        $gid = $all_groups_list[$i];
        $group_count++;
        $group = new User($dbaccess_core, $gid);
        if (!is_object($group)) {
            error_log(__FILE__ . " " . sprintf("Error: invalid group with id '%s'"));
            return false;
        }
        $groups_list = $group->getGroupsId();
        foreach ($groups_list as $g) {
            if (!in_array($g, $all_groups_list)) {
                array_push($all_groups_list, $g);
            }
        }
    }
    $stat['user_groups_count'] = $group_count++;
    
    echo sprintf("login (uid)       = %s (%s)\n", $stat['login'], $stat['uid']);
    echo sprintf("doc count         = %s\n", $stat['doc_count']);
    echo sprintf("docperm count     = %s\n", $stat['docperm_count']);
    echo sprintf("user groups count = %s\n", $stat['user_groups_count']);
    echo sprintf("search word       = %s\n", $parms['word']);
    echo sprintf("limit             = %s\n", $parms['limit']);
}
?>