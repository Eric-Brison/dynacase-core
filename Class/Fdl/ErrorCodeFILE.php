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
     * @errorCode
     * exportfile could not get image size from cached image (the filecache image might be empty or corrupted)
     */
    const FILE0001 = 'exportfile could not get size of cached image "%s"';
    /**
     * @errorCode
     * exportfile's cached image have an invalid width and/or height equal to zero
     */
    const FILE0002 = 'exportfile found an invalid width/height (%s/%s) for cached image "%s"';
    /**
     * @errorCode
     * exportfile count not get image size from thumbnail image (the thumbnail iamge might be empty or corrupted)
     */
    const FILE0003 = 'exportfile could not get size of thumbnail image "%s"';
    /**
     * @errorCode
     * exportfile's thumbnail image have an invalid width and/or height equal to zero
     */
    const FILE0004 = 'exportfile found an invalid width/height (%s/%s) for thumbnail image "%s"';
    /**
     * @errorCode
     * dbaccess.php file is needed
     */
    const FILE0005 = 'dbaccess file not found "%s"';
    /**
     * @errorCode
     * dbaccess.php file is incomplete
     */
    const FILE0006 = 'dbaccess.php file does not contain freedom_authtypeparams variable. May be old syntax for configuration file';
    /**
     * @errorCode
     * The given file could not be stored in the vault because it does
     * not exists or the pathname is not a file (it may be a directory,
     * socket, etc.).
     */
    const FILE0007 = 'Could not store file "%s" in vault because the file does not exists';
    /**
     * @errorCode
     * The given file could not be stored in the vault because it isn't
     * readable (check Apache user read access rights on this file).
     */
    const FILE0008 = 'Could not store file "%s" in vault because the file is not readable';
    /**
     * @errorCode
     * The given file could not be registered in the vault. This might
     * be due to a faulty database connection, incorrect rights on
     * vault's dirs, or I/O errors.
     * The underlying error message is displayed and might provide
     * additional informations on the error's condition.
     */
    const FILE0009 = 'Could not register file "%s" in document: %s';
    /**
     * @errorCode
     * The given file has been registered in the vault but we could not
     * get back its information. This might be due to a faulty database
     * connection.
     */
    const FILE0010 = 'Could not get back informations for registered file "%s" in document.';
}
