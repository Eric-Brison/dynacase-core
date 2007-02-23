<html><head>

</head>
<H1>Check List</H1>
<body>
<?php

include("WHAT/Lib.Common.php");

echo DEFAULT_PUBDIR;

include("dbaccess.php");
echo $dbaccess;

$r=@pg_connect($dbaccess);
if ($r) $dbr_anakeen=true;
 else $dbr_anakeen=false;

$tout["connection db principale"]=array("status"=>1,
					"msg"=>$dbaccess);

$tout["connection db freedom"]=array("status"=>0,
					"msg"=>$dbaccess);



foreach ($tout as $k=>$v) {
  print sprintf("<li>%s : <span style=\"color:%s\">%s</span>",
		$k,$v["status"]?"green
}

?>
</body>
</html>