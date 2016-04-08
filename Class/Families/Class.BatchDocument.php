<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Method for batch freedom processes
 *
 */
namespace Dcp\Core;
class BatchDocument extends \Dcp\Family\Portfolio
{
    function filterContent()
    {
    }
    /**
     * return document includes in portfolio an in each of its guide or searched inside portfolio
     * @param bool $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter to add list sql filter for selected document
     * @param int $famid family identifier to restrict search
     * @param bool $insertguide if true merge each content of guide else same as a normal folder
     * @return array array of document array
     */
    function getContent($controlview = true, array $filter = array() , $famid = "", $insertguide = true, $unused = "")
    {
        
        return parent::getContent($controlview, $filter, $famid, $insertguide);
    }
}
