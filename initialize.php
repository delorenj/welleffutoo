<?php

$req = $_REQUEST['signed_request'];

if($req != "") {
  error_log("Request!: ".$req);

}
else {
  error_log("No Request Found");
}

?>
