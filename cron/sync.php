<?php

require_once 'include/facebook.php';
require_once 'include/db.inc';

$facebook = new Facebook(array(
  'appId'  => '143455525682745',
  'secret' => 'e12993af8b317072d7bffe725a19d898',
  'cookie' => true, // enable optional cookie support
));
//$uid = "100001397895126";
$uid = "10506960";
$access_token = getOfflineAccessToken($uid);
$friendurl = "https://graph.facebook.com/".$uid."/friends?access_token=".$access_token;

$ch = curl_init();
curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
curl_setopt( $ch, CURLOPT_URL, $friendurl );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$content = curl_exec( $ch );
echo "\n".$content."\n";

function getOfflineAccessToken($id) {
  $query = 'SELECT token FROM user WHERE id='.$id;
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  $at = mysql_fetch_array($result);
  return $at[0];
}
?>
