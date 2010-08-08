google.setOnLoadCallback(function(){

  $("#progressbar").progressbar();
  $("#progressbar-container").fadeIn();
  var p = 1;
  var i = setInterval(function() {
    if(p == 101) {
      clearInterval(i);
      done();
    }
    $("#progressbar").progressbar({
      value: p++
    });
  },10);
});

function done() {
  FB.api('/me/friends', function(response) {
    alert(response.data);
  });
}