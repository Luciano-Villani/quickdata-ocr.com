<!-- Main navbar -->
<style>
    .mvl-topbar-icon {
        position: relative;
        min-width: 42px;
        justify-content: center;
    }
    .mvl-topbar-icon .badge {
        position: absolute;
        top: 4px;
        right: 2px;
        font-size: 10px;
    }
    .mvl-alert-pulse i {
        animation: mvlAlertPulse 1s infinite;
        color: #ff5252 !important;
    }
    @keyframes mvlAlertPulse {
        0% { transform: scale(1); text-shadow: 0 0 0 rgba(255,82,82,.35); }
        50% { transform: scale(1.16); text-shadow: 0 0 9px rgba(255,82,82,.95); }
        100% { transform: scale(1); text-shadow: 0 0 0 rgba(255,82,82,.35); }
    }
</style>
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

		<span class="btn bg-success ml-md-3 mr-md-auto"> <?= strtoupper($grupos)?></span>

		<ul class="navbar-nav">
            <li class="nav-item dropdown ml-1 mr-2" id="nav-item-alertas-vencimientos">
                <a href="#" class="navbar-nav-link dropdown-toggle legitRipple text-white mvl-topbar-icon" data-toggle="dropdown" id="dropdown_alertas_vencimientos" title="Alertas de vencimiento">
                    <i class="icon-bell2 text-white"></i>
                    <span id="badge-alertas-vencimientos" class="badge bg-danger badge-pill" style="display:none">0</span>
                    <script>
                        (function () {
                            try {
                                var modulo = window.location.pathname.indexOf('/Electromecanica') === 0 ? 'electromecanica' : 'proveedores';
                                var raw = sessionStorage.getItem('mvl_alertas_vencimientos_cache_' + modulo);
                                if (!raw) {
                                    return;
                                }

                                var cache = JSON.parse(raw);
                                if (!cache || Date.now() - cache.timestamp > 300000) {
                                    return;
                                }

                                var total = parseInt(cache.total || 0, 10);
                                var badge = document.getElementById('badge-alertas-vencimientos');
                                if (!badge) {
                                    return;
                                }

                                badge.textContent = total;
                                badge.style.display = total > 0 ? 'block' : 'none';
                            } catch (e) {}
                        }());
                    </script>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-content wmin-md-350" id="lista-alertas-vencimientos">
                    <div class="dropdown-content-header">
                        <h6 class="font-weight-semibold mb-0">Alertas de vencimiento</h6>
                        <a href="#" class="text-default" id="marcar_alertas_vistas" title="Marcar como visto">
                            <i class="icon-eye"></i>
                        </a>
                    </div>
                    <div class="dropdown-content-body dropdown-scrollable">
                        <div id="alertas-vencimientos-content" class="p-3 text-center text-muted">
                            <i class="icon-spinner2 spinner mr-2"></i> Cargando alertas...
                        </div>
                    </div>
                </div>
            </li>
			
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

<script>
    window.MVL_ALERTAS_CONFIG = {
        proveedoresUrl: '<?= base_url('Alertas/vencimientos_topbar/proveedores') ?>',
        electroUrl: '<?= base_url('Alertas/vencimientos_topbar/electromecanica') ?>'
    };
