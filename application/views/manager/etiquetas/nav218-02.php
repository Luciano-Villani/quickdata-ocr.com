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

			<!--<li class="nav-item dropdown">
				<a href="#" class="navbar-nav-link dropdown-toggle caret-0" data-toggle="dropdown">
					<i class="icon-git-compare"></i>
					<span class="d-md-none ml-2"> Git updates pc</span>
					<span class="badge badge-pill bg-warning-400 ml-auto ml-md-0">9</span>
				</a>

				<div class="dropdown-menu dropdown-content wmin-md-350">
					<div class="dropdown-content-header">
						<span class="font-weight-semibold">Git updates</span>
						<a href="#" class="text-default"><i class="icon-sync"></i></a>
					</div>

					<div class="dropdown-content-body dropdown-scrollable">
						<ul class="media-list">
							<li class="media">
								<div class="mr-3">
									<a href="#"
										class="btn bg-transparent border-primary text-primary rounded-round border-2 btn-icon"><i
											class="icon-git-pull-request"></i></a>
								</div>

								<div class="media-body">
									Drop the IE <a href="#">specific hacks</a> for temporal inputs
									<div class="text-muted font-size-sm">4 minutes ago</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#"
										class="btn bg-transparent border-warning text-warning rounded-round border-2 btn-icon"><i
											class="icon-git-commit"></i></a>
								</div>

								<div class="media-body">
									Add full font overrides for popovers and tooltips
									<div class="text-muted font-size-sm">36 minutes ago</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#"
										class="btn bg-transparent border-info text-info rounded-round border-2 btn-icon"><i
											class="icon-git-branch"></i></a>
								</div>

								<div class="media-body">
									<a href="#">Chris Arney</a> created a new <span
										class="font-weight-semibold">Design</span> branch
									<div class="text-muted font-size-sm">2 hours ago</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#"
										class="btn bg-transparent border-success text-success rounded-round border-2 btn-icon"><i
											class="icon-git-merge"></i></a>
								</div>

								<div class="media-body">
									<a href="#">Eugene Kopyov</a> merged <span
										class="font-weight-semibold">Master</span> and <span
										class="font-weight-semibold">Dev</span> branches
									<div class="text-muted font-size-sm">Dec 18, 18:36</div>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<a href="#"
										class="btn bg-transparent border-primary text-primary rounded-round border-2 btn-icon"><i
											class="icon-git-pull-request"></i></a>
								</div>

								<div class="media-body">
									Have Carousel ignore keyboard events
									<div class="text-muted font-size-sm">Dec 12, 05:46</div>
								</div>
							</li>
						</ul>
					</div>

					<div class="dropdown-content-footer bg-light">
						<a href="#" class="text-grey mr-auto">All updates</a>
						<div>
							<a href="#" class="text-grey" data-popup="tooltip" title="Mark all as read"><i
									class="icon-radio-unchecked"></i></a>
							<a href="#" class="text-grey ml-2" data-popup="tooltip" title="Bug tracker"><i
									class="icon-bug2"></i></a>
						</div>
					</div>
				</div>
			</li> -->
		</ul>
		<?php
