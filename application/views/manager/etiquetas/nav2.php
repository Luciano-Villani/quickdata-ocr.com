<style>
        #areaBadge, #toggleVisibility {
            transition: none; /* Desactiva transiciones para pruebas */
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
$area= 'Proveedores';

		foreach($this->ion_auth->get_users_groups()->result() as $grupo){
			$grupos .= $grupo->description;  
		}
		
		?>

		<span class="badge bg-success ml-md-3 mr-md-auto">MVL Online <?= $grupos?></span>
		<button class="tab-button btn-secondary" id="toggleVisibility"></button>

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

        <li class="nav-item nav-item-menu" data-seccion="lecturas">
            <a href="/Admin/Lecturas" class="nav-link" data-url="/Admin/Lecturas">
                <i class="icon-upload" data-seccion="datos"></i>
                <span>Datos leídos OCR</span>
            </a>
        </li>

        <li class="nav-item-header">
            <div class="text-uppercase font-size-xs line-height-xs">Estructura programática</div> <i class="icon-menu" title="Estructura programática"></i>
        </li>

        <li class="nav-item nav-item-menu">
            <a href="/Admin/Secretarias" class="nav-link" data-url="/Admin/Secretarias">
                <i class="icon-office" data-seccion="datos"></i>
                <span>Secretarias</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu">
            <a href="/Admin/Programas" class="nav-link" data-url="/Admin/Programas">
                <i class="icon-newspaper" data-seccion="datos"></i>
                <span>Programas</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu">
            <a href="/Admin/Proyectos" class="nav-link" data-url="/Admin/Proyectos">
                <i class="icon-pen" data-seccion="datos"></i>
                <span>Proyectos</span>
            </a>
        </li>

        <li class="nav-item nav-item-menu" data-seccion="dependencias">
            <a href="/Admin/Dependencias" class="nav-link" data-url="/Admin/Dependencias">
                <i class="icon-store" data-seccion="datos"></i>
                <span>Dependencias</span>
            </a>
        </li>

        <li class="nav-item-header">
            <div class="text-uppercase font-size-xs line-height-xs">RELACIONES</div> <i class="icon-menu" title="RELACIONES"></i>
        </li>
        
        <li class="nav-item nav-item-menu" data-seccion="indexaciones">
            <a href="/Admin/Indexaciones" class="nav-link" data-url="/Admin/Indexaciones">
                <i class="icon-database" data-seccion="datos"></i>
                <span>Indexadores</span>
            </a>
        </li>
        
        <li class="nav-item-header">
            <div class="text-uppercase font-size-xs line-height-xs">Proveedores</div> <i class="icon-menu" title="Proveedores"></i>
        </li>
        
        <li class="nav-item nav-item-menu" data-seccion="proveedores">
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
        var mostrandoElectromecanica = localStorage.getItem('mostrandoElectromecanica');

        // Si es null (primer acceso), por defecto es Proveedores
        if (mostrandoElectromecanica === null) {
            mostrandoElectromecanica = false;
            localStorage.setItem('mostrandoElectromecanica', mostrandoElectromecanica);
        } else {
            mostrandoElectromecanica = JSON.parse(mostrandoElectromecanica);
        }
        

        // Función para actualizar la visibilidad, estilos y URLs
        function actualizarVisibilidad() {
            var toggleButton = $('#toggleVisibility');
            var areaBadge = $('#areaBadge');
            var links = $('.nav-item-menu a'); // Seleccionar todos los enlaces del menú

            // 🌟 NUEVO: Elemento del ícono de seguimiento de proveedores 🌟
            var seguimientoProveedoresItem = $('#nav-item-proveedores-seguimiento'); 

            // Cambiar el texto del botón y el color del badge
            if (mostrandoElectromecanica) {
                areaBadge.text('Electromecánica').removeClass('bg-success').addClass('bg-primary');
                toggleButton.text('Ir a Proveedores').removeClass('bg-primary').addClass('bg-success');
            } else {
                areaBadge.text('Proveedores').removeClass('bg-primary').addClass('bg-success');
                toggleButton.text('Ir a Electromecánica').removeClass('bg-success').addClass('bg-primary');
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

        // 🌟 NUEVO: Manejar clic en el botón de seguimiento de proveedores (para cargar AJAX) 🌟
        $('#dropdown_seguimiento_proveedores').on('click', function() {
            // Cargar la lista solo si el contenido es el mensaje inicial
            if ($('#seguimiento-content-proveedores').text().includes('Haga clic para cargar la lista')) {
                cargarListaSeguimientoProveedores();
            }
        });

        // Manejar clic en el botón
        $('#toggleVisibility').click(function() {
            mostrandoElectromecanica = !mostrandoElectromecanica;
            localStorage.setItem('mostrandoElectromecanica', mostrandoElectromecanica);
            actualizarVisibilidad();

            // Redirigir según la vista actual
            var redirectionUrl = mostrandoElectromecanica ? '/Electromecanica/Consolidados' : '/Admin/Consolidados';
            window.location.replace(redirectionUrl);
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
    
    // 2. LLAMADA AJAX PARA OBTENER EL CONTEO
    $.ajax({
        url: url_conteo, // 💡 USA LA URL DINÁMICA
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.count !== undefined) {
                var count = parseInt(response.count);
                
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






