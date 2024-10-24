<!-- Main navbar -->
<div class="navbar navbar-expand-md navbar-ligth">
	<div class="navbar-brand">
		<a href="<?= base_url() ?>" class="d-inline-block">
			<img src="<?= base_url('assets/manager/images/logo.png') ?>" alt="">
		</a>
	</div>

	<div class="d-md-none" >
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
			<i class="icon-tree5"></i>
		</button>
		<button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
			<i class="icon-paragraph-justify3"></i>
		</button>
	</div>

	<div class="collapse navbar-collapse toolBarHidden " id="navbar-mobile">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
					<i class="icon-paragraph-justify3" style="color: #ffffff"></i>
				</a>
			</li>

			
		</ul>
		<?php
$grupos= '';

		foreach($this->ion_auth->get_users_groups()->result() as $grupo){
			$grupos .= $grupo->description;  
		}
		
		?>

		<span class="badge bg-success ml-md-3 mr-md-auto">MVL Online <?= $grupos?></span>

		<ul class="navbar-nav">
			
			<li class="nav-item dropdown dropdown-user">
				<a href="#" class="navbar-nav-link d-flex align-items-center dropdown-toggle" style="color: #ffffff" data-toggle="dropdown">
					<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" class="rounded-circle mr-2"
						height="34" alt="">
					<span style="color: #fbe9e7">
						<?= $this->user->first_name; ?>
					</span>
				</a>

				<div class="dropdown-menu dropdown-menu-right">
					<a href="#" class="dropdown-item"><i class="icon-user-plus"></i> Mi perfil</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item"><i class="icon-cog5"></i> Configuración de cuenta</a>
					<a href="<?= base_url('Logout') ?>" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
				</div>
			</li>
		</ul>
	</div>
</div>
<!-- /main navbar -->



<div class="page-content ">

	<!-- Main sidebar -->
	<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg bg-nav-mvl">

		<!-- Sidebar mobile toggler -->
		<div class="sidebar-mobile-toggler text-center">
			<a href="#" class="sidebar-mobile-main-toggle">
				<i class="icon-arrow-left8"></i>
			</a>
			Navigation
			<a href="#" class="sidebar-mobile-expand">
				<i class="icon-screen-full"></i>
				<i class="icon-screen-normal"></i>
			</a>
		</div>
		<!-- /sidebar mobile toggler -->


		<!-- Sidebar content -->
		<div class="sidebar-content">

			<!-- User menu -->
			<style>
				div.media{
				width: max-content;
					
				}
				
				
			</style>
			<div class="sidebar-user">
				<div class="card-body">
					<div class="media">
						<div class="mr-3">
							<a href="#"><img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="38"
									height="38" class="rounded-circle" alt=""></a>
						</div>

						<div class="media-body">
							<div class="media-title font-weight-semibold"><?= $this->user->first_name.', '.$this->user->last_name; ?> </div>
							<div class="font-size-xs opacity-10 font-weight-semibold"> Municipio de V. López</div>
							
						</div>

						
					</div>
				</div>
			</div>
			<!-- /user menu -->


			<!-- Main navigation -->
			<div class="card card-sidebar-mobile">
				<ul class="nav nav-sidebar" data-nav-type="accordion">
					<!-- Layout -wwwwwwwwwwwwwwwwwwwwwwwwww -->

					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">datos</div> <i class="icon-menu" title="datos"></i></li>
					<li class="nav-item nav-item-menu"   data-seccion="consolidados" ><a href="/Admin/Consolidados" class="nav-link"><i class="icon-grid7 " data-seccion="datos"></i>
							<span>
								Datos Consolidados 
							</span>
						</a>
					</li>
					<li class="nav-item nav-item-menu " data-seccion="lecturas">
						<a href="/Admin/Lecturas" class="nav-link ">
							<i class="icon-upload" data-seccion="datos"></i>
							<span>
								Datos leídos OCR
							</span>
						</a>
					</li>
					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">Estructura programática</div> <i class="icon-menu"
							title="Estructura programática"></i>
					</li>
			

					<li class="nav-item nav-item-menu" data-seccion="secretarias">
						<a href="/Admin/Secretarias" class="nav-link">
							<i class="icon-office" data-seccion="datos"></i>
							<span>
								Secretarias
							</span>
						</a>
					</li>
		
					<li class="nav-item nav-item-menu" data-seccion="programas">
						<a href="/Admin/Programas" class="nav-link">
							<i class="icon-newspaper" data-seccion="datos"></i>
							<span>
								Programas
							</span>
						</a>
					</li>
					<li class="nav-item nav-item-menu" data-seccion="proyectos">
						<a href="/Admin/Proyectos" class="nav-link">
							<i class="icon-pen" data-seccion="datos"></i>
							<span>
								Proyectos
							</span>
						</a>
					</li>
					
					<li class="nav-item nav-item-menu" data-seccion="dependencias">
						<a href="/Admin/Dependencias" class="nav-link">
							<i class="icon-store" data-seccion="datos"></i>
							<span>
								Dependencias
							</span>
						</a>
					</li>
			

					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">RELACIONES</div> <i class="icon-menu"
							title="Estructura programática"></i>
					</li>
					<li class="nav-item nav-item-menu" data-seccion="indexaciones">
						<a href="/Admin/Indexaciones" class="nav-link">
							<i class="icon-database" data-seccion="datos"></i>
							<span>
							Indexadores
							</span>
						</a>
					</li>
					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">Proveedores</div> <i class="icon-menu"
							title="Estructura programática"></i>
					</li>
					<li class="nav-item nav-item-menu"  data-seccion="proveedores">
						<a href="/Admin/Proveedores" class="nav-link">
							<i class="icon-certificate" data-seccion="datos"></i>
							<span>
								Lista de Proveedores
							</span>
						</a>
					</li>
					<?php if($this->ion_auth->is_super()){ ?>
					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">Cuentas de usuarios</div> <i class="icon-menu"
						title="Estructura programática"></i>
					</li>
					<li class="nav-item nav-item-menu" data-seccion="usuarios">
						<a href="/Admin/Usuarios" class="nav-link">
							<i class="icon-certificate" ></i>
							<span>
								Administrar Usuarios
							</span>
						</a>
					</li>
					
					<?php }?>
					
					<!-- /layout -->
					



				</ul>
			</div>
			<!-- /main navigation -->

		</div>
		<!-- /sidebar content -->

	</div>
	<!-- /main sidebar -->




	<!-- Main content -->
	<div class="content-wrapper">

		
		<!-- /page header -->
		<?php $class_act ?>
		<?php $this->router->fetch_method() ?>
		<script>
			var class_act = '<?= $class_act ?>';
			var method_act = '<?= $method_act ?>';


		</script>





