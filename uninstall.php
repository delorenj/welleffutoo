<?php
require_once "include/futoo.php";
$req = $_REQUEST['signed_request'];
error_log("called uninstall: ".$req);
$result = parse_signed_request($req, "e12993af8b317072d7bffe725a19d898");
$uid = $result['user_id'];

error_log("Deleting user and associated dropsf rom DB: ".$uid);
$query = "DELETE FROM user WHERE id='".$uid."'";
mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);

$query = "DELETE FROM drops WHERE user_id='".$uid."'";
mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);


function parse_signed_request($signed_request, $secret) {
  list($encoded_sig, $payload) = explode('.', $signed_request, 2);

  // decode the data
  $sig = base64_url_decode($encoded_sig);
  $data = json_decode(base64_url_decode($payload), true);

  if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
    error_log('Unknown algorithm. Expected HMAC-SHA256');
    return null;
  }

  // check sig
  $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
  if ($sig !== $expected_sig) {
    error_log('Bad Signed JSON signature!');
    return null;
  }
  return $data;
}

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_', '+/'));
}

?>
