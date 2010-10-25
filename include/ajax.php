<?php
if(isset($_POST["action"])) {
  $action = $_POST["action"];
  if(!isset($_POST["id"])) {
    echo "Missing parameter";
    exit();
  }
  switch($action) {
    case "cleardrops":
      clearDrops($_POST["id"]);
      break;
    case "setemailnotification":
      setEmailNotification($_POST["id"], $_POST["val"]);
      break;
    case "getemailnotification":
      getEmailNotification($_POST["id"]);
      break;
  }
}

function clearDrops($id) {
  include "db.inc";
  $query = "DELETE FROM drops WHERE user_id=$id";
  $result = mysql_query($query) or die("Couldn't run query: ".mysql_error());
}

function setEmailNotification($id, $val) {
  include "db.inc";
  $val = (int)$val;
  $query = "UPDATE user SET email_notification=$val WHERE id=$id";
  $result = mysql_query($query) or die("Couldn't run query: ".mysql_error());
}

function getEmailNotification($id) {
  include "db.inc";
  $query = "SELECT email_notification FROM user WHERE id=$id";
  $result = mysql_query($query) or die("Couldn't run query: ".mysql_error());
  $isSet = mysql_fetch_array($result);
  echo $isSet[0];
}
?>