bkp de lo ultimo:

<!-- Main navbar -->
<div class="navbar navbar-expand-md navbar-ligth">
	<div class="navbar-brand">
		<a href="<?= base_url() ?>" class="d-inline-block">
			<img src="<?= base_url('assets/manager/images/logo.png') ?>" alt="">
		</a>
	</div>

	<div class="d-md-none" >
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
			<i class="icon-tree5"></i>
		</button>
		<button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
			<i class="icon-paragraph-justify3"></i>
		</button>
	</div>

	<div class="collapse navbar-collapse toolBarHidden " id="navbar-mobile">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
					<i class="icon-paragraph-justify3" style="color: #ffffff"></i>
				</a>
			</li>

			
		</ul>
		<?php
$grupos= '';
$area= 'Proveedores';

		foreach($this->ion_auth->get_users_groups()->result() as $grupo){
			$grupos .= $grupo->description;  
		}
		
		?>

		<span class="badge bg-success ml-md-3 mr-md-auto">MVL Online <?= $grupos?></span>
		<button class="tab-button btn-secondary" id="toggleVisibility"></button>

		<ul class="navbar-nav">
			
			<li class="nav-item dropdown dropdown-user">
				<a href="#" class="navbar-nav-link d-flex align-items-center dropdown-toggle" style="color: #ffffff" data-toggle="dropdown">
					<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" class="rounded-circle mr-2"
						height="34" alt="">
					<span style="color: #fbe9e7">
						<?= $this->user->first_name; ?>
					</span>
				</a>

				<div class="dropdown-menu dropdown-menu-right">
					<a href="#" class="dropdown-item"><i class="icon-user-plus"></i> Mi perfil</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item"><i class="icon-cog5"></i> Configuración de cuenta</a>
					<a href="<?= base_url('Logout') ?>" class="dropdown-item"><i class="icon-switch2"></i> Logout</a>
				</div>
			</li>
		</ul>
	</div>
</div>
<!-- /main navbar -->



