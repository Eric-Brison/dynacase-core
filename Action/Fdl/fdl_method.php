<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Apply document methods
 *
 * @author Anakeen
 * @version $Id: fdl_method.php,v 1.8 2008/12/12 14:38:29 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
function fdl_method(Action & $action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = $action->getArgument("id", 0);
    $method = $action->getArgument("method");
    $zone = $action->getArgument("zone");
    $noredirect = (strtolower(substr($action->getArgument("redirect") , 0, 1)) == "n");
    
    $doc = new_Doc($dbaccess, $docid);
    $opt = '';
    $err = '';
    
    if ($doc && $doc->isAlive()) {
        
        $err = $doc->control("view");
        if ($err != "") $action->exitError($err);
        if (!strpos($method, '(')) $method.= '()';
        if (strpos($method, '::') === false) $method = '::' . $method;
        $match = commentMethodMatch($doc, $method, '@apiExpose');
        if ($match) {
            $err = $doc->ApplyMethod($method);
        } else {
            $err = sprintf(_("Method %s cannot be call by client. Must be exposable method") , $method);
        }
    }
    
    if ($err != "") $action->AddWarningMsg($err);
    $action->AddLogMsg(sprintf(_("method %s executed for %s ") , $method, $doc->title));
    if ($err) $doc->addComment(sprintf(_("method %s not executed : %s") , $method, $err) , HISTO_ERROR);
    else $doc->addComment(sprintf(_("method %s executed") , $method) , HISTO_NOTICE);
    
    if (!$noredirect) {
        if ($zone) $opt = "&zone=$zone";
        if ($location = $_SERVER["HTTP_REFERER"]) {
            Header("Location: $location");
            exit;
        } else {
            redirect($action, "FDL", sprintf("FDL_CARD%s&id=%d", $opt, $doc->id));
        }
    } else {
        if ($err) $action->lay->template = $err;
        else $action->lay->template = sprintf(_("method %s applied to document %s #%d") , $method, $doc->title, $doc->id);
    }
}
/**
 * Verify if a method of an object contains the $comment
 * @param $object
 * @param string $method like "::test()"
 * @param string $comment
 * @return bool
 */
function commentMethodMatch(&$object, $method, $comment)
{
    $parseMethod = new parseFamilyMethod();
    $parseMethod->parse($method);
    $err = $parseMethod->getError();
    if ($err) return $err;
    
    $staticClass = $parseMethod->className;
    if (!$staticClass) $staticClass = get_class($object);
    $methodName = $parseMethod->methodName;
    
    try {
        $refMeth = new ReflectionMethod($staticClass, $methodName);
        $mComment = $refMeth->getDocComment();
        if (!preg_match('/' . $comment . '\b/', $mComment)) {
            global $action;
            $syserr = ErrorCode::getError("DOC1100", $refMeth->getDeclaringClass()->getName() , $refMeth->getName() , $object);
            $action->log->error($syserr);
            return false;
        }
    }
    catch(Exception $e) {
        $err = $e->getMessage();
    }
    return true;
}
?>
