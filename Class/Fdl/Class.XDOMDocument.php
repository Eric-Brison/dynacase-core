<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Utils;

class XDOMDocumentException extends \Exception
{
    public $libXMLError = null;
    
    public function __construct($message, \libXMLError & $libXMLError = null)
    {
        $this->message = $message;
        if ($libXMLError === null) {
            $libXMLError = new \libXMLError();
        }
        $this->libXMLError = $libXMLError;
    }
}

class XDOMDocument extends \DOMDocument
{
    protected static $libXmlIntErr = null;
    protected static function enableLibXMLErrors()
    {
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
        $libXmlIntErr = null;
    }
    /**
     * @return \LibXMLError|null
     */
    protected static function getLibXMLLastError()
    {
        $libxmlError = libxml_get_last_error();
        if ($libxmlError === false) {
            return null;
        }
        return $libxmlError;
    }
    public function load($filename, $options = 0, \libXMLError & $error = null)
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::load($filename, $options = 0);
        $error = self::getLibXMLLastError();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if ($error === null) {
                $error = new \libXMLError();
                $error->message = 'general parsing error';
                $error->level = LIBXML_ERR_FATAL;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = $filename;
            }
            throw new XDOMDocumentException(sprintf("Error loading XML file '%s': %s", $filename, $error->message) , $error);
        }
        return true;
    }
    public function loadXML($source, $options = 0, \libXMLError & $error = null)
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadXML($source, $options);
        $error = self::getLibXMLLastError();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            /*
             * If source is empty, libxml_get_last_error report no errors at all
             * so we mimic the expected error message.
            */
            if ($error === null) {
                $error = new \libXMLError();
                $error->message = 'general parsing error';
                $error->level = LIBXML_ERR_FATAL;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = '';
            }
            if ($source == '') {
                $error = new \libXMLError();
                $error->message = 'Empty string supplied as input';
                $error->level = LIBXML_ERR_ERROR;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = '';
            }
            throw new XDOMDocumentException(sprintf("Error loading XML data: %s", $error->message) , $error);
        }
        return true;
    }
    public function loadHTMLFile($filename, $options = 0, \libXMLError & $error = null)
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadHTMLFile($filename, $options);
        $error = self::getLibXMLLastError();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if ($error === null) {
                $error = new \libXMLError();
                $error->message = 'general parsing error';
                $error->level = LIBXML_ERR_FATAL;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = $filename;
            }
            throw new XDOMDocumentException(sprintf("Error loading HTML file '%s': %s", $filename, $error->message) , $error);
        }
        return true;
    }
    public function loadHTML($source, $options = 0, \libXMLError & $error = null)
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadHTML($source, $options);
        $error = self::getLibXMLLastError();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            /*
             * If source is empty, libxml_get_last_error report no errors at all
             * so we mimic the expected error message.
            */
            if ($error === null) {
                $error = new \libXMLError();
                $error->message = 'general parsing error';
                $error->level = LIBXML_ERR_FATAL;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = '';
            }
            if ($source == '') {
                $error = new \libXMLError();
                $error->message = 'Empty string supplied as input';
                $error->level = LIBXML_ERR_NONE;
                $error->code = 0;
                $error->line = 0;
                $error->column = 0;
                $error->file = '';
            }
            throw new XDOMDocumentException(sprintf("Error loading HTML data: %s", $error->message) , $error);
        }
        return true;
    }
}
