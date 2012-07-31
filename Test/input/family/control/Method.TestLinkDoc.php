<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Ooo Layout test
 *
 * @author Anakeen 2010
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package DCP
 */
/**
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _TST_DOCLINK extends Doc
{
    /**
     * @end-method-ignore
     */
    public function linkOne()
    {
        return 'http://www.test.net/';
    }
    public function linkTwo()
    {
        return sprintf('http://www.test.net/?b=%s', rawurlencode($this->getTitle()));
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
?>
