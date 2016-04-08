<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Utils;

class XDOMDocumentException extends \Exception
{
    public $libXMLErrors = array();
    
    public function __construct($message, &$libXMLErrors = array())
    {
        $this->message = $message;
        if (count($libXMLErrors) <= 0) {
            $libXMLErrors[] = new \libXMLError();
        }
        $this->libXMLErrors = $libXMLErrors;
    }
}

class XDOMDocument extends \DOMDocument
{
    public $libXMLErrorIgnoreCodes = array();
    protected static $libXmlIntErr = null;
    protected static function enableLibXMLErrors()
    {
        if (self::$libXmlIntErr !== null) {
            throw new XDOMDocumentException(sprintf("libXMLIntErr is already initialized."));
        }
        self::$libXmlIntErr = libxml_use_internal_errors(true);
        libxml_clear_errors();
    }
    protected static function restoreLibXMLErrors()
    {
        if (self::$libXmlIntErr === null) {
            throw new XDOMDocumentException(sprintf("Uninitialized libXmlIntErr"));
        }
        libxml_clear_errors();
        libxml_use_internal_errors(self::$libXmlIntErr);
        self::$libXmlIntErr = null;
    }
    public function setLibXMLErrorIgnoreCodes($ignoreCodes)
    {
        $this->libXMLErrorIgnoreCodes = $ignoreCodes;
    }
    public function getLibXMLErrorIgnoreCodes()
    {
        return $this->libXMLErrorIgnoreCodes;
    }
    /**
     * @return \LibXMLError[]
     */
    protected function getLibXMLErrors()
    {
        $libXMLErrors = libxml_get_errors();
        $libXMLErrorsFiltered = array();
        foreach ($libXMLErrors as & $libXMLError) {
            if (in_array($libXMLError->code, $this->libXMLErrorIgnoreCodes)) {
                continue;
            }
            $libXMLErrorsFiltered[] = $libXMLError;
        }
        unset($libXMLError);
        return $libXMLErrorsFiltered;
    }
    public function load($filename, $options = 0, \libXMLError & $error = null)
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::load($filename, $options = 0);
        $errors = $this->getLibXMLErrors();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if (count($errors) <= 0) {
                $error = new \libXMLError();
                $error->message = 'general parsing error';
                $error->level = LIBXML_ERR_FATAL;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = $filename;
                $errors[] = $error;
            }
            throw new XDOMDocumentException(sprintf("Error loading XML file '%s': %s", $filename, $error[0]->message) , $errors);
        }
        return true;
    }
    public function loadXML($source, $options = 0, &$errors = array())
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadXML($source, $options);
        $errors = $this->getLibXMLErrors();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if (count($errors) <= 0) {
                /*
                 * If source is empty, libxml_get_last_error report no errors at all
                 * so we mimic the expected error message.
                */
                if ($source == '') {
                    $error = new \libXMLError();
                    $error->message = 'general parsing error';
                    $error->level = LIBXML_ERR_FATAL;
                    $error->code = 0;
                    $error->line = 0;
                    $error->column = 0;
                    $error->file = '';
                } else {
                    $error = new \libXMLError();
                    $error->message = 'Empty string supplied as input';
                    $error->level = LIBXML_ERR_ERROR;
                    $error->code = 0;
                    $error->line = 0;
                    $error->column = 0;
                    $error->file = '';
                }
                $errors[] = $error;
            }
            throw new XDOMDocumentException(sprintf("Error loading XML data: %s", $errors[0]->message) , $errors);
        }
        return true;
    }
    /**
     * @param string $filename
     * @param int $options
     * @param \libXMLError[] $errors
     * @return bool
     * @throws XDOMDocumentException
     */
    public function loadHTMLFile($filename, $options = 0, &$errors = array())
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadHTMLFile($filename, $options);
        $errors = $this->getLibXMLErrors();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if (count($errors) <= 0) {
                $error = new \libXMLError();
                $error->message = 'general parsing error';
                $error->level = LIBXML_ERR_FATAL;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = $filename;
                $errors[] = $error;
            }
            throw new XDOMDocumentException(sprintf("Error loading HTML file '%s': %s", $filename, $errors[0]->message) , $errors);
        }
        return true;
    }
    public function loadHTML($source, $options = 0, &$errors = array())
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadHTML($source, $options);
        $errors = $this->getLibXMLErrors();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if (count($errors) <= 0) {
                /*
                 * If source is empty, libxml_get_last_error report no errors at all
                 * so we mimic the expected error message.
                */
                if ($source == '') {
                    $error = new \libXMLError();
                    $error->message = 'general parsing error';
                    $error->level = LIBXML_ERR_FATAL;
                    $error->code = 0;
                    $error->line = 0;
                    $error->column = 0;
                    $error->file = '';
                } else {
                    $error = new \libXMLError();
                    $error->message = 'Empty string supplied as input';
                    $error->level = LIBXML_ERR_ERROR;
                    $error->code = 0;
                    $error->line = 0;
                    $error->column = 0;
                    $error->file = '';
                }
                $errors[] = $error;
            }
            throw new XDOMDocumentException(sprintf("Error loading HTML data: %s", $errors[0]->message) , $errors);
        }
        return true;
    }
}
