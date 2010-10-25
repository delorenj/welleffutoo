google.setOnLoadCallback(function(){
  $("button").button();
  initEmailRadioButton();
  $("input[name='email-notification']").click(function() {
    setEmailNotification($(this).val());
  });
});


function clearDrops() {
  FB.api("/me", function(response){
    $.post("include/ajax.php", {action: "cleardrops", id: response.id}, function(data) {
      $(".droppic").parent().css("border", "2px dashed #AAAAAA");
      $(".droppic").children().fadeOut("slow", function() {
        $(".droppic").html("<img src='images/sad.jpg' width=75 />").css("display", "none").fadeIn("slow");
      });
    });
  });
}

function setEmailNotification(val) {
  FB.api("/me", function(response){
    $.post("include/ajax.php", {action: "setemailnotification", id: response.id, val: val});
  });
}

function initEmailRadioButton() {
  FB.api("/me", function(response){
    $.post("include/ajax.php", {action: "getemailnotification", id: response.id}, function(isSet){
      if(isSet == 0) {
        console.log("Setting email OFF");
        $("#email-off").attr("checked", "true");
      } else {
        console.log("Setting email ON");
        $("#email-on").attr("checked", "true");
      }
    });
  });
}