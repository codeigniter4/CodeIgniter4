<em>&copy; 2021</em>
<script>
$(document).ready(function() {

  // Check for click events on the navbar burger icon
  $(".navbar-burger").click(function() {
	$( ".navbar-menu" ).slideToggle( "slow" );
      // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
      $(".navbar-burger").toggleClass("is-active");
	  
      $(".navbar-menu").toggleClass("is-active");

  });
});
</script>

</body>
</html>