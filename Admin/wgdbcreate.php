<?php

include("WHAT/Lib.Common.php");
include("WHAT/Lib.WCheck.php");


$err=checkPGConnection();
if ($err=="") {
  $err=getCheckApp($pubdir,$applications);
  if ($err) {
        exec ( "$pubdir/CORE/CORE_post I" , $out ,$err );
	//$out = shell_exec("$pubdir/CORE/CORE_post I 2>/tmp/w");
    print "$pubdir/CORE/CORE_post I<br>";
    print_r2($out);
    print("<br>err:$err");
  }
 }


?>