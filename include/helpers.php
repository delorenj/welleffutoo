<?php

$facebook = new Facebook(array(
  'appId'  => '143455525682745',
  'secret' => 'e12993af8b317072d7bffe725a19d898',
  'cookie' => true, // enable optional cookie support
));

$session = $facebook->getSession();

function test_serialize() {
  $list = null;
  for($i=0; $i<20; $i++) {
    $list[$i] = $i;
  }
  echo serialize($list);
}

function updateOfflineAccessToken($id) {
  error_log("Setting the offline access token in the database");
  $token = null;
  if(isset($_SESSION["token"])){
    error_log("index.php: Got access token! Inserting into DB under UserID=".$id);
    $token = $_SESSION["token"];
  }
  else {
    error_log("Error!: No offline_session token available. Can't init user!");
    header("location:index.php");
  }
  $query = 'UPDATE user SET `token`="'.$token.'" WHERE `id`='.$id;
  mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
}

function getNumFriendsFromDB($id) {
  error_log("Querying for number of friends as indicated by the user's database entry");
  $query = 'SELECT num_friends FROM user WHERE id='.$id;
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  $num = mysql_fetch_array($result);
  return $num[0];
}

function getOfflineAccessToken($id) {
  error_log("Pulling offline access token from database");
  $query = 'SELECT token FROM user WHERE id='.$id;
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  $at = mysql_fetch_array($result);
  return $at[0];
}

function getFriendsFromDB($id) {
  error_log("Pulling ".$id."'s friends from database");
  $query = 'SELECT friends FROM user WHERE id='.$id;
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  $serialized = mysql_fetch_array($result);
  return unserialize($serialized[0]);
}

function getAllIds() {
  $query = "SELECT id FROM user";
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  while($row = mysql_fetch_array($result)) {
    $rows[] = $row['id'];
  }
  return $rows;
}
?>