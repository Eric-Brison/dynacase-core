<html>
<head>
<title>FreeDom installation check</title>

<LINK REL="stylesheet" type="text/css" HREF="Layout/wg.css" >
<script language="JavaScript" src="../WHAT/Layout/logmsg.js"></script>

<script>
function showhide(ide) {
  if (!document.getElementById(ide)) return;
  var idde = document.getElementById(ide);
  if (idde.style.display!='block') idde.style.display = 'block';
  else idde.style.display = 'none';
  return;
}
</script>
<style>
html {
background-color:#e3e3e3;
}
body {
  font-size:9pt;
  font-family: "MS trbuchet", verdana, sans-serif;
  padding:10px;
  margin:10px;
  border:1px solid #a3a3a3;
}
.test {
  margin : 10px;
}
.test .title {
  font-size:110%;
  font-weight : bold;
  border-bottom:3px solid #006600;
}
  
.test .result {
  margin : 0px 0px 10px 10px;
  padding: 3px 3px 3px 15px;
}
.error {
  color : red;
  border-left:3px solid red;
  padding: 3px 3px 3px 15px;
}
.warning { 
  color : orange;
  border-left:3px solid orange;
  padding: 3px 3px 3px 15px;
}
.info {
  color : #a3a3a3;
  border-left:3px solid #a3a3a3;
  padding: 3px 3px 3px 15px;
}
.pre {
  font-size:7pt;
  font-family : courier;
  background-color: #000000;
  color : yellow;
  margin : 3px 3px 3px 10px;
  border : 1px solid yellow;
  padding: 5px;
  display:none;
}
</style>
</head>
<body>
<img height="100px" style="float:right; border:none;" src="Images/freeeye.png">
<h3>FreeDom installation check....</h3><a href="winit.html">Admin page</a>
<span class="info">Informational text</span> - <span class="warning">Warning text</span> - <span class="error">Error text</span>
<?php

global $ntest, $extfct, $pearmod; 
$ntest = 0;
$extfct = 0;
$prgexec = 0;
$pearmod = 0;

  testStart("General");
  
  $versionref = 5.1;
  $version = phpversion();
  if (floatval($version) >= $versionref) testInfo("PHP Version  : ".$version);
  else testError("PHP Version >= $versionref required (currently $version)");
  
  testInfo("PHP include path : ".ini_get("include_path"));

  testDone();


  $programs = array(
		"rm" => "E",
		"file" => "E",
		"mkdir" => "E",
		"tar" => "E",
		"zip" => "E",
		"unzip" => "E",
		"dot" => "E",
		"convert" => "W",
		"html2ps" => "E",
		"ps2pdf" => "E",
		"php" => "E",
		"ldapdelete" => "W",
		"psql" => "E",
		"pg_dump" => "W",
  		);

  $extensions = array(
	"GETTEXT" => array("f" => "gettext", "l"=>"E", "c"=>""),
	"PGSQL" => array("f" => "pg_connect", "l"=>"E", "c"=>""),
	"GD" => array("f" => "imagegd", "l"=>"E", "c"=>""),
        "XML" => array("f" => "xml_set_default_handler", "l"=>"E", "c"=>""),
        "MHASH" => array("f" => "mhash", "l"=>"W", "c"=>"This function is used by user management to generate informations for samba authentication on FreeDom."),
 	"LDAP" => array("f" => "ldap_connect", "l"=>"W", "c"=>"This extension is used for ldap connexion from FreeDom."),
	);
			
  $pearmodules = array ( "Crypt_CHAP", "Net_SMTP",  "Mail_Mime");

  testStart("Commands used test");
  foreach ($programs as $p => $s) testExecProgram($p, $s);
  testDone();

  testStart("Extensions used test");
  foreach ($extensions as $p => $s) testEXTfct($p, $s["f"], $s["l"], $s["c"]);
  testDone();

  testStart("PEAR installation, check access to PEAR.php");
  if (@require("PEAR.php")) testInfo("Found.");
  else testError("Please check configuration, PEAR.php must be found in include_path");
  testDone();

  testStart("PEAR : check PEAR configuration...");
  foreach ($pearmodules as $p => $s) testPEARModule($s);
  testDone();


  exit;

function testExecProgram($prg, $lvl="E", $comment="") {
  global $prgexec;
  $prgexec++;
  exec("which $prg", $out, $rr);
  $mt = '<span onclick="showhide(\'prg_'.$prgexec.'\')" style="cursor:pointer" > ['.$prgexec.'] Program '.$prg.'</span> : ';
  if ($rr==0) testInfo($mt."Ok");
  else {
     $msg = $mt."Please check configuration, program  $prg not found".($comment!=""?"<br> [info] ".$comment:"");
     if ($lvl=="W") testWarning($msg);
     else testError($msg);
  }
  testText('prg_'.$prgexec, $out);
}


function testEXTfct($ext, $fct, $lvl="E", $comment="") {
  global $extfct;
  $extfct++;
  $mt = '<span>['.$extfct.'] '.$ext.', test function '.$fct.'() >';
  if (function_exists($fct)) testInfo($mt."Found.");
  else {
     $msg = $mt."Please check configuration, function $fct() not found".($comment!=""?"<br> [info] ".$comment:"");
     if ($lvl=="W") testWarning($msg);
     else testError($msg);
  }
}

function testPEARModule($mod) {
  global $pearmod;
  $pearmod++;
  exec("pear list $mod", $out, $rr);
  $mt = '<span onclick="showhide(\'pear_'.$pearmod.'\')" style="cursor:pointer">['.$pearmod.'] Pear::'.$mod.'>';
  if ($rr==0) testInfo($mt." Ok");
  else testError($mt." not found");
  testText('pear_'.$pearmod, $out);
}

function testStart($test) {
   global $ntest;
   $ntest++;
   echo '<div class="test">';
   echo '<div class="title">['.$ntest.'] '.$test.'</div>';
   echo '<div class="result">';
}
function testError($msg) {
   echo '<div class="error">'.$msg.'</div>';
}
function testWarning($msg) {
   echo '<div class="warning">'.$msg.'</div>';
}
function testInfo($msg) {
   echo '<div class="info">'.$msg.'</div>';
}
function testText($id, $msg) {
   print '<div id="'.$id.'" class="pre"><pre>';
   if (is_array($msg)) foreach ($msg as $k => $v) echo $v."\n";
    else print $msg;
   print '</pre></div>';
}
function testDone() {
   echo '</div>';
   echo '</div>';
}

?>
</body>
</html>
