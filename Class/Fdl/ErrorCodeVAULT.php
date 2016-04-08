<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Errors code used when Vault Manager
 * @class ErrorCodeVAULT
 * @brief List all error code for Vault Manager
 * @see VaultManager
 */
class ErrorCodeVAULT
{
    /**
     * @errorCode File cannot be stored to vault
     * @see VaultManager::storeFile
     */
    const VAULT0001 = 'Cannot store file : %s';
    /**
     * @errorCode Temporary file cannot be stored to vault
     * @see VaultManager::storeTemporaryFile
     */
    const VAULT0002 = 'Cannot store temporary file : %s';
    /**
     * @errorCode Temporary file cannot be stored to vault because user's session not found
     * @see VaultManager::storeTemporaryFile
     */
    const VAULT0003 = 'Cannot store temporary file : no session detected';
    /**
     * @errorCode File cannot be destroyed
     * @see VaultManager::destroyFile
     */
    const VAULT0004 = 'Cannot destroy file : %s';
}
