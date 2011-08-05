<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edition to affect document
 *
 * @author Anakeen 2011
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */
/**
 * All view css stylesheets in one single stylesheets
 * @param Action &$action current action
 */
function allviewcss(Action & $action)
{
    $jurl = "WHAT/Layout";
    
    $static_css = array();
    $dynamic_css = array();
    
    $dynamic_css[] = "CORE/Layout/core.css";
    $dynamic_css[] = "FDL/Layout/freedom.css";
    
    setHeaderCache("text/css");
    $action->lay->template = "";
    
    RessourcePacker::pack_css($action, $static_css, $dynamic_css);
}
?>