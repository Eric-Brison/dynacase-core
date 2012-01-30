<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Error codes used for file operations
 * @class ErrorCodeFILE
 * @see ErrorCode
 * @brief List all error code for file operations
 */
class ErrorCodeFILE
{
    /**
     * exportfile could not get image size from cached image (the filecache image might be empty or corrupted)
     */
    const FILE0001 = 'exportfile could not get size of cached image "%s"';
    /**
     * exportfile's cached image have an invalid width and/or height equal to zero
     */
    const FILE0002 = 'exportfile found an invalid width/height (%s/%s) for cached image "%s"';
    /**
     * exportfile count not get image size from thumbnail image (the thumbnail iamge might be empty or corrupted)
     */
    const FILE0003 = 'exportfile could not get size of thumbnail image "%s"';
    /**
     * exportfile's thumbnail image have an invalid width and/or height equal to zero
     */
    const FILE0004 = 'exportfile found an invalid width/height (%s/%s) for thumbnail image "%s"';
    /**
     * dbaccess.php file is needed
     */
    const FILE0005 = 'dbaccess file not found "%s"';
    /**
     * dbaccess.php file is incomplete
     */
    const FILE0006 = 'dbaccess.php file does not contain freedom_authtypeparams variable. May be old syntax for configuration file';
}
