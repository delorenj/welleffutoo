<?php

require_once 'include/facebook.php';
require_once 'include/db.inc';
require_once 'include/helpers.php';

global $session;

//$uids = getAllIds();
$uids[0] = "100001538481220";
foreach($uids as $id) {
  $pastFriends = getFriendsFromDB($id);
  $currentFriends = getFriendsFromFacebookAPI($id);
  print_r("\n\nDB Friends:\n".serialize($pastFriends)."\n\n");
  print_r("\n\nActual Friends:\n".serialize($currentFriends)."\n\n");

  var_dump($pastFriends);
  var_dump($currentFriends);
}

function getFriendsFromFacebookAPI($uid) {
  error_log("Querying for ".$uid."'s friends using FB API");
  $access_token = getOfflineAccessToken($uid);
  error_log("Token: ". $access_token);
  $friendurl = "https://graph.facebook.com/".$uid."/friends?fields=id&access_token=".$access_token;
  error_log("URL: ".$friendurl);
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
  curl_setopt( $ch, CURLOPT_URL, $friendurl );
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  $content = curl_exec( $ch );
  $result_a = json_decode($content, true);
  $data = $result_a["data"];
  $friends[] = null;
  foreach($data as $friend) {
    $friends[] = $friend["id"];
  }
  return array_slice($friends, 1);
}
?>
