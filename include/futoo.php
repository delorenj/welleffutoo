<?php
require_once 'facebook.php';
require_once 'db.inc';

class drops {
  public $id;
  public $user_id;
  public $friend_name;
  public $friend_pic;
  public $time;
}

class futoo {

  private $session;
  public $facebook;

  public function __construct() {
    $this->facebook = new Facebook(array(
                'appId' => '143455525682745',
                'secret' => 'e12993af8b317072d7bffe725a19d898',
                'cookie' => true, // enable optional cookie support
    ));

    $this->session = $this->facebook->getSession();
  }

  public function getSession() {
    return $this->session;
  }

  public function getMe($token = null) {
    if(isset($token)) {
      return $this->facebook->api('/me?access_token='.$token);
    } else {
      return $this->facebook->api('/me');
    }
  }

  public function insertIntoDropsTable($uid, $dropped) {
    print_r("Adding dropped user to drops table :: Dropped:$dropped\n");
    $droppee = $this->getUser($dropped);
    $fname = $droppee['name'];
    $query = 'INSERT INTO drops (user_id, friend_id) VALUES ('.$uid.','.$dropped.')';
    mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  }

  public function SendDropNotification($uid, $dropped) {
    if(!$this->email_activated($uid)) return;
    print_r("Sendind Drop Notification :: To:$uid\tSubject:$dropped\n");
    $access_token = $this->getOfflineAccessToken($uid);
    $droppee = $this->getUser($dropped);
    $sendemail_url = "https://api.facebook.com/method/notifications.sendEmail?recipients=" . $uid .
            "&access_token=" . $access_token .
            "&subject=" . urlencode($droppee['name'] . " is no longer your friend") .
            "&text=Welleffutoo,%20" . urlencode($droppee['name'] . "!") .
            "&fbml=" . urlencode("<fb:profile-pic uid='" . $dropped . "' size='normal' width='60' />");
//  print_r("Send URL: $sendemail_url\n");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
    curl_setopt($ch, CURLOPT_URL, $sendemail_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($ch);
    $result_a = json_decode($content, true);
  }

  public function getUser($uid) {
#    print_r("Getting user object: $uid");
    $access_token = $this->getOfflineAccessToken($uid);
    $url = "https://graph.facebook.com/" . $uid . "?fields=name&access_token=" . $access_token;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($ch);
    $result_a = json_decode($content, true);
    $result["name"] = $result_a["name"];    
    return $result;
  }

  public function updateFriendsInDatabase($id, $currentFriends) {
    $sfriends = addslashes(serialize($currentFriends));
    $query = 'UPDATE user SET `friends`="' . $sfriends . '" WHERE `id`=' . $id;
    mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
  }

  public function getMyFriendsFromFacebookAPI() {
    $call = $this->facebook->api('/me/friends?fields=id');
    $data = $call['data'];
    $friends[] = null;
    foreach($data as $friend) {
      $friends[] = $friend["id"];
    }
    return $friends;
  }
  
  public function getFriendsFromFacebookAPI($uid) {
    #error_log("Querying for " . $this->getUser($uid) . "'s friends using FB API");
    $access_token = $this->getOfflineAccessToken($uid);
    #error_log("Token: ". $access_token);
    $friendurl = "https://graph.facebook.com/" . $uid . "/friends?fields=id&access_token=" . $access_token;
    error_log("URL: ".$friendurl);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
    curl_setopt($ch, CURLOPT_URL, $friendurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    do {
      $content = curl_exec($ch);
      #error_log("Content: ".$content);
      $result_a = json_decode($content, true);
      #error_log("result_a: ".$result_a);
      $data = $result_a["data"];
      error_log("data: ".$data);
    }while(!isset($data));
    $friends[] = null;
    foreach ($data as $friend) {
      $friends[] = $friend["id"];
    }
    return array_slice($friends, 1);
  }

  public function getAppId() {
    return $this->facebook->getAppId();
  }

  public function setOfflineAccessToken($id) {
    error_log("Setting the offline access token in the database");
    $token = null;
    if (isset($_SESSION["token"])) {
      error_log("index.php: Got access token! Inserting into DB under UserID=" . $id);
      $token = $_SESSION["token"];
    } else {
      error_log("Error!: No offline_session token available. Can't init user!");
      header("location:index.php");
    }
    $query = 'UPDATE user SET `token`="' . $token . '" WHERE `id`=' . $id;
    mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
  }

  public function getNumFriendsFromDB($id) {
    error_log("Querying for number of friends as indicated by the user's database entry");
    $query = 'SELECT num_friends FROM user WHERE id=' . $id;
    $result = mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
    $num = mysql_fetch_array($result);
    return $num[0];
  }

  public function accountInitialized($uid) {
#    error_log("Checking for existing account: $uid");
    $query = 'SELECT COUNT(*) FROM user WHERE id=' . $uid;
    $result = mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
    $num = mysql_fetch_array($result);
    return ((int)$num[0] > 0 ? true:false);

  }

  public function getOfflineAccessToken($id) {
#    error_log("Pulling offline access token from database");
    $query = 'SELECT token FROM user WHERE id=' . $id;
    $result = mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
    $at = mysql_fetch_array($result);
    return $at[0];
  }

  public function getFriendsFromDB($id) {
#    error_log("Pulling " . $id . "'s friends from database");
    $query = 'SELECT friends FROM user WHERE id=' . $id;
    $result = mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
    $serialized = mysql_fetch_array($result);
    return unserialize($serialized[0]);
  }

  public function getDroppedFriends($id, $limit) {
#    error_log("Pulling " . $id . "'s last $limit dropped friends from database");
    $query = 'SELECT * FROM drops WHERE user_id=' . $id . ' LIMIT ' . $limit;
    $result = mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
    while ($row = mysql_fetch_array($result)) {
      $d = new Drops();
      $d->id = $row['id'];
      $d->user_id = $row['user_id'];
      $d->friend_id = $row['friend_id'];
      $d->friend_name = $row['friend_name'];
      $d->friend_pic = $row['friend_pic'];
      $d->time = $row['time'];
      $rows[] = $d;
    }
    return $rows;
  }

  public function getAllIds() {
    $query = "SELECT id FROM user";
    $result = mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
    while ($row = mysql_fetch_array($result)) {
      $rows[] = $row['id'];
    }
    return $rows;
  }

  public function test_getUserParams($uid) {
   $user = $this->getUser($uid);
   print_r($user);
  }
  public function email_activated($uid) {
    $query = 'SELECT email_notification FROM user WHERE id=' . $uid;
    $result = mysql_query($query) or die("Error running query:" . mysql_error() . "\n\nQuery:" . $query);
    $at = mysql_fetch_array($result);
    return ($at[0] == "0" ? false:true);

  }
}
?>
