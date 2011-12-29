<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class ErrorCodeACCS
{
    /**
     * the application name must be register before define access
     */
    const ACCS0001 = 'application %s is not found, cannot apply access';
    /**
     * acl must be referenced in application
     */
    const ACCS0002 = 'acl %s not define for %s application, cannot apply acces';
    /**
     * user must be created before apply access to him
     */
    const ACCS0003 = 'user %s not found, cannot apply access';
    /**
     * acl syntax is [alphanum|_]{1,63}
     */
    const ACCS0004 = 'acl syntax error, "%s" must be an alphanum limit to 63, cannot apply access';
    /**
     * application name syntax is [alphanum|_]{1,63}
     */
    const ACCS0005 = 'appname syntax error, "%s" must be an alphanum limit to 63, cannot apply access';
    /**
     * application name is required
     */
    const ACCS0006 = 'appname not set, cannot apply access';
    /**
     * user/group name is required
     */
    const ACCS0007 = 'user not set, cannot apply access';
}
