<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Return portfolio context
 * @param Doc $doc
 * @return array
 */
function portfolio_get_context($doc)
{
    
    $pfctx = $doc->getUTag("PORTFOLIOCTX");
    if (!empty($pfctx) && !empty($pfctx->comment)) {
        try {
            $pfctx = unserialize($pfctx->comment);
        }
        catch(Exception $e) {
            $pfctx = array();
        }
    }
    if (empty($pfctx) || !is_array($pfctx)) {
        $pfctx = array(
            'listtype' => $doc->getParamValue('pfl_liststyle') ,
        );
        if (empty($pfctx['listtype'])) {
            $pfctx['listtype'] = 'icon';
        }
    }
    if (isset($_REQUEST['foliolisttype'])) {
        $pfctx['listtype'] = $_REQUEST['foliolisttype'];
        portfolio_set_context($doc, $pfctx);
    }
    //foreach(explode("\n", print_r($pfctx, true)) as $tmp){error_log($tmp);}
    return $pfctx;
}
/**
 * Set portfolio context
 * @param Doc $doc
 * @param array context
 */
function portfolio_set_context($doc, $context)
{
    
    $doc->addUTag($doc->userid, "PORTFOLIOCTX", serialize($context));
}
?>