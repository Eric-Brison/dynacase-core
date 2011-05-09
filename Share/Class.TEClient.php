<?php
/**
 * Function to dialog with transformation server engine
 *
 * @author Anakeen 2007
 * @version $Id: Class.TEClient.php,v 1.12 2007/08/14 09:39:33 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 */
/**
 */

include_once("WHAT/Lib.FileMime.php");

Class TransformationEngine {
    const error_connect=-2;
    const error_noengine=-3;
    const error_sendfile=-4;
    const error_emptyfile=-5;
    const error_convert=-1;
    const status_inprogress=2;
    const status_waiting=3;
    const status_done=1;
  /**
   * host name of the transformation engine server
   * @private string
   */
  private $host='localhost';
  /**
   * port number of the transformation engine server
   * @private int
   */
  private $port=51968;
  /**
   * initialize host and port
   * @param string $host host name
   * @param int $port port number
   * 
   */
  function __construct($host="localhost",$port=51968) {
    if ($host != "") $this->host=$host;
    if ($port > 0) $this->port=$port;
  }

  /**
   * send a request to do a transformation
   * @param string $te_name Engine name
   * @param string $fkey foreign key
   * @param string $filename the path where is the original file
   * @param string $callback url to activate after transformation is done
   * @param array &$info transformation task info return "tid"=> ,"status"=> ,"comment=>
   * 
   * @return string error message, if no error empty string
   */
  function sendTransformation($te_name,$fkey,$filename,$callback,&$info) {  
    $err="";

    clearstatcache(); // to reset filesize
    $size=filesize($filename);
    if ($size > 0) {

      /* Lit l'adresse IP du serveur de destination */
      $address = gethostbyname($this->host);
      $service_port = $this->port;
      /* Cree une socket TCP/IP. */
      //  echo "Essai de connexion à '$address' sur le port '$service_port'...\n";
      //    $result = socket_connect($socket, $address, $service_port);
      $timeout=floatval(getParam("TE_TIMEOUT",3));
      $fp = @stream_socket_client("tcp://$address:$service_port", $errno, $errstr, $timeout);

      if (!$fp) {
	$err=_("socket creation error")." : $errstr ($errno)\n";
	$info=array("status"=>self::error_connect);
      } 

    
      if ($err=="") {
	$in = "CONVERT\n";
	// echo "Envoi de la commande $in ...";    
	fputs($fp,$in);


	$out = trim(fgets($fp, 2048));
	//      echo "[$out].\n";
	if ($out=="Continue") {
	  $basename=str_replace('"','_',basename($filename));
	  $mime=getSysMimeFile($filename, $basename);

	  $in = "<TE name=\"$te_name\" fkey=\"$fkey\" fname=\"$basename\" size=\"$size\" mime=\"$mime\" callback=\"$callback\"/>\n";
#echo "Envoi du header $in ...";    
	  fputs($fp,$in);
	  $out = trim(fgets($fp));
	  $status="KO";
	  if (preg_match("/status=[ ]*\"([^\"]*)\"/i",$out,$match)) {
	    $status=$match[1];
	  }
	  if ($status=='OK') {
	    //echo "Envoi du fichier $filename ...";

	    if (file_exists($filename)) {
	      $handle = @fopen($filename, "r");
	      if ($handle) {
		while (!feof($handle)) {
		  $buffer = fread($handle, 2048);
		  $cout=fwrite($fp,$buffer,strlen($buffer));	      
		}	
		fclose($handle);
	      }

     
	      fflush($fp);
	      //echo "OK.\n";
     

	      // echo "Lire la réponse : \n\n";
	      $out = trim(fgets($fp));
	      if (preg_match("/status=[ ]*\"([^\"]*)\"/i",$out,$match)) {
		$status=$match[1];
	      }
	      if (preg_match("/<response[^>]*>(.*)<\/response>/i",$out,$match)) {
		$outmsg=$match[1];
	      }
	      //echo "Response [$status]\n";
	      //echo "Message [$outmsg]\n";
	      if ($status == "OK") {
		if (preg_match("/ id=[ ]*\"([^\"]*)\"/i",$outmsg,$match)) {
		  $tid=$match[1];
		}
		if (preg_match("/status=[ ]*\"([^\"]*)\"/i",$outmsg,$match)) {
		  $status=$match[1];
		}
		if (preg_match("/<comment>(.*)<\/comment>/i",$outmsg,$match)) {
		  $comment=$match[1];
		}
		$info=array("tid"=>$tid,
			    "status"=>$status,
			    "comment"=>$comment);
	      } else {
		$err= $outcode." [$outmsg]";
	      }      
	    }
	  } else {
	    $taskerr='-';
	    if (preg_match("/<comment>(.*)<\/comment>/i",$out,$match)) {
              $info=array("status"=>self::error_noengine);
	      $err=$match[1];
	    } else {
	      $err=_("Error sending file");    
	      $info=array("status"=>self::error_sendfile);
	      
	    }
	  }
	}
	//echo "Fermeture de la socket...";
	fclose($fp);
      }
    } else {
      $err=_("empty file");
      $info=array("status"=>self::error_emptyfile);
    }
    return $err;
  }
  /**
   * send a request to get information about a task
   * @param int $tid_task identificator
   * @param array &$info transformation task info return "tid"=> ,"status"=> ,"comment=>
   * 
   * @return string error message, if no error empty string
   */
  function getInfo($tid,&$info) {  
    $err="";

    /* Lit l'adresse IP du serveur de destination */
    $address = gethostbyname($this->host);
    $service_port = $this->port;

    /* Cree une socket TCP/IP. */
    //    echo "Essai de connexion à '$address' sur le port '$service_port'...\n";
    //    $result = socket_connect($socket, $address, $service_port);

    $fp = stream_socket_client("tcp://$address:$service_port", $errno, $errstr, 30);

    if (!$fp) { 
      $err=_("socket creation error")." : $errstr ($errno)\n";

    } 

    if ($err=="") {

      $in = "INFO\n";
      //echo "Envoi de la commande $in ...";    
      fputs($fp,$in);

      $out = trim(fgets($fp, 2048));
      //echo "[$out].\n";
      if ($out=="Continue") {
    
	$in = "<TASK id=\"$tid\" />\n";
	//echo "Envoi du header $in ...";    
	fputs($fp,$in);
     

	$out = trim(fgets($fp));
	if (preg_match("/status=[ ]*\"([^\"]*)\"/i",$out,$match)) {
	  $status=$match[1];
	}	
	
	if ($status == "OK") {

	  //echo "<br>Response <b>$out</b>";

	  if (preg_match("/<task[^>]*>(.*)<\/task>/i",$out,$match)) {
	    $body=$match[1];
	    //	echo "Response $body";
	    if (preg_match_all("|<[^>]+>(.*)</([^>]+)>|U",
			       $body,
			       $reg,
			       PREG_SET_ORDER) ) {
	      
	      foreach ($reg as $v) {
		$info[$v[2]]=$v[1];
	      }
	    }
	  }	      
	} else {
	  $msg="";
	  if (preg_match("/<response[^>]*>(.*)<\/response>/i",$out,$match)) {
	    $msg=$match[1];
	  }
	  $err= $status." [$msg]";
	}            
      }
      
    
    
      //echo "Fermeture de la socket...";
      fclose($fp);
    }

    return $err;
  }

  /**
   * send a request to retrieve a transformation and to erase task from server
   * the status must be D (Done) or K (Done but errors).
   * @param string $tid Task identification
   * @param string $filename the path where put the file (must be writeable)
   * 
   * @return string error message, if no error empty string
   */
  function getTransformation($tid,$filename) {
    $err=$this->getAndLeaveTransformation($tid,$filename);
    $this->eraseTransformation($tid);
  }


  /**
   * send a request for retrieve a transformation
   * the status must be D (Done) or K (Done but errors).
   * all working files are stayed into the server : be carreful to clean it after (use ::eraseTransformation)
   * @param string $tid Task identification
   * @param string $filename the path where put the file (must be writeable)
   * 
   * @return string error message, if no error empty string
   */
  function getAndLeaveTransformation($tid,$filename) {

  
    $err="";

    $handle = @fopen($filename, "w");
    if (!$handle) {
      $err=sprintf("cannot open file <%s> in write mode",$filename);
      return $err;
    }

    /* Lit l'adresse IP du serveur de destination */
    $address = gethostbyname($this->host);
    $service_port = $this->port;

    /* Cree une socket TCP/IP. */
    //echo "Essai de connexion à '$address' sur le port '$service_port'...\n";
    //    $result = socket_connect($socket, $address, $service_port);

    $fp = stream_socket_client("tcp://$address:$service_port", $errno, $errstr, 30);

    if (!$fp) {
      $err=_("socket creation error")." : $errstr ($errno)\n";
    } 

    
    if ($err=="") {
      $in = "GET\n";
      //echo "Envoi de la commande $in ...";    
      fputs($fp,$in);


      $out = trim(fgets($fp, 2048));
      //echo "[$out].\n";
      if ($out=="Continue") {

	$in = "<task id=\"$tid\" />\n";
	//echo "Envoi du header $in ...";    
	fputs($fp,$in);
	//echo "Recept du file size ...";
	$out = trim(fgets($fp, 2048));

	//echo "[$out]\n";
	if (preg_match("/status=[ ]*\"([^\"]*)\"/i",$out,$match)) {
	  $status=$match[1];
	}
	if ($status=="OK") {

	  if (preg_match("/size=[ ]*\"([^\"]*)\"/i",$out,$match)) {
	    $size=$match[1];
	  }
	
	  //echo "Recept du fichier $filename ...";


	  if ($handle) {
	    $trbytes=0;
	    do {
	      if ($size >= 2048) {
		$rsize=2048;
	      } else {
		$rsize=$size;
	      }
	   
	      $buf = fread($fp, $rsize);
	      $l=strlen($buf);
	      $trbytes+=$l;
	      $size-=$l;
	      $wb=fwrite($handle,$buf);	     
	      //echo "file:$l []";
	    } while ($size>0);


	    fclose($handle);
	  }

	  //echo "Wroted  $filename\n.";
    
	  // echo "Lire la réponse : \n\n";
	  $out = trim(fgets($fp, 2048));
	  if (preg_match("/status=[ ]*\"([^\"]*)\"/i",$out,$match)) {
	    $status=$match[1];
	  }
	  if ($status!="OK") {
	    if (preg_match("/<response[^>]*>(.*)<\/response>/i",$out,$match)) {
	      $msg=$match[1];
	    }
	    $err="$status:$msg";
	  }
	} else {
	  // status not OK 
	  $msg="";
	  if (preg_match("/<response[^>]*>(.*)<\/response>/i",$out,$match)) {
	    $msg=$match[1];
	  }
	  $err="$status:$msg";
	}
      }
    }
    //echo "Fermeture de la socket...";
    fclose($fp);
    return $err;
  }

  /**
   * erase transformation
   * delete associated files in the server engine
   * @param string $tid Task identification
   * @param string $filename the path where put the file (must be writeable)
   * @param array &$info transformation task info return "tid"=> ,"status"=> ,"comment=>
   * 
   * @return string error message, if no error empty string
   */
  function eraseTransformation($tid) {  
    $err="";

    /* Lit l'adresse IP du serveur de destination */
    $address = gethostbyname($this->host);
    $service_port = $this->port;

    /* Cree une socket TCP/IP. */
    //    echo "Essai de connexion à '$address' sur le port '$service_port'...\n";
    //    $result = socket_connect($socket, $address, $service_port);

    $fp = stream_socket_client("tcp://$address:$service_port", $errno, $errstr, 30);

    if (!$fp) { 
      $err=_("socket creation error")." : $errstr ($errno)\n";

    } 

    if ($err=="") {

      $in = "ABORT\n";
      //echo "Envoi de la commande $in ...";    
      fputs($fp,$in);

      $out = trim(fgets($fp, 2048));
      //echo "[$out].\n";
      if ($out=="Continue") {
    
	$in = "<TASK id=\"$tid\" />\n";
	//echo "Envoi du header $in ...";    
	fputs($fp,$in);
     
	$out = trim(fgets($fp));
	if (preg_match("/status=[ ]*\"([^\"]*)\"/i",$out,$match)) {
	  $status=$match[1];
	}	
	if ($status == "OK") {

	  //echo "<br>Response <b>$out</b>";

	  if (preg_match("/<task[^>]*>(.*)<\/task>/i",$out,$match)) {
	    $body=$match[1];
	    //	echo "Response $body";
	    if (preg_match_all("|<[^>]+>(.*)</([^>]+)>|U",
			       $body,
			       $reg,
			       PREG_SET_ORDER) ) {
	      
	      foreach ($reg as $v) {
		$info[$v[2]]=$v[1];
	      }
	    }
	  }	      
	} else {
	  $msg="";
	  if (preg_match("/<response[^>]*>(.*)<\/response>/i",$out,$match)) {
	    $msg=$match[1];
	  }
	  $err= $status." [$msg]";
	}            
      }
      
    
    
      //echo "Fermeture de la socket...";
      fclose($fp);
    }

    return $err;
  }
}
?>