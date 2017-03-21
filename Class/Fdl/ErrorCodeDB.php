<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace {
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
         * @errorCode
         * the query cannot be executed
         */
        const DB0001 = 'query error : %s';
        /**
         * @errorCode
         * the query cannot be executed after prepare
         */
        const DB0002 = 'query error : %s';
        /**
         * @errorCode
         * when try to create automatically DbObj Table
         * the sqlcreate attribute if probably wrong
         */
        const DB0003 = 'Table "%s" doesn\'t exist and cannot be created : %s';
        /**
         * @errorCode
         * when try to create automatically DbObj Table
         * the sqlcreate attribute if probably wrong
         */
        const DB0004 = 'Table "%s" cannot be updated : %s';
        /**
         * @errorCode
         * the query cannot be prepared
         */
        const DB0005 = 'query prepare error : %s';
        /**
         * @errorCode
         * the prepare statement cannot be done
         */
        const DB0006 = 'preparing statement : %s';
        /**
         * @errorCode
         * the execute statement cannot be done
         */
        const DB0007 = 'execute statement : %s';
        /**
         * @errorCode
         * the query cannot be sent to server
         */
        const DB0008 = 'sending query : %s';
        /**
         * @errorCode missing column on table
         */
        const DB0009 = 'no auto update for "%s" table';
        /**
         * @errorCode The lock prefix is converted to a 4 bytes numbre and it is limited to 4 characters
         * @see DbObj::lockPoint()
         */
        const DB0010 = 'The prefix lock "%s" must not exceed 4 characters';
        /**
         * @errorCode Lock is efficient only into a transaction
         * @see DbObj::lockPoint()
         */
        const DB0011 = 'The lock "%d-%s" must be set inside a savePoint transaction';
        /**
         * @errorCode Lock identifier is not a valid int32
         * @see DbObj::lockPoint()
         */
        const DB0012 = 'Lock identifier (%s) is not a valid int32';
        /**
         * @errorCode
         * simple query error
         */
        const DB0100 = 'simple query error "%s" for query "%s"';
        /**
         * @errorCode
         * database connection error
         */
        const DB0101 = 'cannot connect to "%s"';
        /**
         * @errorCode
         * simple query error connect
         */
        const DB0102 = 'cannot connect to "%s". Simple query error "%s" for query "%s"';
        /**
         * @errorCode  Vault identifier key cannot be generated
         * @see VaultDiskStorage::getNewVaultId
         */
        const DB0103 = 'Cannot generate vault identifier';
        /**
         * @errorCode  Vault identifier key must be verify if not already in use
         * @see VaultDiskStorage::getNewVaultId
         */
        const DB0104 = 'Cannot verify vault identifier : %s';
        /**
         * @errorCode
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}
namespace Dcp\Db {
    class Exception extends \Dcp\Exception
    {
        /**
         * for beautifier
         */
        private function _bo()
        {
            if (true) return;
        }
    }
}
