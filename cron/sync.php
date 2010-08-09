<?php

//require_once 'include/db.inc';
require_once 'include/facebook.php';

$facebook = new Facebook(array(
  'appId'  => '143455525682745',
  'secret' => 'e12993af8b317072d7bffe725a19d898',
  'cookie' => true, // enable optional cookie support
));

//curl -F type=client_cred -F client_id=143455525682745 -F client_secret=e12993af8b317072d7bffe725a19d898 https://graph.facebook.com/oauth/access_token
$access_token = $facebook->getAccessToken();
$url = "https://graph.facebook.com/".$facebook->getAppId()."/insights?access_token=".$facebook->getAccessToken();
$friendurl = "https://graph.facebook.com/100001397895126?access_token=".$facebook->getAccessToken();

$ch = curl_init();
curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
curl_setopt( $ch, CURLOPT_URL, $friendurl );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$content = curl_exec( $ch );
echo "\n".$content."\n";
 
?>
