<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

interface IMailRecipient {
    /**
     * return a RFC822-compliant mail address like "john" <john@example.net>
     * @return string
     */
    public function getMail();

    /**
     * return a mail address in a user-friendly representation, which
     * might not be RFC822-compliant.
     * (e.g. "John Doe (john.doe (at) EXAMPLE.NET)")
     * @return string
     */
    public function getMailTitle();
    /**
     * return attribute used to filter from keyword
     * @return string
     */
    public static function getMailAttribute();
}

