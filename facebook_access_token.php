<?php
#session_start();
#unset($_SESSION["token"]);
require_once 'include/futoo.php';
global $futoo;
$futoo = new futoo();
$me = $futoo->getMe();
$tries = 0;
do {
  error_log("Generating Access Token: attempt #$tries");
  $token = explode('=', file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=143455525682745&redirect_uri=http://$_SERVER[SERVER_NAME]$_SERVER[PHP_SELF]&client_secret=e12993af8b317072d7bffe725a19d898&code=".(get_magic_quotes_gpc() ? stripslashes($_GET['code']) : $_GET['code'])));

} while(!$token[1] && ($tries++ < 5));

if($token[1]) {
  error_log("Got Token: ".$token[1]);
#  $_SESSION["token"] = $token[1];
  $friends = $futoo->getMyFriendsFromFacebookAPI();
  $friends = array_slice($friends, 1);
  $sfriends = serialize($friends);
  $query = 'REPLACE INTO user (id, token, num_friends, friends) VALUES ('.$me["id"].',"'.$token[1].'",'.count($friends).',\''.$sfriends.'\')';
  mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  header("location:index.php");
}
else {
  error_log("Error getting token!");
  header("location:token_failure.php");
}
?>
