<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Ajax</title>
</head>

<body>
	<form>
		<input type="text" name="name">
		<button>Send</button>
	</form>
	<div id="msg"></div>


	<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
	<script>
	$('form').submit(function() {
		var data = $(this).serialize();
		console.log(data);
		$.post('/ajax/post', data, function(response) {
			//console.log(response);
		});

		return false;
	})

	</script>
</body>

</html>
