<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * redefine class to add explicit CID
 * @deprecated use \Dcp\Mail\Message instead
 */
class Fdl_Mail_mime extends Mail_mime
{
    // USE TO ADD CID in attachment
    
    /**
     * Adds a file to the list of attachments.
     *
     * @param  string  $file       The file name of the file to attach
     *                             OR the file data itself
     * @param  string  $c_type     The content type
     * @param  string  $name       The filename of the attachment
     *                             Only use if $file is the file data
     * @param  bool    $isFilename Whether $file is a filename or not
     *                             Defaults to true
     * @return mixed true on success or PEAR_Error object
     * @access public
     */
    function addAttachment($file, $c_type = 'application/octet-stream', $name = '', $isfilename = true, $encoding = 'base64', $cid = '', $charset = "UTF-8", $language = '', $location = '', $n_encoding = null, $f_encoding = null, $description = '')
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file) : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : basename($file);
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError('The supplied filename for the attachment can\'t be empty');
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        
        $this->_parts[] = array(
            'body' => $filedata,
            'name' => $filename,
            'charset' => $charset,
            'c_type' => $c_type,
            'encoding' => $encoding
        );
        return true;
    }
    
    function addAttachmentInline($file, $c_type = 'application/octet-stream', $name = '', $isfilename = true, $encoding = 'base64', $cid = '', $charset = "UTF-8")
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file) : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : basename($file);
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError('The supplied filename for the attachment can\'t be empty');
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        
        $this->_parts[] = array(
            'body' => $filedata,
            'name' => $filename,
            'charset' => $charset,
            'c_type' => $c_type,
            'encoding' => $encoding,
            'disposition' => 'inline',
            'cid' => $cid
        );
        return true;
    }
    /**
     * Adds an attachment subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param  Fdl_Mail_mimePart  $obj The mimePart to add the image to
     * @param  array  $value The attachment information
     * @return Fdl_Mail_mimePart  The image mimePart object
     * @access private
     */
    function &_addAttachmentPart(&$obj, $value)
    {
        $params['content_type'] = $value['c_type'];
        $params['encoding'] = $value['encoding'];
        $params['dfilename'] = $value['name'];
        $params['filename'] = $value['name'];
        $params['charset'] = $value['charset'];
        
        if (isset($value['disposition'])) {
            $params['disposition'] = $value['disposition'];
        } else {
            $params['disposition'] = 'attachment';
        }
        
        if (isset($value['cid'])) {
            $params['cid'] = $value['cid'];
        }
        
        if (isset($value['name_encoding'])) {
            $params['name_encoding'] = $value['name_encoding'];
        } else {
            $params['name_encoding'] = 'quoted-printable';
        }
        
        if (isset($value['filename_encoding'])) {
            $params['filename_encoding'] = $value['filename_encoding'];
        } else {
            $params['filename_encoding'] = 'quoted-printable';
        }
        
        $obj->addSubpart($value['body'], $params);
    }
    function __construct($crlf = "\r\n")
    {
        parent::Mail_mime($crlf);
        $this->_build_params['html_charset'] = 'UTF-8';
        $this->_build_params['text_charset'] = 'UTF-8';
        $this->_build_params['head_charset'] = 'UTF-8';
    }
}

class Fdl_Mail_mimePart extends Mail_mimePart
{
    var $_filename = '';
    
    function Fdl_Mail_mimePart($body = '', $params = array())
    {
        // Keep track of the unaltered/unencoded filename for further use
        if (isset($params['filename'])) {
            $this->_filename = $params['filename'];
        } elseif (isset($params['dfilename'])) {
            $this->_filename = $params['dfilename'];
        }
        
        parent::Mail_mimePart($body, $params);
    }
    /**
     * @param string $body
     * @param array $params
     * @return Fdl_Mail_mimePart
     */
    function &addSubpart($body, $params)
    {
        if (!property_exists('Mail_mimePart', '_body_file') && isset($params['body_file'])) {
            // Mail_mimePart < 1.6.0 has no support for passing a file with $param['body_file']
            $body = file_get_contents($params['body_file']);
            unset($params['body_file']);
        }
        $this->_subparts[] = new Fdl_Mail_mimePart($body, $params);
        return $this->_subparts[count($this->_subparts) - 1];
    }
    
    function setBodyFile($file)
    {
        if (!property_Exists('Mail_mimePart', '_body_file')) {
            // Mail_mimePart < 1.6.0
            $this->_body = file_get_contents($file);
        } else {
            $this->_body_file = $file;
        }
        return $this;
    }
}
