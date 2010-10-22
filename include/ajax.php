<?php
if(isset($_POST["action"])) {
  $action = $_POST["action"];
  switch($action) {
    case "cleardrops":
      if(!isset($_POST["id"])) {
        echo "Missing parameter: id";
        exit();
      }
      clearDrops($_POST["id"]);
      break;
  }
}

function clearDrops($id) {
  include "db.inc";
  $query = "DELETE FROM drops WHERE user_id=$id";
  $result = mysql_query($query) or die("Couldn't run query: ".mysql_error());
}
?>
