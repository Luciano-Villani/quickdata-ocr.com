<!DOCTYPE html>
<html lang="en">
	<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="<?= web_assets()?>img/favicon.png">
	<title><?php echo 'CI Template - MySetup'; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<?php
		if(is_array ($css_common )){
			foreach($css_common as $data){
				echo '<link href="'.$data.'"rel="stylesheet">';
			}
		}
	?>
</head>
<body class="onepage" data-bs-spy="scroll" data-bs-target=".navbar">