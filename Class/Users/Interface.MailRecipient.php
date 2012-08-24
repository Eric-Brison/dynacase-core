<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

interface MailRecipient {
    /**
     * return mail address like "john" <john@example.net>
     * @return string
     */
    public function getMail();
    /**
     * return attribute used to filter from keyword
     * @return string
     */
    public static function getMailAttribute();
}