<div class="page-content ">

	<!-- Main sidebar -->
	<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg bg-nav-mvl">

		<!-- Sidebar mobile toggler -->
		<div class="sidebar-mobile-toggler text-center">
			<a href="#" class="sidebar-mobile-main-toggle">
				<i class="icon-arrow-left8"></i>
			</a>
			Navigation
			<a href="#" class="sidebar-mobile-expand">
				<i class="icon-screen-full"></i>
				<i class="icon-screen-normal"></i>
			</a>
		</div>
		<!-- /sidebar mobile toggler -->


		<!-- Sidebar content -->
		<div class="sidebar-content">

			<!-- User menu -->
			<style>
				div.media{
				width: max-content;
					
				}
				
				
			</style>
			<div class="sidebar-user">
				<div class="card-body">
					<div class="media">
						<div class="mr-3">
							<a href="#"><img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="38"
									height="38" class="rounded-circle" alt=""></a>
						</div>

						<div class="media-body">
							<div class="media-title font-weight-semibold"><?= $this->user->first_name.', '.$this->user->last_name; ?> </div>
							<div class="font-size-xs opacity-10 font-weight-semibold"> Municipio de V. López</div>
							
						</div>

						
					</div>
				</div>
			</div>
			<!-- /user menu -->


			<!-- Main navigation -->
			<div class="card card-sidebar-mobile">
				<ul class="nav nav-sidebar" data-nav-type="accordion">
				<span class="badge bg-success" style="font-size: 15px;" id="areaBadge"><?= $area ?></span>
					<!-- Layout -wwwwwwwwwwwwwwwwwwwwwwwwww -->

					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">datos</div> <i class="icon-menu" title="datos"></i></li>
					<li class="nav-item nav-item-menu proveedores" data-seccion="consolidados" ><a href="/Admin/Consolidados" class="nav-link"><i class="icon-grid7 " data-seccion="datos"></i>
							<span>
								Datos Consolidados 
							</span>
						</a>
					</li>

					<li class="nav-item nav-item-menu electromecanica" data-seccion="consolidados" ><a href="/Electromecanica" class="nav-link"><i class="icon-grid7 " data-seccion="datos"></i>
							<span>
								Datos Consolidados
							</span>
						</a>
					</li>


					<li class="nav-item nav-item-menu proveedores" data-seccion="lecturas">
						<a href="/Admin/Lecturas" class="nav-link">
							<i class="icon-upload" data-seccion="datos"></i>
							<span>
								Datos leídos OCR 
							</span>
						</a>
					</li>

					
					<li class="nav-item nav-item-menu electromecanica" data-seccion="lecturas">
						<a href="/Electromecanica/Lecturas" class="nav-link">
							<i class="icon-upload" data-seccion="datos"></i>
							<span>
								Datos leídos OCR
							</span>
						</a>
					</li>

					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">Estructura programática</div> <i class="icon-menu"
							title="Estructura programática"></i>
					</li>
			

					<li class="nav-item nav-item-menu proveedores" data-seccion="secretarias">
						<a href="/Admin/Secretarias" class="nav-link">
							<i class="icon-office" data-seccion="datos"></i>
							<span>
								Secretarias
							</span>
						</a>
					</li>

					<li class="nav-item nav-item-menu electromecanica" data-seccion="secretarias">
						<a href="/Admin/Secretarias" class="nav-link">
							<i class="icon-office" data-seccion="datos"></i>
							<span>
								Secretarias
							</span>
						</a>
					</li>
		
					<li class="nav-item nav-item-menu proveedores" data-seccion="programas">
						<a href="/Admin/Programas" class="nav-link">
							<i class="icon-newspaper" data-seccion="datos"></i>
							<span>
								Programas
							</span>
						</a>
					</li>

					<li class="nav-item nav-item-menu electromecanica" data-seccion="programas">
						<a href="/Admin/Programas" class="nav-link">
							<i class="icon-newspaper" data-seccion="datos"></i>
							<span>
								Programas
							</span>
						</a>
					</li>

					<li class="nav-item nav-item-menu proveedores" data-seccion="proyectos">
						<a href="/Admin/Proyectos" class="nav-link">
							<i class="icon-pen" data-seccion="datos"></i>
							<span>
								Proyectos
							</span>
						</a>
					</li>

					<li class="nav-item nav-item-menu electromecanica" data-seccion="proyectos">
						<a href="/Admin/Proyectos" class="nav-link">
							<i class="icon-pen" data-seccion="datos"></i>
							<span>
								Proyectos
							</span>
						</a>
					</li>
					
					<li class="nav-item nav-item-menu proveedores" data-seccion="dependencias">
						<a href="/Admin/Dependencias" class="nav-link">
							<i class="icon-store" data-seccion="datos"></i>
							<span>
								Dependencias
							</span>
						</a>
					</li>
					<li class="nav-item nav-item-menu electromecanica" data-seccion="dependencias">
						<a href="/Electromecanica/Dependencias" class="nav-link">
							<i class="icon-store" data-seccion="datos"></i>
							<span>
								Dependencias
							</span>
						</a>
					</li>
			

					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">RELACIONES</div> <i class="icon-menu"
							title="Estructura programática"></i>
					</li>
					<li class="nav-item nav-item-menu proveedores" data-seccion="indexaciones">
						<a href="/Admin/Indexaciones" class="nav-link">
							<i class="icon-database" data-seccion="datos"></i>
							<span>
							Indexadores
							</span>
						</a>
					</li>
					<li class="nav-item nav-item-menu electromecanica" data-seccion="indexaciones">
						<a href="/Electromecanica/Indexaciones" class="nav-link">
							<i class="icon-database" data-seccion="datos"></i>
							<span>
							Indexadores
							</span>
						</a>
					</li>
					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">Proveedores</div> <i class="icon-menu"
							title="Estructura programática"></i>
					</li>
					<li class="nav-item nav-item-menu proveedores"  data-seccion="proveedores">
						<a href="/Admin/Proveedores" class="nav-link">
							<i class="icon-certificate" data-seccion="datos"></i>
							<span>
								Lista de Proveedores
							</span>
						</a>
					</li>

					<li class="nav-item nav-item-menu electromecanica"  data-seccion="proveedores">
						<a href="/Electromecanica/Proveedores" class="nav-link">
							<i class="icon-certificate" data-seccion="datos"></i>
							<span>
								Lista de Proveedores
							</span>
						</a>
					</li>
					<?php if($this->ion_auth->is_super()){ ?>
					<li class="nav-item-header proveedores">
						<div class="text-uppercase font-size-xs line-height-xs">Cuentas de usuarios</div> <i class="icon-menu"
						title="Estructura programática"></i>
					</li>
					<li class="nav-item nav-item-menu proveedores" data-seccion="usuarios">
						<a href="/Admin/Usuarios" class="nav-link">
							<i class="icon-certificate" ></i>
							<span>
								Administrar Usuarios
							</span>
						</a>
					</li>
					
					<?php }?>
					
					<!-- /layout -->
					



				</ul>
			</div>
			<!-- /main navigation -->

		</div>
		<!-- /sidebar content -->

	</div>
	<!-- /main sidebar -->




	<!-- Main content -->
	<div class="content-wrapper">

		
		<!-- /page header -->
		<?php $class_act ?>
		<?php $this->router->fetch_method() ?>

		
		
		
  
   <script>
    var class_act = '<?= $class_act ?>';
    var method_act = '<?= $method_act ?>';

    
	$(document).ready(function() {
    // Obtener el valor de localStorage
    var mostrandoElectromecanica = localStorage.getItem('mostrandoElectromecanica');

    // Si es null (primer acceso), por defecto es Proveedores
    if (mostrandoElectromecanica === null) {
        mostrandoElectromecanica = false;
        localStorage.setItem('mostrandoElectromecanica', mostrandoElectromecanica);
    } else {
        mostrandoElectromecanica = JSON.parse(mostrandoElectromecanica);
    }

    // Función para actualizar la visibilidad y estilos
    function actualizarVisibilidad() {
        var toggleButton = $('#toggleVisibility');
        var areaBadge = $('#areaBadge');
        var electromecanicaItems = $('.electromecanica');
        var proveedoresItems = $('.proveedores');

        if (mostrandoElectromecanica) {
            electromecanicaItems.show();
            proveedoresItems.hide();
            toggleButton.text('Ir a Proveedores').removeClass('bg-primary').addClass('bg-success');
            areaBadge.text('Electromecánica').removeClass('bg-success').addClass('bg-primary');
        } else {
            electromecanicaItems.hide();
            proveedoresItems.show();
            toggleButton.text('Ir a Electromecánica').removeClass('bg-success').addClass('bg-primary');
            areaBadge.text('Proveedores').removeClass('bg-primary').addClass('bg-success');
        }
    }

    // Inicializar visibilidad al cargar la página
    actualizarVisibilidad();

    // Manejar clic en el botón
    $('#toggleVisibility').click(function() {
        mostrandoElectromecanica = !mostrandoElectromecanica;
        localStorage.setItem('mostrandoElectromecanica', mostrandoElectromecanica);
        actualizarVisibilidad();

        // Redirigir según la vista actual
        if (mostrandoElectromecanica) {
            window.location.replace('/Electromecanica');
        } else {
            window.location.replace('/Admin/Consolidados');
        }
    });
});
</script>






