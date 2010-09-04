<?php

require_once 'include/facebook.php';
require_once 'include/db.inc';
require_once 'include/helpers.php';

global $session;

$uids = getAllIds();
//$uids[0] = "100001538481220";
foreach($uids as $id) {
  $pastFriends = getFriendsFromDB($id);
  $currentFriends = getFriendsFromFacebookAPI($id);
  $friendDiff = array_diff($pastFriends, $currentFriends);
  print_r("DB Friends:\n".serialize($pastFriends)."\n");
  print_r("Actual Friends:\n".serialize($currentFriends)."\n");
  if(empty($friendDiff)) {
    print_r("$id: No Delta\n\n");
  }
  else {
    foreach($friendDiff as $x) {
      print_r("Delta: ".$x." is no longer a friend\n\n");
    }
  }
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
