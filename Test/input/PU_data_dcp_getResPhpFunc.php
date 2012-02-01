<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

function PU_data_dcp_getResPhpFunc_getGravite()
{
    return array(
        0 => array(
            "mineure",
            "Mi"
        ) ,
        1 => array(
            "majeure",
            "Ma"
        ) ,
        2 => array(
            "bloquante",
            "Bl"
        )
    );
}

function PU_data_dcp_getResPhpFunc_getTitle_title($dbaccess, $title)
{
    return PU_data_dcp_getResPhpFunc_getTitle_fam_title($dbaccess, "TST_GETRESPHPFUNC", $title);
}

function PU_data_dcp_getResPhpFunc_getTitle_fam_title($dbaccess, $famId, $title)
{
    if (!is_numeric($famId)) {
        $famId = getIdFromName($dbaccess, $famId);
    }
    $s = new SearchDoc($dbaccess, $famId);
    $s->setObjectReturn();
    $s->search();
    $ret = array();
    while ($doc = $s->nextDoc()) {
        if (strlen($title) > 0 && strpos($doc->getTitle() , $title) === false) {
            continue;
        }
        $ret[] = array(
            $doc->getTitle() ,
            $doc->getTitle()
        );
    }
    return $ret;
}

function PU_data_dcp_getResPhpFunc_getTitle_reverse_fam_title($title, $famId, $dbaccess)
{
    return PU_data_dcp_getResPhpFunc_getTitle_fam_title($dbaccess, $famId, $title);
}

function PU_data_dcp_getResPhpFunc_mirror_args()
{
    $ret = array();
    $argList = array();
    for ($i = 0; $i < func_num_args(); $i++) {
        $argList[] = func_get_arg($i);
    }
    $argString = join(', ', $argList);
    $ret[] = array(
        $argString,
        $argString
    );
    return $ret;
}

function PU_data_dcp_getResPhpFunc_latin1($a)
{
    $data = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $a);
    if (seems_utf8($data)) $data2 = 'utf8';
    else $data2 = 'other';
    return array(
        array(
            $data,
            $data2
        )
    );
}

function PU_data_dcp_getResPhpFunc_wrongArray($a)
{
    $data = $a;
    return array(
        $data,
        $data
    );
}
?>