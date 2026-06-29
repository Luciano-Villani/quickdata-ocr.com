<style>
        #areaBadge {
            transition: none; /* Desactiva transiciones para pruebas */
        }
        .mvl-topbar-icon {
            position: relative;
            min-width: 42px;
            justify-content: center;
        }
        .mvl-module-switch {
            display: inline-flex;
            align-items: center;
            gap: 0;
            margin-left: 1rem;
            margin-right: auto;
            padding: 3px;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: .42rem;
            background: rgba(255,255,255,.08);
            box-shadow: inset 0 0 0 1px rgba(0,0,0,.05);
        }
        .mvl-module-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 128px;
            min-height: 32px;
            padding: .38rem .9rem;
            border-radius: .32rem;
            color: rgba(255,255,255,.78);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .035em;
            text-transform: uppercase;
            transition: background .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .mvl-module-tab:hover {
            color: #fff;
            background: rgba(255,255,255,.12);
            text-decoration: none;
        }
        .mvl-module-tab.active {
            color: #fff;
            background: #075cf7;
            box-shadow: 0 8px 18px rgba(0,0,0,.18);
        }
        .mvl-role-badge {
            margin-left: .75rem;
            margin-right: .75rem;
            font-size: .68rem;
            opacity: .95;
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
        .mvl-sidebar-area { display: none; }
        .mvl-sidebar-section > .nav-link {
            margin: .15rem .55rem;
            border-radius: .35rem;
            font-weight: 700;
            letter-spacing: .01em;
            color: rgba(255,255,255,.92);
            background: rgba(255,255,255,.055);
        }
        .mvl-sidebar-section > .nav-link:hover,
        .mvl-sidebar-section.nav-item-open > .nav-link {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        .mvl-sidebar-section .nav-group-sub {
            margin: .15rem .55rem .45rem;
            border-left: 2px solid rgba(255,255,255,.12);
        }
        .mvl-sidebar-section .nav-group-sub .nav-link {
            padding-top: .55rem;
            padding-bottom: .55rem;
        }
        .mvl-sidebar-section .nav-group-sub .nav-link span {
            white-space: normal;
            line-height: 1.2;
        }
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin: .2rem .55rem;
            padding: .72rem .9rem;
            border-radius: .45rem;
            cursor: pointer;
            background: rgba(255,255,255,.055);
            color: rgba(255,255,255,.92);
            font-weight: 700;
            min-height: 42px;
            transition: background .15s ease, color .15s ease;
        }
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle > div {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1;
            font-size: .78rem;
            letter-spacing: .025em;
        }
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle:hover,
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle.mvl-sidebar-open {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle > i {
            display: none;
        }
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle::after {
            content: "";
            width: .48rem;
            height: .48rem;
            flex: 0 0 auto;
            border-right: 2px solid rgba(255,255,255,.72);
            border-bottom: 2px solid rgba(255,255,255,.72);
            transform: rotate(-45deg);
            transition: transform .15s ease, border-color .15s ease;
        }
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle.mvl-sidebar-open::after {
            transform: rotate(45deg);
            border-color: #fff;
        }
        .nav-sidebar .nav-item-header.mvl-sidebar-toggle + .nav-item-menu {
            margin-top: .15rem;
        }
        .nav-sidebar .nav-item-menu.mvl-sidebar-collapsed {
            display: none;
        }
    </style>

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
$is_electro_route = strpos($_SERVER['REQUEST_URI'], '/Electromecanica') === 0;
$area= $is_electro_route ? 'Electromecanica' : 'Proveedores';
$nav_user_first = (isset($this->user) && is_object($this->user) && isset($this->user->first_name)) ? $this->user->first_name : '';
$nav_user_last = (isset($this->user) && is_object($this->user) && isset($this->user->last_name)) ? $this->user->last_name : '';
$nav_is_financiero = $this->ion_auth->is_financiero() && !$this->ion_auth->is_admin() && !$this->ion_auth->is_super();
$nav_show_dashboard_financiero = $this->ion_auth->is_super() || $nav_is_financiero;

		foreach($this->ion_auth->get_users_groups()->result() as $grupo){
			$grupos .= $grupo->description;  
		}
		
		?>

        <?php if (!$nav_is_financiero) { ?>
        <div class="mvl-module-switch" id="mvlModuleSwitch" aria-label="Selector de modulo">
            <a href="/Admin/Consolidados" class="mvl-module-tab <?= !$is_electro_route ? 'active' : '' ?>" data-module="proveedores">Proveedores</a>
            <a href="/Electromecanica/Consolidados" class="mvl-module-tab <?= $is_electro_route ? 'active' : '' ?>" data-module="electromecanica">Electromec&aacute;nica</a>
        </div>
        <?php } else { ?>
        <span class="badge bg-primary mvl-role-badge mr-md-auto">Finanzas</span>
        <?php } ?>

		<ul class="navbar-nav">
            
           <li class="nav-item dropdown ml-3 mr-2" id="nav-item-proveedores-seguimiento"> 
    
    <a href="#" class="navbar-nav-link dropdown-toggle legitRipple text-white" data-toggle="dropdown" id="dropdown_seguimiento_proveedores">
        <i class="icon-alarm text-white"></i> 
        
        <?php 
            // Inicializamos la variable $conteo_seguimiento (será 0 al cargar)
            $conteo_seguimiento = isset($conteo_proveedores_seguimiento) ? (int)$conteo_proveedores_seguimiento : 0;
        ?>
        
        <span id="badge-seguimiento-proveedores" class="badge bg-danger badge-pill ml-auto ml-md-0" 
              style="<?= ($conteo_seguimiento > 0) ? '' : 'display: none;' ?>">
            <?= $conteo_seguimiento ?>
        </span>
        <script>
            (function () {
                try {
                    var modulo = window.location.pathname.indexOf('/Electromecanica') === 0 ? 'electromecanica' : 'proveedores';
                    var raw = sessionStorage.getItem('mvl_seguimiento_count_' + modulo);
                    if (raw === null) {
                        return;
                    }

                    var total = parseInt(raw || 0, 10);
                    var badge = document.getElementById('badge-seguimiento-proveedores');
                    if (!badge) {
                        return;
                    }

                    badge.textContent = total;
                    badge.style.display = total > 0 ? '' : 'none';
                } catch (e) {}
            }());
        </script>
    </a>

    <div class="dropdown-menu dropdown-menu-right dropdown-content wmin-md-350" id="lista-seguimiento-proveedores">
        <div class="dropdown-content-header">
            <h6 class="font-weight-semibold mb-0">Proveedores en Seguimiento</h6>
            <a href="#" class="text-default" onclick="cargarListaSeguimientoProveedores(); return false;" title="Recargar lista">
                <i class="icon-sync"></i>
            </a>
        </div>
        <div id="seguimiento-content-proveedores" class="dropdown-content-body dropdown-scrollable">
            <div class="p-3 text-center text-muted">Haga clic para cargar la lista.</div>
        </div>
    </div>
</li>

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
						<?= $nav_user_first; ?>
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
							<div class="media-title font-weight-semibold"><?= trim($nav_user_first . ', ' . $nav_user_last, ', '); ?> </div>
							<div class="font-size-xs opacity-10 font-weight-semibold"> Municipio de V. López</div>
							
						</div>

						
					</div>
				</div>
			</div>
			<!-- /user menu -->


			<!-- Main navigation -->
			<div class="card card-sidebar-mobile">
    <ul class="nav nav-sidebar" data-nav-type="accordion">
        <span class="badge bg-success mvl-sidebar-area" id="areaBadge"><?= $area ?></span>
        <!-- Layout -->

        <li class="nav-item-header">
            <div class="text-uppercase font-size-xs line-height-xs">datos</div> <i class="icon-menu" title="datos"></i>
        </li>

        <li class="nav-item nav-item-menu" data-seccion="consolidados">
            <a href="/Admin/Consolidados" class="nav-link" data-url="/Admin/Consolidados">
                <i class="icon-grid7" data-seccion="datos"></i>
                <span>Datos Consolidados</span>
            </a>
        </li>

        <?php if ($nav_show_dashboard_financiero) { ?>
        <li class="nav-item nav-item-menu">
            <a href="/Admin/DashboardFinanciero" class="nav-link" data-url="/Admin/DashboardFinanciero">
                <i class="icon-stats-growth" data-seccion="datos"></i>
                <span>Dashboard Financiero</span>
            </a>
        </li>
        <?php } ?>

        <li class="nav-item nav-item-menu" data-seccion="lecturas" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Lecturas" class="nav-link" data-url="/Admin/Lecturas">
                <i class="icon-upload" data-seccion="datos"></i>
                <span>Datos leídos OCR</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu" data-seccion="vencimientos" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Vencimientos" class="nav-link" data-url="/Admin/Vencimientos">
                <i class="icon-calendar" data-seccion="datos"></i>
                <span>Calendario de vencimientos</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu" data-seccion="auditoria-edenor" <?= ($nav_is_financiero || !$is_electro_route) ? 'style="display:none"' : '' ?>>
            <a href="/Electromecanica/AuditoriaEdenor" class="nav-link" data-url="/Electromecanica/AuditoriaEdenor">
                <i class="icon-clipboard3" data-seccion="datos"></i>
                <span>Auditoria Datos Edenor</span>
            </a>
        </li>

        <li class="nav-item-header" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <div class="text-uppercase font-size-xs line-height-xs">Estructura programática</div> <i class="icon-menu" title="Estructura programática"></i>
        </li>

        <li class="nav-item nav-item-menu" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Secretarias" class="nav-link" data-url="/Admin/Secretarias">
                <i class="icon-office" data-seccion="datos"></i>
                <span>Secretarias</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Programas" class="nav-link" data-url="/Admin/Programas">
                <i class="icon-newspaper" data-seccion="datos"></i>
                <span>Programas</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Proyectos" class="nav-link" data-url="/Admin/Proyectos">
                <i class="icon-pen" data-seccion="datos"></i>
                <span>Proyectos</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu" data-seccion="dependencias" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Dependencias" class="nav-link" data-url="/Admin/Dependencias">
                <i class="icon-store" data-seccion="datos"></i>
                <span>Dependencias</span>
            </a>
        </li>

        <li class="nav-item-header" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <div class="text-uppercase font-size-xs line-height-xs">RELACIONES</div> <i class="icon-menu" title="RELACIONES"></i>
        </li>
        
        <li class="nav-item nav-item-menu" data-seccion="indexaciones" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Indexaciones" class="nav-link" data-url="/Admin/Indexaciones">
                <i class="icon-database" data-seccion="datos"></i>
                <span>Indexadores</span>
            </a>
        </li>
        
        <li class="nav-item-header" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <div class="text-uppercase font-size-xs line-height-xs">Proveedores</div> <i class="icon-menu" title="Proveedores"></i>
        </li>
        
        <li class="nav-item nav-item-menu" data-seccion="proveedores" <?= $nav_is_financiero ? 'style="display:none"' : '' ?>>
            <a href="/Admin/Proveedores" class="nav-link" data-url="/Admin/Proveedores">
                <i class="icon-certificate" data-seccion="datos"></i>
                <span>Lista de Proveedores</span>
            </a>
        </li>

        <?php if ($this->ion_auth->is_super()) { ?>
        <li class="nav-item-header">
            <div class="text-uppercase font-size-xs line-height-xs">Cuentas de usuarios</div> <i class="icon-menu" title="Cuentas de usuarios"></i>
        </li>
        <li class="nav-item nav-item-menu">
            <a href="/Admin/Usuarios" class="nav-link" data-url="/Admin/Usuarios">
                <i class="icon-certificate"></i>
                <span>Administrar Usuarios</span>
            </a>
        </li>
        <?php } ?>
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
    window.MVL_ALERTAS_CONFIG = {
        proveedoresUrl: '<?= base_url('Alertas/vencimientos_topbar/proveedores') ?>',
        electroUrl: '<?= base_url('Alertas/vencimientos_topbar/electromecanica') ?>'
    };

    // -------------------------------------------------------------
// SCRIPT AJAX para cargar la lista de seguimiento
// -------------------------------------------------------------
function cargarListaSeguimientoProveedores() {
    var contentDiv = $('#seguimiento-content-proveedores');
    
    // 💡 DETERMINAR LA URL DE LA LISTA SEGÚN EL MODO
    var currentUrl = window.location.href.toLowerCase();
    var isCanonMode = currentUrl.indexOf('/electromecanica/') !== -1;
    
    var listUrl;
    if (isCanonMode) {
        // URL para Electromecánica (canon)
        listUrl = '<?= base_url('Electromecanica/Consolidados/obtener_lista_seguimiento_canon_ajax') ?>'; 
    } else {
        // URL para Proveedores (default)
        listUrl = '<?= base_url('Consolidados/obtener_lista_seguimiento_ajax') ?>';
    }

    // Mostrar un loader mientras carga
    contentDiv.html('<div class="p-3 text-center text-muted"><i class="icon-spinner2 spinner mr-2"></i> Cargando cuentas...</div>');

    $.ajax({
        // 💡 USAR LA URL DINÁMICA
        url: listUrl, 
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                contentDiv.html(response.html);
            } else {
                contentDiv.html('<div class="p-3 text-center text-danger">Error al cargar los datos.</div>');
            }
        },
        error: function() {
            contentDiv.html('<div class="p-3 text-center text-danger">Error de conexión con el servidor.</div>');
        }
    });
}
        function getSeguimientoUrl() {
    // 1. Obtiene la URL actual y la convierte a minúsculas para una comparación segura
    var currentUrl = window.location.href.toLowerCase();
    
    // 2. Define la URL del controlador AJAX de Proveedores (default)
    // Controlador: application/controllers/Consolidados.php
    var defaultUrl = '<?php echo base_url("Admin/Consolidados/get_seguimiento_count_ajax"); ?>';
    
    // 3. Define la URL del controlador AJAX de Electromecánica
    // Controlador: application/controllers/Electromecanica/Consolidados.php
    var canonUrl   = '<?php echo base_url("Electromecanica/Consolidados/get_seguimiento_canon_count_ajax"); ?>';
    
    // 4. Verifica el modo: Si la URL contiene '/electromecanica/'
    if (currentUrl.indexOf('/electromecanica/') !== -1) {
        return canonUrl;
    } else {
        return defaultUrl;
    }
}

    
	$(document).ready(function() {
        // Obtener el valor de localStorage
        var mostrandoElectromecanica = window.location.pathname.indexOf('/Electromecanica') === 0;
        localStorage.setItem('mostrandoElectromecanica', mostrandoElectromecanica);

        function inicializarMenuLateral() {
            var currentPath = window.location.pathname.toLowerCase();
            var grupos = [];
            var grupoActual = null;

            $('.nav-sidebar > li, .nav-sidebar > span').each(function() {
                var $item = $(this);

                if ($item.hasClass('nav-item-header')) {
                    grupoActual = {
                        header: $item,
                        items: [],
                        key: $.trim($item.text()).toLowerCase()
                    };
                    grupos.push(grupoActual);
                    $item.addClass('mvl-sidebar-toggle');
                    return;
                }

                if (grupoActual && $item.hasClass('nav-item-menu')) {
                    grupoActual.items.push($item);
                }
            });

            function abrirGrupo(grupo, abrir) {
                grupo.header.toggleClass('mvl-sidebar-open', abrir);
                $.each(grupo.items, function(_, $item) {
                    $item.toggleClass('mvl-sidebar-collapsed', !abrir);
                });
            }

            $.each(grupos, function(_, grupo) {
                var esDatos = grupo.key.indexOf('datos') !== -1;
                var contieneRutaActual = false;

                $.each(grupo.items, function(_, $item) {
                    var $link = $item.find('a.nav-link');
                    var href = String($link.attr('href') || '').toLowerCase();
                    $link.removeClass('active');
                    if (href && currentPath.indexOf(href) === 0) {
                        contieneRutaActual = true;
                        $link.addClass('active');
                    }
                });

                abrirGrupo(grupo, esDatos || contieneRutaActual);

                grupo.header.off('click.mvlSidebar').on('click.mvlSidebar', function() {
                    abrirGrupo(grupo, !grupo.header.hasClass('mvl-sidebar-open'));
                });
            });
        }

        

        // Función para actualizar la visibilidad, estilos y URLs
        function actualizarVisibilidad() {
            var areaBadge = $('#areaBadge');
            var links = $('.nav-item-menu a'); // Seleccionar todos los enlaces del menú

            // 🌟 NUEVO: Elemento del ícono de seguimiento de proveedores 🌟
            var seguimientoProveedoresItem = $('#nav-item-proveedores-seguimiento'); 

            // Cambiar el texto del botón y el color del badge
            if (mostrandoElectromecanica) {
                areaBadge.text('Electromecanica').removeClass('bg-success').addClass('bg-primary');
                $('.mvl-module-tab').removeClass('active');
                $('.mvl-module-tab[data-module="electromecanica"]').addClass('active');
            } else {
                areaBadge.text('Proveedores').removeClass('bg-primary').addClass('bg-success');
                $('.mvl-module-tab').removeClass('active');
                $('.mvl-module-tab[data-module="proveedores"]').addClass('active');
            }

            // Actualizar URLs dinámicas
            links.each(function() {
                var $this = $(this);
                var baseUrl = $this.data('url');

                // Ajustar URLs para secciones variables
                switch ($this.closest('li').data('seccion')) {
                    case 'consolidados':
                        $this.attr('href', mostrandoElectromecanica ? baseUrl.replace('/Admin/Consolidados', '/Electromecanica/Consolidados') : baseUrl.replace('/Electromecanica', '/Admin/Consolidados'));
                        break;
                    case 'lecturas':
                        $this.attr('href', mostrandoElectromecanica ? baseUrl.replace('/Admin/Lecturas', '/Electromecanica/Lecturas') : baseUrl.replace('/Electromecanica/Lecturas', '/Admin/Lecturas'));
                        break;
                    case 'vencimientos':
                        $this.attr('href', mostrandoElectromecanica ? baseUrl.replace('/Admin/Vencimientos', '/Electromecanica/Vencimientos') : baseUrl.replace('/Electromecanica/Vencimientos', '/Admin/Vencimientos'));
                        break;
                    case 'indexaciones':
                        $this.attr('href', mostrandoElectromecanica ? baseUrl.replace('/Admin/Indexaciones', '/Electromecanica/Indexaciones') : baseUrl.replace('/Electromecanica/Indexaciones', '/Admin/Indexaciones'));
                        break;
                    case 'dependencias':
                        $this.attr('href', mostrandoElectromecanica ? baseUrl.replace('/Admin/Dependencias', '/Electromecanica/Dependencias') : baseUrl.replace('/Electromecanica/Dependencias', '/Admin/Dependencias'));
                        break;
                    case 'proveedores':
                        $this.attr('href', mostrandoElectromecanica ? baseUrl.replace('/Admin/Proveedores', '/Electromecanica/Proveedores') : baseUrl.replace('/Electromecanica/Proveedores', '/Admin/Proveedores'));
                        break;
                    default:
                        // Para rutas fijas
                        if (baseUrl.includes('/Admin/Secretarias') || baseUrl.includes('/Admin/Programas') || baseUrl.includes('/Admin/Proyectos') || baseUrl.includes('/Admin/Usuarios')) {
                            $this.attr('href', baseUrl); // No cambia para las rutas fijas
                        }
                        break;
                }
            });
        }

        // Inicializar visibilidad al cargar la página
        actualizarVisibilidad();
        inicializarMenuLateral();

        // 🌟 NUEVO: Manejar clic en el botón de seguimiento de proveedores (para cargar AJAX) 🌟
        $('#dropdown_seguimiento_proveedores').on('click', function() {
            // Cargar la lista solo si el contenido es el mensaje inicial
            if ($('#seguimiento-content-proveedores').text().includes('Haga clic para cargar la lista')) {
                cargarListaSeguimientoProveedores();
            }
        });

        $('.mvl-module-tab').click(function(e) {
            e.preventDefault();
            var modulo = $(this).data('module');
            mostrandoElectromecanica = modulo === 'electromecanica';
            localStorage.setItem('mostrandoElectromecanica', mostrandoElectromecanica);
            window.location.href = $(this).attr('href');
        });

        // Manejar clic en el enlace del menú
        $('.nav-item-menu a').click(function(e) {
            e.preventDefault(); // Evitar comportamiento predeterminado del enlace
            var url = $(this).attr('href');
            window.location.href = url;
        });

        // Si la URL actual es una de las rutas fijas, restablecer el estado
        var currentUrl = window.location.pathname;
        if (currentUrl.startsWith('/Admin') || currentUrl.startsWith('/Electromecanica')) {
            // Asegúrate de restablecer el estado a false si es necesario
            if (currentUrl.startsWith('/Admin') && mostrandoElectromecanica) {
                mostrandoElectromecanica = false;
                localStorage.setItem('mostrandoElectromecanica', mostrandoElectromecanica);
                actualizarVisibilidad();
            }
            // Actualiza el estado de la UI si el usuario accede a una ruta fija
        }
        // Script de actualización AJAX CORREGIDO

    
    // La URL apunta a tu método en el controlador Consolidados
    // ----------------------------------------------------------------------------------
    // 🌟 INICIALIZACIÓN DEL CONTEO DE SEGUIMIENTO (DUAL MODE) 🌟
    // ----------------------------------------------------------------------------------
    
    // 1. Obtiene la URL correcta llamando a la función que revisa el modo
    var url_conteo = getSeguimientoUrl(); 
    var $badge = $('#badge-seguimiento-proveedores');
    var seguimientoCacheKey = 'mvl_seguimiento_count_' + (mostrandoElectromecanica ? 'electromecanica' : 'proveedores');
    var seguimientoCache = sessionStorage.getItem(seguimientoCacheKey);

    if (seguimientoCache !== null) {
        var cachedCount = parseInt(seguimientoCache);
        $badge.text(cachedCount);
        if (cachedCount > 0) {
            $badge.show();
        } else {
            $badge.hide();
        }
    }
    
    // 2. LLAMADA AJAX PARA OBTENER EL CONTEO
    $.ajax({
        url: url_conteo, // 💡 USA LA URL DINÁMICA
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.count !== undefined) {
                var count = parseInt(response.count);
                sessionStorage.setItem(seguimientoCacheKey, count);
                
                // 3. Actualiza y muestra/oculta el badge
                $badge.text(count);
                if (count > 0) {
                    $badge.show(); 
                } else {
                    $badge.hide();
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener el conteo de seguimiento:", error);
            $badge.hide(); 
        }
    });

    // ----------------------------------------------------------------------------------
    // MANEJO DE EVENTOS
    // ----------------------------------------------------------------------------------
    
    // Manejar clic en el botón desplegable (llama a la función que ya modificamos)
    $('#dropdown_seguimiento_proveedores').on('click', function() {
        // Llama a la función que ahora es dinámica
        cargarListaSeguimientoProveedores(); 
    });
    });
</script>
