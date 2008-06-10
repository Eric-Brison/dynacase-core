<?php

include_once('Authenticator/authenticator.php');

$auth = new Authenticator(
			  array(
				'type' => 'html',
				'provider' => 'freedom',
				'username' => 'username',
				'password' => 'password',
				'cookie' => 'session',
				'connection' => 'host=localhost dbname=anakeen user=anakeen',
				)
			  );

$status = $auth->checkAuthentication();
if( $status == FALSE ) {
  session_name('session');
  session_start();
  session_regenerate_id();
  session_commit();
  sendAuthPage();
  exit(0);
}
header('Location: index.php');

function sendAuthPage() {
  echo <<<HTMLAUTH
<html><head><title>Authentication</title></head>
<body><form action="auth.php" method="post" encoding="x-www-form-urlencoded">
<input type="text" name="username" id="username" />
<input type="password" name="password" id="password" />
<input type="submit" name="submit" />
</form></body></html>
HTMLAUTH;
}

?>