<?php

$token = explode('=', file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=143455525682745&redirect_uri=http://$_SERVER[SERVER_NAME]$_SERVER[PHP_SELF]&client_secret=e12993af8b317072d7bffe725a19d898&code=".(get_magic_quotes_gpc() ? stripslashes($_GET['code']) : $_GET['code'])));
error_log("Got Token: ".$token[1]);
$r = array("token" => $token[1]);
echo json_encode($r);

?>