</script>



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
						<li class="nav-item nav-item-menu"   data-seccion="admin" ><a href="/Electromecanica" class="nav-link"><i class="icon-grid7 " data-seccion="admin"></i>
						<span>
								Datos Consolidados 
							</span>
							</a>
						</li>
					
					<li class="nav-item nav-item-menu " data-seccion="lecturas">
						<a href="/Electromecanica/Lecturas" class="nav-link ">
							<i class="icon-upload" data-seccion="datos"></i>
							<span>
								Datos leídos OCR
							</span>
						</a>
					</li>
					<li class="nav-item nav-item-menu " data-seccion="vencimientos">
						<a href="/Electromecanica/Vencimientos" class="nav-link ">
							<i class="icon-calendar" data-seccion="datos"></i>
							<span>Calendario de vencimientos</span>
						</a>
					</li>
					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">Estructura programática</div> <i class="icon-menu"
							title="Estructura programática"></i>
					</li>
			

					<li class="nav-item nav-item-menu d-none" data-seccion="secretarias">
						<a href="/Electromecanica/Secretarias" class="nav-link">
							<i class="icon-office" data-seccion="datos"></i>
							<span>
								Secretarias
							</span>
						</a>
					</li>
	
					<!-- <li class="nav-item nav-item-menu" data-seccion="obras">
						<a href="/Admin/Obras" class="nav-link">
							<i class="icon-construction" data-seccion="datos"></i>
							<span>
								Obras
							</span>
						</a>
					</li> -->
					<li class="nav-item nav-item-menu" data-seccion="dependencias">
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
					<li class="nav-item nav-item-menu" data-seccion="indexaciones">
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
					<li class="nav-item nav-item-menu"  data-seccion="proveedores">
						<a href="/Electromecanica/Proveedores" class="nav-link">
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
					<!-- <li class="nav-item nav-item-menu" data-seccion="usuarios">
						<a href="/Admin/Usuarios" class="nav-link">
							<i class="icon-certificate" ></i>
							<span>
								Administrar Usuarios
							</span>
						</a>
					</li> -->
					
					<?php }?>
					
					<!-- /layout -->
					<!-- Main -->
					
					<!-- /main -->



				</ul>
			</div>
			<!-- /main navigation -->

		</div>
		<!-- /sidebar content -->

	</div>
	<!-- /main sidebar -->




	<!-- Main content -->
	<div class="content-wrapper">

		<!-- Page header -->
		<div class="page-header page-header-light d-none">
			<div class="page-header-content header-elements-md-inline">
				<div class="page-title d-flex">
					<h4><i class="icon-arrow-left52 mr-2"></i> <span class="font-weight-semibold">
							<?= $page_title ?>
						</span> -
						<?= $page_datail ?>
					</h4>
					<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
				</div>

				<div class="header-elements d-none">
					<div class="d-flex justify-content-center">
						<a href="#" class="btn btn-link btn-float text-default"><i
								class="icon-bars-alt text-primary"></i><span>Statistics</span></a>
						<a href="#" class="btn btn-link btn-float text-default"><i
								class="icon-calculator text-primary"></i> <span>Invoices</span></a>
						<a href="#" class="btn btn-link btn-float text-default"><i
								class="icon-calendar5 text-primary"></i> <span>Schedule</span></a>
					</div>
				</div>
			</div>

			<div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
				<div class="d-flex">
					<div class="breadcrumb">

						<a href="/Manager" class="breadcrumb-item"><i class="icon-home2 mr-2"></i> Admin</a>
						<a href="#" class="breadcrumb-item">
							<?= $this->router->fetch_class(); ?>
						</a>



						<span class="breadcrumb-item active">
							<?= $this->router->fetch_method() ?>
						</span>
					</div>

					<a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
				</div>

				<div class="header-elements d-none">
					<div class="breadcrumb justify-content-center">
						<a href="#" class="breadcrumb-elements-item">
							<i class="icon-comment-discussion mr-2"></i>
							Support
						</a>

						<div class="breadcrumb-elements-item dropdown p-0">
							<a href="#" class="breadcrumb-elements-item dropdown-toggle" data-toggle="dropdown">
								<i class="icon-gear mr-2"></i>
								Settings
							</a>

							<div class="dropdown-menu dropdown-menu-right">
								<a href="#" class="dropdown-item"><i class="icon-user-lock"></i> Account security</a>
								<a href="#" class="dropdown-item"><i class="icon-statistics"></i> Analytics</a>
								<a href="#" class="dropdown-item"><i class="icon-accessibility"></i> Accessibility</a>
								<div class="dropdown-divider"></div>
								<a href="#" class="dropdown-item"><i class="icon-gear"></i> All settings</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /page header -->
		<?php $class_act ?>
		<?php $this->router->fetch_method() ?>
		<script>
			var class_act = '<?= $class_act ?>';
			var method_act = '<?= $method_act ?>';


		</script>
