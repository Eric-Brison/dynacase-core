<?php
/**
 * Send document mail with SMTP protocol
 *
 * @author Anakeen 2007
 * @version $Id: sendmail.php,v 1.4 2007/10/10 16:15:35 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
include('Mail/mime.php');
include('Net/SMTP.php');
/**
 * Send mail via smtp server
 * @param string $to mail addresses (, separate)
 * @param string $cc mail addresses (, separate)
 * @param string $bcc mail addresses (, separate)
 * @param string $from mail address
 * @param string $subject mail subject
 * @param Mail_mime &$mimemail mail mime object 
 * @return string error message : if no error: empty if no error
 */
function sendmail($to,$from,$cc,$bcc,$subject,&$mimemail,$multipart=null) {
 
  
  $rcpt = array_merge(explode(',',$to),
		      explode(',',$cc),
		      explode(',',$bcc));

  $host=getParam('SMTP_HOST','localhost');
  $port=getParam('SMTP_PORT',25);
  $login=getParam('SMTP_LOGIN');
  $password=getParam('SMTP_PASSWORD');



  $mimemail->setFrom($from);
  if ($cc!='') $mimemail->addCc($cc);


  $xh['To']=$to;
  /* Create a new Net_SMTP object. */
  if (! ($smtp = new Net_SMTP($host,$port))) {
    die("Unable to instantiate Net_SMTP object\n");
  }
  $smtp->setDebug(false);
  
  /* Connect to the SMTP server. */
  if (PEAR::isError($e = $smtp->connect())) {
    return ("smtp connect:".$e->getMessage() );
  }

  if ($login) {
    if (PEAR::isError($e = $smtp->auth($login,$password))) {
      return ("smtp login:".$e->getMessage() );
    }
  }
  /* Send the 'MAIL FROM:' SMTP command. */
  if (PEAR::isError($smtp->mailFrom($from))) {
    return ("Unable to set sender to <$from>");
  }
  
  /* Address the message to each of the recipients. */
  foreach ($rcpt as $v) {
    if ($v) {
      if (preg_match("/<([^>]*)>/",$v,$reg)) {
	$v=$reg[1];
      }
      if (PEAR::isError($res = $smtp->rcptTo($v))) {
	return ("Unable to add recipient <$v>: " . $res->getMessage() );
      }
    }
   }
  setlocale(LC_TIME, 'C');

  $body=$mimemail->get();

  $xh['Date']=strftime("%a, %d %b %Y %H:%M:%S %z",time());
  //  $xh['Content-type']= "multipart/related";
  $xh['Subject']=$subject;
  $xh['Message-Id']='<'.strftime("%Y%M%d%H%M%S-",time()).rand(1,65535)."@$host>";

  $xh['User-Agent']=sprintf("Dynacase Platform %s",getParam('VERSION'));
  $data="";
  $h=$mimemail->headers($xh);
  if ($multipart)  $h['Content-Type']=str_replace("mixed",$multipart,$h['Content-Type']);

  foreach ($h as $k=>$v) {
    $data.="$k: $v\r\n";
  }
  
  $data.="\r\n".$body;

  
  /* Set the body of the message. */
  if (PEAR::isError($smtp->data($data))) {
    return ("Unable to send data");
  }

 

  /* Disconnect from the SMTP server. */
  $smtp->disconnect();
}


/**
 * redefine class to add explicit CID
 */
