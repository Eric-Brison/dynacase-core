<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Errors code used to database query errors
 * @class ErrorCodeDB
 * @see ErrorCode
 * @brief List all error code database errors
 * @see ErrorCode
 */
class ErrorCodeDB
{
    /**
     * the query cannot be executed
     */
    const DB0001 = 'query error : %s';
    /**
     * the query cannot be executed after prepare
     */
    const DB0002 = 'query error : %s';
    /**
     * when try to create automatically DbObj Table
     * the sqlcreate attribute if probably wrong
     */
    const DB0003 = 'Table "%s" doesn\'t exist and cannot be created : %s';
    /**
     * when try to create automatically DbObj Table
     * the sqlcreate attribute if probably wrong
     */
    const DB0004 = 'Table "%s" cannot be updated : %s';
    /**
     * the query cannot be prepared
     */
    const DB0005 = 'query prepare error : %s';
    /**
     * the prepare statement cannot be done
     */
    const DB0006 = 'preparing statement : %s';
    /**
     * the execute statement cannot be done
     */
    const DB0007 = 'execute statement : %s';
    /**
     * the query cannot be sent to server
     */
    const DB0008 = ' sending query : %s';
    /**
     * missing column on table
     */
    const DB0009 = 'no auto update for "%s" table';
    /**
     * simple query error
     */
    const DB0100 = 'simple query error "%s" for query "%s"';
    /**
     * database connection error
     */
    const DB0101 = 'cannot connect to "%s"';
    /**
     * simple query error connect
     */
    const DB0102 = 'cannot connect to "%s". Simple query error "%s" for query "%s"';
}
