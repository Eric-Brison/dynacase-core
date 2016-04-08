<?php
/*
 * @author Anakeen
 * @package FDL
 */

class storeInfo
{
    const NO_ERROR = 0;
    /**
     * preCreated has return an error
     */
    const CREATE_ERROR = 2;
    /**
     * database record has return error
     */
    const UPDATE_ERROR = 3;
    /**
     * all constraints are not validated
     */
    const CONSTRAINT_ERROR = 4;
    /**
     * preStore has returned an error
     */
    const PRESTORE_ERROR = 5;
    /**
     * @var string message returned by Doc::refresh
     */
    public $refresh = '';
    /**
     * @var string message returned by Doc::postStore
     */
    public $postStore = '';
    /**
     * @deprecated use postStore attribute instead
     * @var string message returned by Doc::postStore
     */
    public $postModify = '';
    /**
     * @var string message returned by Doc::preStore
     */
    public $preStore = '';
    /**
     * set of information about constraint test indexed by attribute identifier and rank index if multiple attribute
     * @var array message returned by Doc::verifyAllConstraints
     */
    public $constraint = array();
    /**
     * @var string store error, empty if no errors
     */
    public $error = '';
    /**
     * @var int error code
     */
    public $errorCode = 0;
}
