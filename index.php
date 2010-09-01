<?php
session_start();

require './include/facebook.php';
require_once './include/db.inc';

$facebook = new Facebook(array(
  'appId'  => '143455525682745',
  'secret' => 'e12993af8b317072d7bffe725a19d898',
  'cookie' => true, // enable optional cookie support
));

$session = $facebook->getSession();

$me = null;
// Session based API call.
if ($session) {
  try {
    $uid = $facebook->getUser();
    $me = $facebook->api('/me');
    $token = getOfflineAccessToken();
    $call = $facebook->api('/me/friends?fields=id');
    $friends = $call['data'];
    $dbFriends = getFriendsFromDB();
    if($dbFriends == null) {
      initUser();
      $dbFriends = getFriendsFromDB();
      header('Location: index.php');
    }
    else if($token == null) {
      error_log("token was null in db");
      updateOfflineAccessToken();
    }
  } catch (FacebookApiException $e) {
    //header('Location: verify.php');
    error_log($e);
  }
}

if ($me) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

?>

<html xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="http://www.google.com/jsapi?key=ABQIAAAAg5hreqiv4zDpiIkbdnYh2hTzfCc0yQNCbcPtiTLLMI753LI8pxRmlMPmjJmMp2SUicPuSauIcJawDQ" type="text/javascript"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js" type="text/javascript"></script>
        <script src="./js/main.js" type="text/javascript"></script>
        <link href="./css/default.css" rel="stylesheet" type="text/css" />
        <link href='http://fonts.googleapis.com/css?family=IM+Fell+English' rel='stylesheet' type='text/css'>
        <title>WellEffUToo</title>
    </head>
    <body>
      <div id="fb-root"></div>
      <?php if ($me && ($token != NULL)): ?>
      <a href="<?php echo $logoutUrl; ?>"><img border="none" src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif" alt="logout button"></a>
      <h1><?php echo $me["name"] ?>'s Friend List <?php echo " (".getNumFriendsFromDB().")";?></h1>
      <p>My Facebook UID: <?php echo $me["id"];?></p>
      <div id="listContainer">
        <div id="realFriends">
          <?php foreach($friends as $f): ?>
            <?php echo $f['id']."<br>"; ?>
          <?php endforeach ?>
        </div>

        <div id="dbFriends">
          <?php foreach($dbFriends as $f): ?>
          <?php echo $f['id']."<br>"; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <div style="text-align: center">
        <div id="siteshout">
          <img src="./images/facebook-photo-collage.jpg" alt="photo collage" />
        </div>
        <div id="sitedesc">
          <p>Tired of spending hours sifting through your list wondering who was the douche that decided your posts were too annoying to deal with anymore? Make life easier and get notified when your friends dump you!</p>
        </div>
        <div style="margin-top:20px;">
          <fb:login-button size="xlarge" perms="offline_access" length="long" onlogin='window.location="https://graph.facebook.com/oauth/authorize?client_id=<?echo $facebook->getAppId();?>&scope=offline_access&redirect_uri=http://www.fmlrecovery.com/welleffutoo/facebook_access_token.php";' >Try it out!</fb:login-button>
        </div>
      </div>
      <?php endif ?>

      <script type="text/javascript">
        window.fbAsyncInit = function() {
          FB.init({
            appId   : '<?php echo $facebook->getAppId(); ?>',
            session : <?php echo json_encode($session); ?>, // don't refetch the session when PHP already has it
            status  : true, // check login status
            cookie  : true, // enable cookies to allow the server to access the session
            xfbml   : true // parse XFBML
          });

          // whenever the user logs in, we refresh the page
          FB.Event.subscribe('auth.login', function() {
            window.location.reload();
          });

          FB.Event.subscribe('auth.logout', function(response) {
            document.location.href = 'index.php'
          });
        };

        (function() {
          var e = document.createElement('script');
          e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
          e.async = true;
          document.getElementById('fb-root').appendChild(e);
        }());
      </script>
    </body>
</html>

<?php
function test_serialize() {
  $list = null;
  for($i=0; $i<20; $i++) {
    $list[$i] = $i;
  }
  echo serialize($list);
}

function initUser() {
  global $me, $friends, $token;
  if(isset($_SESSION["token"])){
    error_log("index.php: Got access token! Inserting into DB under UserID=".$uid);
    $token = $_SESSION["token"];
  }
  else {
    error_log("Error!: No offline_session token available. Can't init user!");
    header("location:index.php");
  }
  $sfriends = addslashes(serialize($friends));
  $query = 'INSERT INTO user (id, token, num_friends, friends) VALUES ('.$me["id"].',"'.$token.'",'.count($friends).',\''.$sfriends.'\')';
  mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
}

function updateOfflineAccessToken() {
  global $me, $token;
  if(isset($_SESSION["token"])){
    error_log("index.php: Got access token! Inserting into DB under UserID=".$uid);
    $token = $_SESSION["token"];
  }
  else {
    error_log("Error!: No offline_session token available. Can't init user!");
    header("location:index.php");
  }
  $query = 'UPDATE user SET `token`="'.$token.'" WHERE `id`='.$me["id"];
  mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
}

function getNumFriendsFromDB() {
  global $me;
  $query = 'SELECT num_friends FROM user WHERE id='.$me["id"];
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  $num = mysql_fetch_array($result);
  return $num[0];
}

function getOfflineAccessToken() {
  global $me;
  $query = 'SELECT token FROM user WHERE id='.$me["id"];
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  $at = mysql_fetch_array($result);
  return $at[0];
}

function getFriendsFromDB() {
  global $me;
  $query = 'SELECT friends FROM user WHERE id='.$me["id"];
  $result = mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
  $serialized = mysql_fetch_array($result);
  return unserialize($serialized[0]);
}
?>