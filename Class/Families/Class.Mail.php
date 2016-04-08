<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * INterface to send mail
 *
 */
namespace Dcp\Core;
class MailEdit extends \Dcp\Family\Document
{
    var $defaultedit = "FDL:EDITMAILDOC";
    /**
     * @templateController
     */
    function editmaildoc()
    {
        $this->editattr();
    }

}