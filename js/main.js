google.setOnLoadCallback(function(){
  $("button").button();
});


function clearDrops() {
  FB.api("/me", function(response){
    $.post("include/ajax.php", {action: "cleardrops", id: response.id}, function(data) {
      $(".droppic").children().fadeOut("slow", function() {
        $(".droppic").html("<img src='images/sad.jpg' width=75 />").css("display", "none").fadeIn("slow");
      });
    });
  })
}