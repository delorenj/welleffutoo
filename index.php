<?php

require './include/facebook.php';

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
    $call = $facebook->api('/me/friends');
    $friends = $call['data'];
  } catch (FacebookApiException $e) {
    error_log($e);
  }
}

// login or logout url will be needed depending on current user state.
if ($me) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl();
}

// This call will always work since we are fetching public data.
$carrie = $facebook->api('/carrie.vitale');

?>

<html xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
      <div id="fb-root"></div>
      <script>
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
        };

        (function() {
          var e = document.createElement('script');
          e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
          e.async = true;
          document.getElementById('fb-root').appendChild(e);
        }());
      </script>

      <?php if ($me): ?>
      <a href="<?php echo $logoutUrl; ?>">
        <img src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif">
      </a>
      <?php else: ?>
      <div>
        <fb:login-button></fb:login-button>
      </div>
      <?php endif ?>

      <h1><?php echo $me["name"] ?>'s Friend List <?php echo " (".count($friends).")";?></h1>
      <?php foreach($friends as $f): ?>
        <? echo $f['name']."<br>"; ?>
      <?php endforeach ?>
    </body>
</html>
