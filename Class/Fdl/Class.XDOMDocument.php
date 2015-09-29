<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Utils;

class XDOMDocumentException extends \Exception
{
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
    protected static function getLibXMLLastErrorMessage()
    {
        $error = libxml_get_last_error();
        return ($error === false) ? '' : trim($error->message);
    }
    public function load($filename, $options = 0, &$error = '')
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::load($filename, $options = 0);
        $error = self::getLibXMLLastErrorMessage();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if ($error == '') {
                $error = 'general parsing error';
            }
            throw new XDOMDocumentException(sprintf("Error loading XML file '%s': %s", $filename, $error));
        }
        return true;
    }
    public function loadXML($source, $options = 0, &$error = '')
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadXML($source, $options);
        $error = self::getLibXMLLastErrorMessage();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            /*
             * If source is empty, libxml_get_last_error report no errors at all
             * so we mimic the expected error message.
            */
            if ($source == '') {
                $error = 'Empty string supplied as input';
            } else {
                $error = 'general parsing error';
            }
            throw new XDOMDocumentException(sprintf("Error loading XML data: %s", $error));
        }
        return true;
    }
    public function loadHTMLFile($filename, $options = 0, &$error = '')
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadHTMLFile($filename, $options);
        $error = self::getLibXMLLastErrorMessage();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            if ($error == '') {
                $error = 'general parsing error';
            }
            throw new XDOMDocumentException(sprintf("Error loading HTML file '%s': %s", $filename, $error));
        }
        return true;
    }
    public function loadHTML($source, $options = 0, &$error = '')
    {
        self::enableLibXMLErrors();
        /*
         * Explicitly mask errors so PHPUnit work properly
        */
        $ret = @parent::loadHTML($source, $options);
        $error = self::getLibXMLLastErrorMessage();
        self::restoreLibXMLErrors();
        if ($ret === false) {
            /*
             * If source is empty, libxml_get_last_error report no errors at all
             * so we mimic the expected error message.
            */
            if ($error == '') {
                $error = 'general parsing error';
            }
            if ($source == '') {
                $error = 'Empty string supplied as input';
            }
            throw new XDOMDocumentException(sprintf("Error loading HTML data: %s", $error));
        }
        return true;
    }
}
