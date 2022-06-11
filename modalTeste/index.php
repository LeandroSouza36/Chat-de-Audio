<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styleModal.css">
	<title>Box Model</title>
</head>
<script>
	function showModal(){
		var element = document.getElementById("modal");
		element.classList.add("show-modal");
	}

	function hideModal(){
		var element = document.getElementById("modal");
		element.classList.remove("show-modal");
	}
	showModal();

</script>
<body>
<!-- modal -->
	<div>

		<button onclick="showModal()">Hello World</button>
		<div class="modal" id="modal">
			<div class="modal-content">
				<span onclick="hideModal()" id="spanModal">&times;</span>
				<h1>Hello World, Modal</h1>
			</div>
		</div>
	</div>
<!-- fim modal-->
</body>
</html>