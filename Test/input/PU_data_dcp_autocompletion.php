<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
*/

function PU_data_dcp_autocompletion_getGravite()
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

function PU_data_dcp_autocompletion_getTitle_title($dbaccess, $title)
{
    return PU_data_dcp_autocompletion_getTitle_fam_title($dbaccess, "TST_AUTOCOMPLETION", $title);
}

function PU_data_dcp_autocompletion_getTitle_fam_title($dbaccess, $famId, $title)
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

function PU_data_dcp_autocompletion_getTitle_reverse_fam_title($title, $famId, $dbaccess)
{
    return PU_data_dcp_autocompletion_getTitle_fam_title($dbaccess, $famId, $title);
}

function PU_data_dcp_autocompletion_bug_2108_ok($text, $ct)
{
    return array(
        array(
            "$text, $ct",
            "$text, $ct"
        )
    );
}

function PU_data_dcp_autocompletion_bug_2108($ct, $text)
{
    return array(
        array(
            "$ct, $text",
            "$ct, $text"
        )
    );
}

function PU_data_dcp_autocompletion_relation_2($ct_r1, $ct, $s_text)
{
    return array(
        array(
            "$ct, $ct_r1, $s_text",
            "$ct, $ct_r1, $s_text"
        )
    );
}

function PU_data_dcp_autocompletion_relation_3($s_text, $ct, $ct_r1)
{
    return array(
        array(
            "$s_text, $ct, $ct_r1",
            "$s_text, $ct, $ct_r1"
        )
    );
}
?>