<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Utils;

class htmlclean
{
    static $libXMLErrorIgnoreCodes = array(
        /* Ignore "htmlParseEntityRef: expecting ';'" (XML_ERR_ENTITYREF_SEMICOL_MISSING) errors */
        23,
        /* Ignore "Tag video invalid in Entity" (XML_HTML_UNKNOWN_TAG) errors */
        801
    );
    /**
     * Delete dangerous attributes and elements to prevent xss attacks
     * @param string $data html fragment
     * @return string
     */
    public static function xssClean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array(
            '&amp;',
            '&lt;',
            '&gt;'
        ) , array(
            '&amp;amp;',
            '&amp;lt;',
            '&amp;gt;'
        ) , $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[a-z]*\s*=\s*["][^"]*["]+#iu', '$1', $data);
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[a-z]*\s*=\s*[\'][^\']*[\']+#iu', '$1', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*([`\'"])(?:.(?!\2))*?expression[\x00-\x20]*\((?:.(?!\2))*[^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*([`\'"])(?:.(?!\2))*?behaviour[\x00-\x20]*\((?:.(?!\2))*[^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*([`\'"])(?:.(?!\2))*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*(?:.(?!\2))*[^>]*>#iu', '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|script)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);
        // we are done...
        return $data;
    }
    
    public static function cleanStyle($data)
    {
        // Remove span tags and keep content
        $data = preg_replace('/<\/?span[^>]*>/is', "", $data);
        // Remove font tags and keep content
        $data = preg_replace('/<\/?font[^>]*>/is', "", $data);
        // Remove style attributes
        $data = preg_replace('/<([^>]*) style\s*=\s*"[^"]*"/is', "<\\1", $data);
        $data = preg_replace('/<([^>]*) style\s*=\s*\'[^\']*\'/is', "<\\1", $data);
        // Delete class attributes
        $data = preg_replace('/<([^>]*) class\s*=\s*"[^"]*"/is', "<\\1", $data);
        $data = preg_replace('/<([^>]*) class\s*=\s*\'[^\']*\'/is', "<\\1", $data);
        // Delete style tags
        $data = preg_replace('/<\s*style[^>]*>[^<]*<\/style>/iu', "", $data);
        /*
        do {
            // Remove really unwanted tags
            $old_data = $data;
           // $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);
        */
        return $data;
    }
    /**
     * Normalize/correct an HTML fragment by loading and serializing it back through libxml
     *
     * @param string $html The HTML fragment to cleanup/correct (HTML must be encoded in UTF-8)
     * @param string $error Empty string if no error or non-empty string containing the error message
     * @return bool(false)|string The resulting HTML on success or bool(false) on failure (the error message is returned in the $error argument)
     */
    public static function normalizeHTMLFragment($html, &$error = '')
    {
        $dom = new XDOMDocument();
        $dom->setLibXMLErrorIgnoreCodes(self::$libXMLErrorIgnoreCodes);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        /*
         * Add a HTML meta header to setup DOMDocument to UTF-8 encoding and no trailing </body></html>
         * to not interfere with the given $html fragment.
        */
        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $html;
        /**
         * @var \libXMLError[] $libXMLErrors
         */
        $libXMLErrors = array();
        try {
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET, $libXMLErrors);
        }
        catch(XDOMDocumentException $e) {
            $error = $e->getMessage();
            return false;
        }
        $error = self::getFirstErrorMessage($libXMLErrors);
        /* Get back the top <body> wrapper node added by loadHTML() */
        $dom->normalizeDocument();
        $wrapper = $dom->documentElement->getElementsByTagName('body')->item(0);
        if ($wrapper === null || $wrapper === false) {
            $error = "body wrapper not found";
            return false;
        }
        /* Extract and serialize back all the childs to HTML */
        $html = '';
        for ($i = 0; $i < $wrapper->childNodes->length; $i++) {
            $html.= $dom->saveHTML($wrapper->childNodes->item($i));
        }
        /* Remove carriage-returns inserted by libxml's HTML serialization */
        $html = str_replace(array(
            "\n<",
            ">\n"
        ) , array(
            "<",
            ">"
        ) , $html);
        return $html;
    }
    /**
     * Convert an HTML fragment to a XHTML document
     *
     * @param string $html The HTML fragment to cleanup/correct (HTML must be encoded in UTF-8)
     * @param string $error Empty string if no error or non-empty string containing the error message
     * @return bool(false)|string The resulting XHTML on success or bool(false) on failure (the error message is returned in the $error argument)
     */
    public static function convertHTMLFragmentToXHTMLDocument($html, &$error = '')
    {
        $dom = new XDOMDocument();
        $dom->setLibXMLErrorIgnoreCodes(self::$libXMLErrorIgnoreCodes);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        /*
         * Add a HTML meta header to setup DOMDocument to UTF-8 encoding and no trailing </body></html>
         * to not interfere with the given $html fragment.
        */
        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $html;
        /**
         * @var \libXMLError[] $libXMLErrors
         */
        $libXMLErrors = array();
        try {
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET, $libXMLErrors);
        }
        catch(XDOMDocumentException $e) {
            $error = $e->getMessage();
            return false;
        }
        $error = self::getFirstErrorMessage($libXMLErrors);
        /* Get back the top <body> wrapper node */
        $dom->normalizeDocument();
        $wrapper = $dom->documentElement->getElementsByTagName('body')->item(0);
        if ($wrapper === null || $wrapper === false) {
            $error = 'body top node not found';
            return false;
        }
        /* Extract and serialize back to XML all the childs */
        $xhtml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xhtml.= '<html xmlns="http://www.w3.org/1999/xhtml"><body>';
        for ($i = 0; $i < $wrapper->childNodes->length; $i++) {
            $xhtml.= $dom->saveXML($wrapper->childNodes->item($i));
        }
        $xhtml.= '</body></html>';
        return $xhtml;
    }
    /**
     * @param \libXMLError[] List of libXMLError objects
     * @return string
     */
    public static function getFirstErrorMessage(&$libXMLErrors = array())
    {
        if (count($libXMLErrors) > 0) {
            return $libXMLErrors[0]->message;
        }
        return '';
    }
}
