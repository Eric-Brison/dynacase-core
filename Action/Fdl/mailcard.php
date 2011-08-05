<?php
/**
 * Functions to send document by email
 *
 * @author Anakeen 2000 
 * @version $Id: mailcard.php,v 1.83 2008/12/16 15:52:35 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/sendmail.php");
include_once("Class.MailAccount.php");


// -----------------------------------
function mailcard(&$action) {

  $docid = GetHttpVars("id"); 
  $cr = GetHttpVars("cr"); // want a status
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  // control sending
  $err=$doc->control('send');
  if ($err != "") $action->exitError($err);

  $mailto = "";
  $mailcc = "";
  $mailbcc = "";
  $mailfrom = GetHttpVars("_mail_from");

  foreach (array("plain","link") as $format) {
    $tmailto[$format]=array();
    $tmailcc[$format]=array();
    $tmailbcc[$format]=array();
  }

  $tuid=array(); // list of user id to notify

  $mt = GetHttpVars("to"); // simple arguments (can be use with wsh
  if ($mt == "") {
    $rtype = GetHttpVars("_mail_copymode", "");
    $raddr = GetHttpVars("_mail_recip", "");
    $idraddr = GetHttpVars("_mail_recipid", "");
    $tformat = GetHttpVars("_mail_sendformat", "");
    if ((is_array($raddr)) && (count($raddr)>0)) {
      foreach ($raddr as $k => $v) {
	$v=trim($v);
        if ($v!="") { 
	  if ($tformat[$k]=="") $tformat[$k]="plain";
          switch ($rtype[$k]) {
          case "cc": $tmailcc[$tformat[$k]][$v]=$v; break;
          case "bcc": $tmailbcc[$tformat[$k]][$v]=$v; break;
          default : 
	    $tmailto[$tformat[$k]][$v]=$v;
	    if ($idraddr[$k] > 0) $tuid[]=$idraddr[$k];
	    break;
          }
        }
      }
    }
  } else {
    // other notation
    $tmailto["plain"][0]=$mt;
    $oldcc=GetHttpVars("cc");
    if ($oldcc) $tmailcc["plain"][0]=$oldcc;
    $oldbcc=GetHttpVars("bcc");
    if ($oldbcc) $tmailbcc["plain"][0]=$oldbcc;
    if ($mailfrom=="") $mailfrom=GetHttpVars("from");
  }

  $sendedmail=false;  
  foreach (array("plain","link") as $format) {
    
    $mailto=implode(",",$tmailto[$format]);
    $mailcc=implode(",",$tmailcc[$format]);
    $mailbcc=implode(",",$tmailbcc[$format]);

    // correct trim --->
    setHttpVar("_mail_to", $mailto);
    setHttpVar("_mail_cc", $mailcc);
    setHttpVar("_mail_bcc", $mailbcc);
    setHttpVar("_mail_from", $mailfrom);
    if ($format=="link") setHttpVar("_mail_format", "htmlnotif");     
    if (($mailto!="") || ($mailcc!="") || ($mailbcc!=""))  {
      $err=sendmailcard($action);  
      $sendedmail=true;
    }
  }

  if ($cr == "Y") {
    if ($err != "") $action->exitError(sprintf(_("the document %s has not be sended :\n %s"), $doc->title, $err));
    elseif (! $sendedmail) $action->addWarningMsg(sprintf(_("the document %s has not been sended : no recipient"),$doc->title));
  }

  foreach ($tuid as $uid) {
    if ($uid > 0) {
      $tu=getTDoc($dbaccess,$uid);
      $wuid=getv($tu,"us_whatid");
      //      $err=$doc->addComment(_("document received for"),HISTO_NOTICE,"RCPTDOC",$wuid);
      $err=$doc->addUTag($wuid,"TOVIEW");
    }
  }

  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&latest=Y&refreshfld=Y&id=".$doc->id),
	   $action->GetParam("CORE_STANDURL"));

}
// -----------------------------------
function sendmailcard(&$action) {

  $sendcopy=true;
  $addfiles=array();
  $userinfo=true;
  $err = sendCard($action,
		  GetHttpVars("id"),
		  GetHttpVars("_mail_to",''),
		  GetHttpVars("_mail_cc",""),
		  GetHttpVars("_mail_subject"),
		  GetHttpVars("zone"),
		  GetHttpVars("ulink","N")=="Y",
		  GetHttpVars("_mail_cm",""),
		  GetHttpVars("_mail_from",""), 
		  GetHttpVars("_mail_bcc",""), 
		  GetHttpVars("_mail_format","html"),
		  $sendcopy,
		  $addfiles,
		  $userinfo,		  
		  GetHttpVars("_mail_savecopy","no")=="yes"
		  );

  if ($err != "") return $err;

  // also change state sometime with confirmmail action
  
  $state = GetHttpVars("state"); 
 
  if ($state != "") {
    
    $docid = GetHttpVars("id"); 
  
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->wid > 0) {
      if ($state != "-") {
	$wdoc = new_Doc($dbaccess,$doc->wid);
	$wdoc->Set($doc);
	$err=$wdoc->ChangeState($state,_("email sended"),true);
	if ($err != "")  $action->addWarningMsg($err);
      }
    } else {
      $action->AddLogMsg(sprintf(_("the document %s is not related to a workflow"),$doc->title));
    }
  }
}
// -----------------------------------
function sendCard(&$action,
		  $docid,
		  $to,$cc,$subject,
		  $zonebodycard="", // define mail layout
		  $ulink=false,// don't see hyperlink
		  $comment="",
		  $from="",
		  $bcc="",
		  $format="html", // define view action
		  $sendercopy=true, // true : a copy is send to the sender according to the Freedom user parameter 
		  $addfiles = array(),
                  $userinfo = true,
		  $savecopy=false
		  ) {

  // -----------------------------------
  $viewonly=  (GetHttpVars("viewonly","N")=="Y");
  if ((!$viewonly) &&($to == "")&&($cc=="")&&($bcc=="")) return _("mail dest is empty");

  // -----------------------------------
  global $ifiles;
  global $tfiles;
  global $tmpfile;
  global $vf; 
  global $doc;
  global $pubdir;
  global $action;

  $ifiles=array();
  $tfiles=array();
  $tmpfile=array();
  $mixed=true; // to see file as attachement
  // set title
  
  
  setHttpVar("id",$docid); // for view zone
  if (GetHttpVars("_mail_format") == "") setHttpVar("_mail_format",$format);

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  $ftitle = str_replace(array(" ","/",")","("), "_",$doc->title);
  $ftitle = str_replace("'", "",$ftitle);
  $ftitle = str_replace("\"", "",$ftitle);
  $ftitle = str_replace("&", "",$ftitle);

  $to=   str_replace("\"","'",$to);
  $from= str_replace("\"","'",$from);
  $cc=   str_replace("\"","'",$cc);
  $bcc=  str_replace("\"","'",$bcc);

  $vf = newFreeVaultFile($dbaccess);
  $pubdir = $action->getParam("CORE_PUBDIR");
  $szone=false;

  $multi_mix = new Fdl_Mail_mimePart('', array('content_type' => 'multipart/mixed'));
  $multi_rel = $multi_mix->addSubpart('', array('content_type' => 'multipart/related'));
  
  if ($sendercopy && $action->getParam("FDL_BCC") == "yes") {    
    $umail=getMailAddr($action->user->id);
    if ($umail != "") {      
      if ($bcc != "") $bcc = "$bcc,$umail";
      else  $bcc = "$umail";
    }
  }
  if ($from == "") {
    $from=getMailAddr($action->user->id);
    if ($from == "")  $from = getParam('SMTP_FROM');
    if ($from == "")  $from = $action->user->login.'@'.php_uname('n');    
  }

  if ($subject == "") $subject = $ftitle;
  $subject = str_replace("\"","'",$subject);
  
  $layout="maildoc.xml"; // the default
  if ($format=="htmlnotif") {
    $layout="mailnotification.xml";
    $zonebodycard="FDL:MAILNOTIFICATION:S";
  }
 
  if ($zonebodycard == "") $zonebodycard=$doc->defaultmview;
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;



  if (preg_match("/[A-Z]+:[^:]+:S/", $zonebodycard, $reg))  $szone=true;// the zonebodycard is a standalone zone ?
  if (preg_match("/[A-Z]+:[^:]+:T/", $zonebodycard, $reg))  setHttpVar("dochead","N");// the zonebodycard without head ?
  if (preg_match("/[A-Z]+:[^:]+:B/", $zonebodycard, $reg))  $binary=true;


  if ($binary) {

    $binfile=$doc->viewDoc($zonebodycard);
    if (! is_file($binfile)) $err=$binfile;

    if ($err=="") {
      $engine=$doc->getZoneTransform($zonebodycard);
      if ($engine) {
	include_once("FDL/Lib.Vault.php");
	$outfile= uniqid(getTmpDir()."/conv").".$engine";
	$err=convertFile($binfile,$engine,$outfile,$info);
	if ($err=="") {
	  $mime=getSysMimeFile($outfile, basename($outfile));
	  $ext=getExtension($mime);
	  $binfile=$outfile;
	}
      } else {
	$mime=getSysMimeFile($binfile,basename($binfile));
	$ext=getExtension($mime);
	if (!$ext) {
	  $tplfile=$doc->getZoneFile($zonebodycard);
	  $ext=getFileExtension($tplfile);
	}
      }

      $multi_mix->addSubpart('',
      	array(
      		'body_file' => $binfile,
      		'content_type' => $mime,
      		'charset' => 'UTF-8',
      		'filename' => sprintf("%s.%s", $doc->title, $ext),
      		'dfilename' => sprintf("%s.%s", $doc->title, $ext),
      		'encoding' => 'base64',
      		'name_encoding' => 'quoted-printable',
      		'filename_encoding' => 'quoted-printable',
      		'disposition' => 'attachment'
      	)
      );
      
      $zonebodycard="FDL:EMPTY";
    }
  } 

  if ($err=="") {
    setHttpVar("target","mail");
    if (preg_match("/html/",$format, $reg)) {

      if ($action->GetParam("CORE_URLINDEX") != "") {
	$turl=parse_url($action->GetParam("CORE_URLINDEX"));
	$url=$turl["scheme"].'://'.$turl["host"];
	if (isset($turl["port"])) $url.=':'.$turl["port"];
	if (isset($turl["path"])) $url.=$turl["path"]."/";
	$baseurl=$url ;	
	$absurl=$action->GetParam("CORE_URLINDEX");	
      } else {
	$absurl=$action->GetParam("CORE_ABSURL")."/";
	$baseurl=$action->GetParam("CORE_ABSURL");
      }



      if ($szone) {
           
	$sgen = $doc->viewDoc($zonebodycard,"mail",$ulink,false,true);

	$doc->lay->Set("absurl",$absurl);
	$doc->lay->Set("baseurl",$baseurl);
	$sgen=$doc->lay->gen();
	if ($comment != "") {
	  $comment= nl2br($comment);
	  $sgen = preg_replace("'<body([^>]*)>'i",
			       "<body \\1><P>$comment<P><HR>",
			       $sgen);
	}
       
      } else {
	// contruct HTML mail
      
	$docmail = new Layout(getLayoutFile("FDL",$layout),$action);

	$docmail->Set("TITLE", $doc->title);
	$docmail->Set("ID", $doc->id);
	$docmail->Set("zone", $zonebodycard);
	$docmail->Set("absurl",$absurl);
	$docmail->Set("baseurl",$baseurl) ;	
	if ($comment != "") {
	  $docmail->setBlockData("COMMENT", array(array("boo")));
	  $docmail->set("comment", nl2br($comment));
	}

	$sgen = $docmail->gen();
      }
      if ($viewonly) {echo $sgen;exit;}


   
      $sgen1 = preg_replace("/src=\"(FDL\/geticon[^\"]+)\"/ei",
			    "imgvaultfile('\\1')",
			    $sgen);

      $sgen1 = preg_replace(array("/SRC=\"([^\"]+)\"/e","/src=\"([^\"]+)\"/e"),
			    "srcfile('\\1')",
			    $sgen1);

      $pfout = uniqid(getTmpDir()."/".$doc->id);
      $fout = fopen($pfout,"w");
   
      fwrite($fout,$sgen1);
    
      fclose($fout);
    }

    if (preg_match("/pdf/",$format, $reg)) {
      // ---------------------------
      // contruct PDF mail
      if ($szone) {
	$sgen = $doc->viewDoc($zonebodycard,"mail",false);
      } else {
    
    
	$docmail2 = new Layout(getLayoutFile("FDL",$layout),$action);


	$docmail2->Set("zone", $zonebodycard);
	$docmail2->Set("TITLE", $doc->title);
  
	$sgen = $docmail2->gen();
      }
      $sgen2 = preg_replace("/src=\"([^\"]+)\"/ei",
			    "realfile('\\1')",
			    $sgen);

      $ppdf = uniqid(getTmpDir()."/".$doc->id).".pdf.html";
      $fout = fopen($ppdf,"w");
      $sgen2 = preg_replace('/\xE2\x82\xAC/', '&euro;', $sgen2);
      fwrite($fout,$sgen2);
      fclose($fout);
    }


    // ---------------------------
    // contruct mail_mime object
    $htmlPart = null;
    if (preg_match("/html/",$format, $reg)) {
      $htmlPart = $multi_rel->addSubpart('',
      	array(
      		'body_file' => $pfout,
      		'content_type' => 'text/html',
      		'charset' => 'UTF-8',
      		'encoding' => 'quoted-printable',
      		'name_encoding' => 'quoted-printable',
      		'filename_encoding' => 'quoted-printable'
      	)
      );
    } else if ($format == "pdf") {   
      $multi_rel->addSubpart($comment,
      	array(
      		'content_type' => 'text/plain',
      		'charset' => 'UTF-8',
      		'encoding' => 'quoted-printable',
      		'name_encoding' => 'quoted-printable',
      		'filename_encoding' => 'quoted-printable'
      	)
      );
    }


    if ($format != "pdf") {

      // ---------------------------
      // insert attached files
      if (preg_match_all("/(href|src)=\"cid:([^\"]*)\"/i",$sgen,$match)) {
	$tcids = $match[2]; // list of file references inserted in mail

	$afiles = $doc->GetFileAttributes();
	$taids = array_keys($afiles);
	if (count($afiles) > 0) {
	  foreach($tcids as $kf=>$vaf) {
	    $tf=explode("+",$vaf);
	    if (count($tf)==1) {
	      $aid=$tf[0];
	      $index=-1;
	    } else {
	      $aid=$tf[0];
	      $index=$tf[1];	  
	    }
	    if (in_array($aid, $taids)) {	
	      $tva=array();
	      $cidindex="";
	      if ($afiles[$aid]->repeat) $va=$doc->getTValue($aid,"",$index);
	      else $va=$doc->getValue($aid);

	      if ($va != "") {
		list($mime,$vid)=explode("|",$va);

		if ($vid != "") {
		  if ($vf->Retrieve ($vid, $info) == "") {  
		
		    $cidindex= $vaf;
		    if (($mixed) && ($afiles[$aid]->type != "image"))  $cidindex=$info->name;
		    $multi_mix->addSubpart('',
      			array(
      				'body_file' => $info->path,
      				'content_type' => $info->mime_s?$info->mime_s:$mime,
      				'charset' => 'UTF-8',
      				'filename' => $info->name,
      				'dfilename' => $info->name,
      				'encoding' => 'base64',
      				'name_encoding' => 'quoted-printable',
      				'filename_encoding' => 'quoted-printable',
      				'disposition' => 'attachment',
      				'cid' => $cidindex
      			)
		  	);

		  }
		}	    
	      }
	    }
	  }
	}
      }

      // Remove <a href="cid:xxx"> links in HTML source, and regenerate attached file
      if( $htmlPart !== null && $sgen1 != '' && $pfout != '' ) {
        $htmlBody = preg_replace('|<a\s+(?:\w+="[^"]+")*\s*href="cid:[^"]+"[^>]*>([^<]*)</a>|si', '\1', $sgen1);
        $fout = fopen($pfout, "w");
        fwrite($fout, $htmlBody);
        fclose($fout);
        $htmlPart->setBodyFile($pfout);
      }

      // ---------------------------
      // add icon image
      if (preg_match("/html/",$format, $reg)) {
	if (! $szone) {
	  $va=$doc->icon;
	  if ($va != "") {
	    list($mime,$vid)=explode("|",$va);

	    if ($vid != "") {
	      if ($vf->Retrieve ($vid, $info) == "") {  
			$multi_rel->addSubpart('',
				array(
					'body_file' => $info->path,
					'content_type' => $info->mime_s?$info->mime_s:$mime,
					'charset' => 'UTF-8',
					'filename' => $info->name,
					'dfilename' => $info->name,
					'encoding' => 'base64',
					'name_encoding' => 'quoted-printable',
					'filename_encoding' => 'quoted-printable',
					'disposition' => 'inline',
					'cid' => 'icon'
				)
			);

	      }
	    } else {
	      $icon=$doc->getIcon();
	      if (file_exists($pubdir."/$icon")) {
	      	$multi_rel->addSubpart('',
      			array(
      				'body_file' => sprintf("%s/%s", $pubdir, $icon),
      				'content_type' => sprintf("image/%s", fileextension($icon)),
      				'charset' => 'UTF-8',
      				'filename' => "icon",
      				'dfilename' => "icon",
      				'encoding' => 'base64',
      				'name_encoding' => 'quoted-printable',
      				'filename_encoding' => 'quoted-printable',
      				'disposition' => 'inline',
      				'cid' => 'icon'
      			)
		  	);

	      }
	    }
	  }
	}
      }
  
    
      // ---------------------------
      // add inserted image


      foreach($ifiles as $k => $v) {
	if (file_exists($pubdir."/$v")) {

		$multi_rel->addSubpart('',
      		array(
      			'body_file' => sprintf("%s/%s", $pubdir, $v),
      			'content_type' => sprintf("image/%s", fileextension($v)),
      			'charset' => 'UTF-8',
      			'filename' => basename($v),
      			'dfilename' => basename($v),
      			'encoding' => 'base64',
      			'name_encoding' => 'quoted-printable',
      			'filename_encoding' => 'quoted-printable',
      			'disposition' => 'inline',
      			'cid' => $v
      		)
      	);

	}
      }


      foreach($tfiles as $k=>$v) {
      	if (file_exists($v)) {

		$multi_rel->addSubpart('',
      		array(
      			'body_file' => $v,
      			'content_type' => trim(shell_exec(sprintf("file --mime -b %s", escapeshellarg($v)))),
      			'charset' => 'UTF-8',
      			'filename' => $k,
      			'dfilename' => $k,
      			'encoding' => 'base64',
      			'name_encoding' => 'quoted-printable',
      			'filename_encoding' => 'quoted-printable',
      			'disposition' => 'inline',
      			'cid' => $k
      		)
      	);

	}
      
      }
  
      // Other files, 
      if (count($addfiles)>0) 
	{
	  foreach ($addfiles as $kf => $vf) 
	  {
	      if (count($vf)==3) 
		{
		  $fview = $vf[0];
		  $fname = $vf[1];
		  $fmime = $vf[2];
		
		  $fgen = $doc->viewDoc($fview, "mail");
		  $fpname = getTmpDir()."/".str_replace(array(" ","/","(",")"), "_", uniqid($doc->id).$fname);
		  if ($fp = fopen($fpname, 'w')) {
		    fwrite($fp, $fgen);
		    fclose($fp);
		  }
		  $fpst = stat($fpname);
		  if (is_array($fpst) && $fpst["size"]>0) {
		  	
		  	$multi_mix->addSubpart('',
      			array(
      				'body_file' => $fpname,
      				'content_type' => $fmime,
      				'charset' => 'UTF-8',
	      			'filename' => $fname,
      				'dfilename' => $fname,
      				'encoding' => 'base64',
      				'name_encoding' => 'quoted-printable',
      				'filename_encoding' => 'quoted-printable',
      				'disposition' => 'attachment',
      				'cid' => '$fname'
      			)
      		);

		  }
		}
	    }
	}

    }
    if (preg_match("/pdf/",$format, $reg)) {
      // try PDF 
      $fps= uniqid(getTmpDir()."/".$doc->id)."ps";
      $fpdf= uniqid(getTmpDir()."/".$doc->id)."pdf";
      $cmdpdf = sprintf("recode u8..l9 %s && html2ps -U -i 0.5 -b %s/ %s > %s && ps2pdf %s %s",
                        escapeshellarg($ppdf), // recode
                        escapeshellarg($pubdir), escapeshellarg($ppdf), escapeshellarg($fps), // html2ps
                        escapeshellarg($fps), escapeshellarg($fpdf) // ps2pdf
                        );
      system (($cmdpdf), $status);
      if ($status == 0)  {     
      	$multi_mix->addSubpart('',
      		array(
      			'body_file' => $fpdf,
      			'content_type' => 'application/pdf',
      			'charset' => 'UTF-8',
      			'filename' => sprintf("%s.pdf", $doc->title),
      			'dfilename' => sprintf("%s.pdf", $doc->title),
      			'encoding' => 'base64',
      			'name_encoding' => 'quoted-printable',
      			'filename_encoding' => 'quoted-printable',
      			'disposition' => 'attachment'
      		)
      	);

      } else {
	$action->addlogmsg(sprintf(_("PDF conversion failed for %s"),$doc->title));
      }
    }  
    
    $err = sendmail($to, $from, $cc, $bcc, $subject, $multi_mix);
    if ($err=="")  {
      if ($savecopy) createSentMessage($to,$from,$cc,$bcc,$subject,$multi_mix,$doc);
      if ($cc != "") $lsend=sprintf("%s and %s",$to,$cc);
      else $lsend=$to;
      $doc->addcomment(sprintf(_("sended to %s"), $lsend));
      $action->addlogmsg(sprintf(_("sending %s to %s"),$doc->title, $lsend)); 
      if ($userinfo) $action->addwarningmsg(sprintf(_("sending %s to %s"),$doc->title, $lsend));   
    } else {
      $action->log->warning($err);
      $action->addlogmsg(sprintf(_("%s cannot be sent"),$doc->title));
      if ($userinfo) $action->addwarningmsg(sprintf(_("%s cannot be sent"),$doc->title));
      if ($userinfo) $action->addwarningmsg($err);
   
    }

  
    // suppress temporaries files
    if (isset($ftxt) && is_file($ftxt))       unlink($ftxt);
    if (isset($fpdf) && is_file($fpdf))       unlink($fpdf);
    if (isset($fps) && is_file($fps))         unlink($fps);
    if (isset($pfout) && is_file($pfout))     unlink($pfout);
    if (isset($ppdf) && is_file($ppdf))       unlink($ppdf);
    if (isset($binfile) && is_file($binfile)) unlink($binfile);
    
  
    $tmpfile=array_merge($tmpfile,$tfiles);
    foreach($tmpfile as $k=>$v) {
      if (file_exists($v) && (substr($v,0,9)==getTmpDir()."/"))	unlink($v);    

    }
 
  }


  return $err;

}


function srcfile($src) {
  global $ifiles;
  $vext= array("gif","png","jpg","jpeg","bmp");
  if (substr($src,0,3) == "cid")   return "src=\"$src\"";
  if (substr($src,0,4) == "http")  {
  	$chopped_src = '';
  	// Detect HTTP URLs pointing to myself
  	foreach( array('CORE_URLINDEX', 'CORE_PUBURL') as $url ) {
  		$url = getParam($url);
  		if( strlen($url) <= 0 ) {
  			continue;
  		}
  		if( strcmp(substr($src, 0, strlen($url)), $url) == 0 ) {
  			// Chop the URL base part, and leave only the args/vars
  			$chopped_src = substr($src, strlen($url));
  			break;
  		}
  	}
  	if( $chopped_src == '' ) {
  		return sprintf('src="%s"', $src);
  	}
  	$src = $chopped_src;
  }
  
  if (preg_match("/(.*)(app=FDL.*action=EXPORTFILE.*)$/",$src,$reg)) {
    return imgvaultfile(str_replace('&amp;','&',$reg[2]));
  }
  
  if ( ! in_array(strtolower(fileextension($src)),$vext)) return "";

  $ifiles[$src] = $src;
  return "src=\"cid:$src\"";
}
function imgvaultfile($src) {
  global $tfiles;
  $newfile=copyvault($src);
  if ($newfile) {
    $src="img".count($tfiles);
    $tfiles[$src] = $newfile;
    return "src=\"cid:$src\" ";
  }
  return "";
}
function copyvault($src) {
  global $action;

  include_once('FDL/Lib.Vault.php');

  if (preg_match("/(.*)(app=FDL.*action=EXPORTFILE.*)docid=([^&]*)&/",$src,$reg)) {
    $url=$action->getParam("CORE_OPENURL",$action->getParam("CORE_EXTERNURL"));
    if (strstr($url,'?')) $url.='&';
    else $url.='?';
    $token = $action->user->getUserToken(3600,true,array("app"=>"FDL","action"=>"EXPORTFILE","docid"=>$reg[3]));
    $url.="authtype=open&privateid=$token&";
    $url.=$src;
    $newfile=uniqid(getTmpDir()."/img");
    if (!copy($url, $newfile)) {
      return "";
    } 
  return $newfile;
  }
  if( preg_match("|^FDL/geticon\.php\?vaultid=(?P<vid>\d+)|", $src, $reg) ) {
    $info = vault_properties($reg['vid']);
    $newfile = uniqid(getTmpDir()."/img");
    if( ! copy($info->path, $newfile) ) {
      return "";
    }
    return $newfile;
  }

  return "";
}


function realfile($src) {
  global $vf; 
  global $doc; 
  global $pubdir;
  global $tmpfile;
  $f=false;
  if ($src == "cid:icon") {
    $va=$doc->icon;
  } else { 
    if (substr($src,0,4) == "cid:") $va=$doc->getValue(substr($src,4));
    elseif (preg_match("/(.*)(app=FDL.*action=EXPORTFILE.*)$/",$src,$reg)) {
      $va= copyvault(str_replace('&amp;','&',$reg[2]));
      $tmpfile[]=$va;
    } else $va=$src;
  }

  if ($va != "") {
    list($mime,$vid)=explode("|",$va);

    if ($vid != "") {
      if ($vf -> Retrieve ($vid, $info) == "") {  
	$f= $info->path;
      }

    } else {
      
      if (file_exists($pubdir."/$va")) $f=$pubdir."/$va";
      elseif (file_exists($pubdir."/Images/$va")) $f=$pubdir."/Images/$va";
      elseif ((substr($va,0,12)==getTmpDir().'/img') && file_exists($va)) $f=$va;
    }
  }
  
    
//   $mime=trim(`file -ib "$f"`);
//   print "<br>[$mime][$f][$va]";
//   if (substr($mime,0,5) != "image") $f="";

  if ($f) return "src=\"$f\"";
  return "";

}





?>
