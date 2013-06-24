<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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