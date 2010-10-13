<?php
session_start();
require_once './include/futoo.php';

global $futoo;
global $me;
global $drops;

$futoo = new futoo();
$me = null;
$drops = null;
// Session based API call.
if ($futoo->getSession()) {
  try {
    $me = $futoo->getMe();
    $uid = $me['id'];
    $token = $futoo->getOfflineAccessToken($uid);
    if(!$futoo->accountInitialized($uid)) {
      error_log("User has not yet been initialized");
      initUser();
    }
    if($token == null) {
      error_log("token was null in db");
      $futoo->setOfflineAccessToken($uid);
    }
    $drops = $futoo->getDroppedFriends($uid,10);
  } catch (FacebookApiException $e) {
    header('Location: index.php');
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
          <h2>Recently Dropped Friends</h2>
          <?php for($i=0; $i<12; $i++): ?>
            <?php if($i < count($drops)): ?>
              <?php $d = $drops[$i]; ?>
              <div class="friendContainer" style="border:2px dashed blue;" id="drop-<?php echo $i; ?>">
              <?php echo "<fb:profile-pic width=75 height=75 uid=$d->friend_id></fb:profile-pic>"; ?>
            <?php else: ?>
              <div class="friendContainer" id="drop-<?php echo $i; ?>">
                <img src="images/sad.jpg" width="75"/>
            <?php endif; ?>
            </div>
          <?php endfor ?>
          <?php if(!$drops): ?>
            <h3>Awesome! No droppings yet!</h3>
          <?php endif ?>
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
        <fb:friendpile></fb:friendpile>
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
  global $me, $token;
  error_log("Initializing user by creating a database entry");
  if(isset($_SESSION["token"])){
    error_log("index.php: Got access token! Inserting into DB under UserID=".$me["id"]);
    $token = $_SESSION["token"];
  }
  else {
    error_log("Error!: No offline_session token available. Can't init user!");
    header("location:index.php");
  }
  $friends = $futoo->getMyFriendsFromFacebookAPI();
  $friends = array_slice($friends, 1);
  $sfriends = serialize($friends);
  $query = 'REPLACE INTO user (id, token, num_friends, friends) VALUES ('.$me["id"].',"'.$token.'",'.count($friends).',\''.$sfriends.'\')';
  mysql_query($query) or die("Error running query:".mysql_error()."\n\nQuery:".$query);
}
?>