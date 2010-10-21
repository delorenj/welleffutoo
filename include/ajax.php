<?php

if(isset($_POST["action"])) {
  $action = $_POST["action"];
  switch($action) {
    case "cleardrops":
      clearDrops();
      break;
  }
}

function clearDrops() {
  echo "OK: Cleared ".$_POST['id']."'s drop list";
}
?>
