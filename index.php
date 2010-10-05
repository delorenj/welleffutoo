<?php
session_start();
require_once './include/futoo.php';

global $futoo;
global $me;
global $friends;
global $drops;

$futoo = new futoo();
$me = null;
$friends = null;
$drops = null;
// Session based API call.
if ($futoo->getSession()) {
  try {
    $me = $futoo->getMe();
    $uid = $me['id'];
    $token = $futoo->getOfflineAccessToken($uid);
    $dbFriends = $futoo->getFriendsFromDB($uid);
    $friends = $futoo->getMyFriendsFromFacebookAPI();
    if($dbFriends == null) {
      initUser();
      $dbFriends = $futoo->getFriendsFromDB($uid);
    }
    else if($token == null) {
      error_log("token was null in db");
      $futoo->setOfflineAccessToken($uid);
    }
    else {
      $drops = $futoo->getDroppedFriends($uid,5);
    }
  } catch (FacebookApiException $e) {
    //header('Location: verify.php');
    error_log($e);
  }
}

if ($me) {
  $logoutUrl = $futoo->facebook->getLogoutUrl();
} else {
  $loginUrl = $futoo->facebook->getLoginUrl();
}
?>

<html xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="http://www.google.com/jsapi?key=ABQIAAAAg5hreqiv4zDpiIkbdnYh2hTzfCc0yQNCbcPtiTLLMI753LI8pxRmlMPmjJmMp2SUicPuSauIcJawDQ" type="text/javascript"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js" type="text/javascript"></script>
        <script src="./js/main.js" type="text/javascript"></script>
        <link href="./css/reset.css" rel="stylesheet" type="text/css" />
        <link href="./css/default.css" rel="stylesheet" type="text/css" />
        <link href='http://fonts.googleapis.com/css?family=IM+Fell+English' rel='stylesheet' type='text/css'>
        <title>WellEffUToo</title>
    </head>
    <body>
      <div id="fb-root"></div>
      <div class="clearfix">
        <a class="logout" href="<?php echo $logoutUrl; ?>"><img border="none" src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif" alt="logout button"></a>
      </div>
      <?php if ($me && ($token != NULL)): ?>
      <div id="header">
        <h1>Welleffutoo</h1>
      </div>
      <div id="content">
        <div id="droppedFriends">
          <?php if($drops) { ?>
            <?php foreach($drops as $d): ?>
            <div class="friendContainer">
              <?php echo "<p>Dropped Friend: $d->friend_name</p>"; ?>
              <?php echo "<img src=".$d->friend_pic." />"; ?>
            </div>
            <?php endforeach ?>
          <?php } else { ?>
            <div id="nodrops">
              <h3>No unfriendings yet</h3>
            </div>
          <?php } ?>
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
          <fb:login-button size="xlarge" perms="offline_access,email" length="long" onlogin='window.location="https://graph.facebook.com/oauth/authorize?client_id=<?echo $futoo->getAppId();?>&scope=offline_access&redirect_uri=http://www.fmlrecovery.com/welleffutoo/facebook_access_token.php";' >Try it out!</fb:login-button>
        </div>
      </div>
      <?php endif ?>

      <script type="text/javascript">
        window.fbAsyncInit = function() {
          FB.init({
            appId   : '<?php echo $futoo->getAppId(); ?>',
            session : <?php echo json_encode($futoo->getSession()); ?>, // don't refetch the session when PHP already has it
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
function initUser() {
  global $me, $friends, $token;
  error_log("Initializing user by creating a database entry");
  if(isset($_SESSION["token"])){
    error_log("index.php: Got access token! Inserting into DB under UserID=".$me["id"]);
    $token = $_SESSION["token"];
  }
  else {
    error_log("Error!: No offline_session token available. Can't init user!");
    header("location:index.php");
  }
  $friends = array_slice($friends, 1);
  $sfriends = serialize($friends);
  $query = 'INSERT INTO user (id, token, num_friends, friends) VALUES ('.$me["id"].',"'.$token.'",'.count($friends).',\''.$sfriends.'\')';
  mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
}
?>