$grupos= '';

		foreach($this->ion_auth->get_users_groups()->result() as $grupo){
			$grupos .= $grupo->description;  
		}
		
		?>

		<span class="badge bg-success ml-md-3 mr-md-auto">MVL Online <?= $grupos?></span>

		<ul class="navbar-nav">
			<!-- <li class="nav-item dropdown">
				<a href="#" class="navbar-nav-link dropdown-toggle caret-0" data-toggle="dropdown">
					<i class="icon-people"></i>
					<span class="d-md-none ml-2">Users</span>
				</a> 

				<div class="dropdown-menu dropdown-menu-right dropdown-content wmin-md-300">
					<<div class="dropdown-content-header">
						<span class="font-weight-semibold">Users online</span>
						<a href="#" class="text-default"><i class="icon-search4 font-size-base"></i></a>
					</div>

					<<div class="dropdown-content-body dropdown-scrollable">
						<ul class="media-list">
							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<a href="#" class="media-title font-weight-semibold">Jordana Ansley</a>
									<span class="d-block text-muted font-size-sm">Lead web developer</span>
								</div>
								<div class="ml-3 align-self-center"><span
										class="badge badge-mark border-success"></span></div>
							</li>

							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<a href="#" class="media-title font-weight-semibold">Will Brason</a>
									<span class="d-block text-muted font-size-sm">Marketing manager</span>
								</div>
								<div class="ml-3 align-self-center"><span class="badge badge-mark border-danger"></span>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<a href="#" class="media-title font-weight-semibold">Hanna Walden</a>
									<span class="d-block text-muted font-size-sm">Project manager</span>
								</div>
								<div class="ml-3 align-self-center"><span
										class="badge badge-mark border-success"></span></div>
							</li>

							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<a href="#" class="media-title font-weight-semibold">Dori Laperriere</a>
									<span class="d-block text-muted font-size-sm">Business developer</span>
								</div>
								<div class="ml-3 align-self-center"><span
										class="badge badge-mark border-warning-300"></span></div>
							</li>

							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<a href="#" class="media-title font-weight-semibold">Vanessa Aurelius</a>
									<span class="d-block text-muted font-size-sm">UX expert</span>
								</div>
								<div class="ml-3 align-self-center"><span
										class="badge badge-mark border-grey-400"></span></div>
							</li>
						</ul>
					</div>

					<div class="dropdown-content-footer bg-light">
						<a href="#" class="text-grey mr-auto">All users</a>
						<a href="#" class="text-grey"><i class="icon-gear"></i></a>
					</div>
				</div>
			</li> 
			-->

			<!--<li class="nav-item dropdown">
				<a href="#" class="navbar-nav-link dropdown-toggle caret-0" data-toggle="dropdown">
					<i class="icon-bubbles4"></i>
					<span class="d-md-none ml-2">Messages</span>
					<span class="badge badge-pill bg-warning-400 ml-auto ml-md-0">2</span>
				</a>

				<div class="dropdown-menu dropdown-menu-right dropdown-content wmin-md-350">
					<div class="dropdown-content-header">
						<span class="font-weight-semibold">Messages</span>
						<a href="#" class="text-default"><i class="icon-compose"></i></a>
					</div>

					<div class="dropdown-content-body dropdown-scrollable">
						<ul class="media-list">
							<li class="media">
								<div class="mr-3 position-relative">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>

								<div class="media-body">
									<div class="media-title">
										<a href="#">
											<span class="font-weight-semibold">James Alexander</span>
											<span class="text-muted float-right font-size-sm">04:58</span>
										</a>
									</div>

									<span class="text-muted">who knows, maybe that would be the best thing for
										me...</span>
								</div>
							</li>

							<li class="media">
								<div class="mr-3 position-relative">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>

								<div class="media-body">
									<div class="media-title">
										<a href="#">
											<span class="font-weight-semibold">Margo Baker</span>
											<span class="text-muted float-right font-size-sm">12:16</span>
										</a>
									</div>

									<span class="text-muted">That was something he was unable to do because...</span>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<div class="media-title">
										<a href="#">
											<span class="font-weight-semibold">Jeremy Victorino</span>
											<span class="text-muted float-right font-size-sm">22:48</span>
										</a>
									</div>

									<span class="text-muted">But that would be extremely strained and
										suspicious...</span>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<div class="media-title">
										<a href="#">
											<span class="font-weight-semibold">Beatrix Diaz</span>
											<span class="text-muted float-right font-size-sm">Tue</span>
										</a>
									</div>

									<span class="text-muted">What a strenuous career it is that I've chosen...</span>
								</div>
							</li>

							<li class="media">
								<div class="mr-3">
									<img src="<?= base_url('assets/manager/images/users/icon-1.png') ?>" width="36"
										height="36" class="rounded-circle" alt="">
								</div>
								<div class="media-body">
									<div class="media-title">
										<a href="#">
											<span class="font-weight-semibold">Richard Vango</span>
											<span class="text-muted float-right font-size-sm">Mon</span>
										</a>
									</div>

									<span class="text-muted">Other travelling salesmen live a life of luxury...</span>
								</div>
							</li>
						</ul>
					</div>

					<div class="dropdown-content-footer justify-content-center p-0">
						<a href="#" class="bg-light text-grey w-100 py-2" data-popup="tooltip" title="Load more"><i
								class="icon-menu7 d-block top-0"></i></a>
					</div>
				</div>
			</li> -->

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
							<div class="font-size-xs opacity-50 font-weight-semibold"> Municipalidad de Vicente López</div>
							<div class="font-size-xs opacity-50 d-none">
								<i class="icon-pin font-size-sm"></i> &nbsp;Vicente López, BsAs
							</div>
						</div>

						<!--<div class="ml-3 align-self-center">
							<a href="#" class="text-white"><i class="icon-cog3"></i></a>
						</div> -->
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
					<li class="nav-item nav-item-menu" data-seccion="obras">
						<a href="/Admin/Obras" class="nav-link">
							<i class="icon-construction" data-seccion="datos"></i>
							<span>
								Obras
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
						<div class="text-uppercase font-size-xs line-height-xs">RELACIONES JURIDISCION-PROVEEDOR</div> <i class="icon-menu"
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
					
					<!-- <li class="nav-item nav-item-submenu">
						<a href="#" class="nav-link"><i class="icon-stack2"></i> <span>Proveedores</span></a>



						<ul class="nav nav-group-sub" data-submenu-title="Page layouts">
							<?php foreach ($proveedores as $prov): ?>
								<li class="nav-item"><a href="/Admin/Lecturas/<?= $this->encrypt->encode(urlencode($prov->id))?>" class="nav-link">
										<?= $prov->nombre ?>
									</a></li>
							<?php endforeach; ?>
						</ul>
					</li> -->

					<!-- /layout -->
					<!-- Main -->
					<!-- <li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">ADMIN</div> <i class="icon-menu"
							title="ADMIN"></i>
					</li>
					<li class="nav-item">
						<a href="#" class="nav-link">
							<i class="icon-home4" data-seccion="<?= $this->router->fetch_class(); ?>"></i>
							<span>
								Dashboard
								<span class="d-block font-weight-normal opacity-50">No active orders</span>
							</span>
						</a>
					</li>
					<li class="nav-item nav-item-submenu">
						<a href="#" class="nav-link"><i class="icon-users"></i> <span>Estructura Programática</span></a>
						<ul class="nav nav-group-sub" data-submenu-title="ABM Usuarios">
							<li class="nav-item" data-seccion="listados"><a href="<?= base_url('Admin/Secretarias') ?>"
									class="nav-link">Secretarías</a></li>
							<li class="nav-item" data-seccion="listados"><a href="<?= base_url('Admin/Dependencias') ?>"
									class="nav-link">Dependencias</a></li>
							<li class="nav-item" data-seccion="listados"><a href="<?= base_url('Admin/Programas') ?>"
									class="nav-link">Programas</a></li>
							<li class="nav-item" data-seccion="listados"><a href="<?= base_url('Admin/dependencias') ?>"
									class="nav-link">Proyectos</a></li>
							<li class="nav-item" data-seccion="listados"><a href="<?= base_url('Admin/dependencias') ?>"
									class="nav-link">Obras</a></li>
							<li class="nav-item" data-seccion="listados"><a href="<?= base_url('Admin/proveedores') ?>"
									class="nav-link">Proveedores</a></li>

						</ul>
					</li>
					<li class="nav-item-header">
						<div class="text-uppercase font-size-xs line-height-xs">Administración</div> <i
							class="icon-menu" title="Layout options"></i>
					</li>
					<li class="nav-item nav-item-submenu">
						<a href="#" class="nav-link"><i class="icon-color-sampler"></i> <span>Usuarios</span></a>

						<ul class="nav nav-group-sub" data-submenu-title="Themes">
							<li class="nav-item" data-seccion="listados"><a
									href="<?= base_url('Admin/usuarios/listados') ?>" class="nav-link">Listados</a></li>
							<li class="nav-item" data-seccion="agregar"><a
									href="<?= base_url('Admin/usuarios/agregar') ?>" class="nav-link ">Altas</a></li>
						</ul>
					</li> -->

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