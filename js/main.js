google.setOnLoadCallback(function(){
  $("button").button();
  $("input[name='email-notification']").click(function() {
    alert("clicked:" + $this.val());
  })
});


function clearDrops() {
  FB.api("/me", function(response){
    $.post("include/ajax.php", {action: "cleardrops", id: response.id}, function(data) {
      $(".droppic").parent().css("border", "2px dashed #AAAAAA");
      $(".droppic").children().fadeOut("slow", function() {
        $(".droppic").html("<img src='images/sad.jpg' width=75 />").css("display", "none").fadeIn("slow");
      });
    });
  })
}