<?php
$dbdata = array("server"=>"localhost",
                "username"=>"fmladmin_futoo",
                "password"=>"Mog43yo...",
                "dbname"=>"fmladmin_futoo"
          );

$db = mysql_connect($dbdata["server"], $dbdata["username"], $dbdata["password"])
         or die("Could not connect:". mysql_error());

mysql_select_db($dbdata["dbname"])
        or die("Could not select database:". mysql_error());

?>