class Fdl_Mail_mime extends Mail_mime {
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
    function addAttachment($file, $c_type = 'application/octet-stream',
                           $name = '', $isfilename = true,
                           $encoding = 'base64',$cid='',$charset="UTF-8")
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file)
                                           : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : basename($file);
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError(
              'The supplied filename for the attachment can\'t be empty'
            );
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $this->_parts[] = array(
                                'body'     => $filedata,
                                'name'     => $filename,
				'charset'  => $charset,
                                'c_type'   => $c_type,
                                'encoding' => $encoding
                               );
        return true;
    }

    function addAttachmentInline($file, $c_type = 'application/octet-stream',
				 $name = '', $isfilename = true,
				 $encoding = 'base64',$cid='',$charset="UTF-8")
    {
        $filedata = ($isfilename === true) ? $this->_file2str($file)
                                           : $file;
        if ($isfilename === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (!empty($name)) ? $name : basename($file);
        } else {
            $filename = $name;
        }
        if (empty($filename)) {
            return PEAR::raiseError(
              'The supplied filename for the attachment can\'t be empty'
            );
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $this->_parts[] = array(
                                'body'     => $filedata,
                                'name'     => $filename,
				'charset'  => $charset,
                                'c_type'   => $c_type,
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
     * @param  object  The mimePart to add the image to
     * @param  array   The attachment information
     * @return object  The image mimePart object
     * @access private
     */
    function &_addAttachmentPart(&$obj, $value)
    {
        $params['content_type'] = $value['c_type'];
        $params['encoding']     = $value['encoding'];
        $params['dfilename']    = $value['name'];
        $params['charset']    = $value['charset'];

	if( isset($value['disposition']) ) {
	  $params['disposition'] = $value['disposition'];
	} else {
	  $params['disposition'] = 'attachment';
	}

	if( isset($value['cid']) ) {
	  $params['cid'] = $value['cid'];
	}

        $obj->addSubpart($value['body'], $params);

    }
    function __construct($crlf = "\r\n") {
      parent::Mail_mime($crlf);
      $this->_build_params['html_charset']='UTF-8';
      $this->_build_params['text_charset']='UTF-8';
      $this->_build_params['head_charset']='UTF-8';        
    }
    
}

/**
 * record message sent from freedom
 */
function createSentMessage($to,$from,$cc,$bcc,$subject,&$mimemail,&$doc=null) {
  include_once('WHAT/Lib.Common.php');
  
  $msg=createDoc(getDbAccessFreedom(),"SENTMESSAGE",true);
  if ($msg) {
    $msg->setValue("emsg_from",$from);
    $msg->setValue("emsg_date",Doc::getTimeDate());
    $msg->setValue("emsg_subject",$subject);
    if ($doc && $doc->id) {
      $msg->setValue("emsg_refid",$doc->id);
      $msg->profid=$doc->profid;
    }
    $trcp=array();
    foreach (explode(',',$to) as $v) {
      if ($v) $msg->addArrayRow("emsg_t_recipient",array("emsg_sendtype"=>"to",
							 "emsg_recipient"=>$v));
    }
    foreach (explode(',',$cc) as $v) {
      if ($v) $msg->addArrayRow("emsg_t_recipient",array("emsg_sendtype"=>"cc",
							 "emsg_recipient"=>$v));
    }
    foreach (explode(',',$bcc) as $v) {
      if ($v) $msg->addArrayRow("emsg_t_recipient",array("emsg_sendtype"=>"bcc",
							 "emsg_recipient"=>$v));
    }
      
    $msg->setValue("emsg_textbody",$mimemail->_txtbody);
    $msg->setValue("emsg_htmlbody",$mimemail->_htmlbody);
    $linkedbody=$mimemail->_htmlbody;
    foreach ($mimemail->_parts as $k=>$v) {
      $tmpfile=tempnam(getTmpDir(), 'fdl_attach');
      file_put_contents($tmpfile, $v["body"]);
      $msg->storeFile("emsg_attach",$tmpfile,$v["name"],$k);
      @unlink($tmpfile);
    }

    $err=$msg->add();
    // relink body
    if ($err=="") {
      $linkedbody=$mimemail->_htmlbody;
      foreach ($mimemail->_parts as $k=>$v) {
	$linkedbody=str_replace("cid:".$v["cid"],$msg->getFileLink("emsg_attach",$k),$linkedbody);
	
      }
      $msg->disableEditControl();
      $msg->setValue("emsg_htmlbody",$linkedbody);
      $err=$msg->modify(true);
    }
  }
  return $err;
}
?>