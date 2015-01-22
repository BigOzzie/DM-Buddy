<head>
<?php require("../includes/globalHead.php"); ?>
</head>
<body>
	<div id="encounters">
	</div>
</body>

<script>
$(document).ready(function(){
	generateEncounters();
})

function generateEncounters() {
	url = "generate.php";
	data = {
		characterLevels: [1,1,1,1]
	};
	$.ajax({
		url:url,
		data:data,
		type:"POST",
		success:function(data) {
			$("#encounters").html(data);
		}
	});
}
</script>