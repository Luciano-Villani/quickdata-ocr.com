<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8" />
	<title><?php echo 'QuickData - BackOffice'; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">

		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
	<!--	css globales-->
	<?php



	if(is_array ($css_common )){
		foreach($css_common as $datar){
			echo '<link href="'.$datar.'?dat='.time().'" rel="stylesheet" type="text/css">';
		}
	}
?>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>


<!--	js globales-->
<?php
	if(is_array ($script_common )){
		foreach($script_common as $datas){
			echo '<script src="'.$datas.'?dat='.time().'"></script>';
		}
	}
?>
	<!--	js locales -->
	<?php
	if(is_array ($script )){
		foreach($script as $datad){
		echo '<script src="'.$datad.'?dat='.time().'"></script>';
		}
	}
?>

</head>

<script>
	
</script>
<body data-tabla= "<?= @$tabla?>" data-data_lote="<?= @$this->lote?>" data-data_action="<?= @$this->BtnText?>"  data-base_url="<?= base_url()?>" data-nro_cuenta="<?= @urlencode($result->nro_cuenta); ?>" class="" >
<div class="container row d-none ">
						<div class="col-md-4">
							<div class="panel panel-body border-top-primary text-center">
								<h6 class="no-margin text-semibold">Colored button</h6>
								<p class="text-muted content-group-sm">Button with contextual colors</p>

		                    	<button type="button" class="btn btn-primary">Default button</button>
							</div>
						</div>

						<div class="col-md-4">
							<div class="panel panel-body border-top-primary text-center">
								<h6 class="no-margin text-semibold">Colored with icon</h6>
								<p class="text-muted content-group-sm">Available in both directions</p>

		                    	<button type="button" class="btn btn-primary"><i class="icon-cog3 position-left"></i> With icon</button>
							</div>
						</div>

						<div class="col-md-4">
							<div class="panel panel-body border-top-primary text-center">
								<h6 class="no-margin text-semibold">Colored with menu</h6>
								<p class="text-muted content-group-sm">Colored button with dropdown</p>

								<div class="btn-group">
			                    	<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></button>
			                    	<ul class="dropdown-menu dropdown-menu-right">
										<li><a href="#"><i class="icon-menu7"></i> Lotes</a></li>
										<li><a href="#"><i class="icon-screen-full"></i> datos api</a></li>
										<li><a href="#"><i class="icon-mail5"></i> One more action</a></li>
										<li class="divider"></li>
										<li><a href="#"><i class="icon-gear"></i> Separated line</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>