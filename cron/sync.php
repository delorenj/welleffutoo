<?php

require_once 'include/futoo.php';

global $futoo;

$futoo = new futoo();

$uids = $futoo->getAllIds();
foreach($uids as $id) {
  $pastFriends = $futoo->getFriendsFromDB($id);
  $currentFriends = $futoo->getFriendsFromFacebookAPI($id);
  $droppedFriends = array_diff($pastFriends, $currentFriends);
  $newFriends = array_diff($currentFriends, $pastFriends);
  print_r("DB Friends:\n".serialize($pastFriends)."\n");
  print_r("Actual Friends:\n".serialize($currentFriends)."\n");
  if(empty($droppedFriends) && empty($newFriends)) {    //If nothing  has changed
    print_r("$id: No Delta\n\n");
    continue;                                           //then do nothing
  }
  else {                                                //else do some stuff...
    if (!empty($droppedFriends)) {                       //if drops exist
      foreach ($droppedFriends as $x) {                 //for each drop
        print_r("Dumped!: " . $x . " is no longer a friend\n");
        $futoo->SendDropNotification($id, $x);                  //send a notification
        $futoo->insertIntoDropsTable($id, $x);                  //insert new drops into drop table
      }
    }
    if (!empty($newFriends)) {                          //if adds exist
      foreach ($newFriends as $x) {                     //for each add
        print_r("New Friend: " . $x . "\n");            //let's just print it
      }
    }
    $futoo->updateFriendsInDatabase($id, $currentFriends);      //regardless of delta, update db
  }
}
?>
