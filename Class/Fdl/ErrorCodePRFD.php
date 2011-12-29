<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Created by JetBrains PhpStorm.
 * User: eric
 * Date: 01/12/11
 * Time: 18:27
 * To change this template use File | Settings | File Templates.
 */

class ErrorCodePRFD
{
    /**
     * the profil id must reference a document
     */
    const PRFD0001 = 'profil identificator %s is not found';
    /**
     * the profil id must reference a family profil or family itself
     */
    const PRFD0002 = 'profil %s is not a family profil';
}
