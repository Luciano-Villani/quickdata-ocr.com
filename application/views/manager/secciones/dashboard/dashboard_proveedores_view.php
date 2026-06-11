<style>
/* --- ESTILOS MODERNOS Y LIMPIOS --- */

/* Contenedor principal para evitar márgenes raros */
.dashboard-container {
    padding: 30px;
    background-color: #f7f9fc; /* Fondo claro para destacar elementos */
}

/* Header: Limpio y organizado */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

/* Switch (Botones de Alternancia) */
.switch-container {
    display: flex;
    background-color: #e9ecef;
    border-radius: 6px;
    padding: 2px;
}
.switch-container button {
    padding: 8px 18px;
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.3s, color 0.3s;
    border-radius: 4px;
}
.switch-container button.active {
    background-color: #007bff; /* Color primario de la municipalidad */
    color: white;
    box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
}

/* FILTROS */
.filtros-principales label, .filtros-principales select {
    margin-left: 15px;
    font-size: 0.9em;
}

/* GRID DE TARJETAS (Nivel 1: Jurisdicciones/Proveedores) */
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

/* Tarjeta Individual Estilo Dashboard (TOTAL RECEIVED) */
.data-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Sombra suave y profesional */
    border-left: 5px solid #007bff; /* Barra de color para destacar la tarjeta */
    cursor: pointer;
    transition: all 0.3s ease;
}
.data-card:hover {
    transform: translateY(-5px); /* Efecto de elevación al pasar el mouse */
    border-left-color: #0056b3;
}

.card-header {
    font-size: 0.8em;
    color: #6c757d;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
}
.card-metric {
    font-size: 1.8em;
    font-weight: 700;
    color: #343a40;
    margin-bottom: 5px;
}
.card-metric small {
    font-size: 0.5em;
    font-weight: normal;
    color: #6c757d;
    display: block;
    margin-top: 5px;
}

/* Area de Gráfico y Drill-Down */
.drill-down-area {
    padding: 30px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.breadcrumb-path {
    font-size: 1.1em;
    color: #495057;
    margin-bottom: 20px;
}
</style>

<div class="dashboard-container">
    <h1>Dashboard de Control de Gasto Municipal</h1>

    <div class="dashboard-header">
        
        <div class="switch-container" id="vista-switch">
            <button id="switch-jurisdicciones" class="active" data-vista="jurisdicciones">Jurisdicciones</button>
            <button id="switch-proveedores" data-vista="proveedores">Proveedores</button>
        </div>

        <div class="filtros-principales">
            <label for="filtro_mes_comparativo">Período:</label>
            <select id="filtro_mes_comparativo" multiple>
                <option value="<?php echo $mes_actual . '-' . $anio_actual; ?>" selected>
                    <?php echo $mes_actual . '/' . $anio_actual . ' (Actual)'; ?>
                </option>
                </select>

            <label for="filtro_proveedor">Proveedor:</label>
            <select id="filtro_proveedor">
                <option value="">Todos (Default)</option>
                </select>
        </div>
    </div>
    
    <h2 style="color: #495057;">Análisis por <span id="titulo-tarjetas">Jurisdicciones</span></h2>

    <div class="card-grid" id="main-cards-container">
        <?php 
        // Si hay datos iniciales, los renderizamos (usando la nueva estructura de tarjeta)
        if (!empty($jurisdicciones_data)) {
            foreach ($jurisdicciones_data as $jurisdiccion) {
                // Formateamos el gasto en "M" (millones) o "K" (miles) si es necesario,
                // o lo dejamos en formato de moneda para mayor precisión.
                $gasto_formato = number_format($jurisdiccion['gasto_total'], 0, ',', '.'); // Sin decimales por ser tarjeta principal
                
                echo '<div class="data-card" data-id="' . htmlspecialchars($jurisdiccion['jurisdiccion']) . '" data-nivel="1">';
                echo '    <div class="card-header">' . htmlspecialchars($jurisdiccion['jurisdiccion']) . '</div>';
                echo '    <div class="card-metric">$ ' . $gasto_formato . '<small>Total Facturado</small></div>';
                echo '    <div class="card-metric-secondary" style="font-size: 0.9em; color: #6c757d;">' . 
                         $jurisdiccion['facturas_procesadas'] . ' Facturas Procesadas' .
                         '</div>';
                echo '</div>';
            }
        } else {
            echo '<p style="color: #dc3545;">No se encontraron datos de facturación para el período seleccionado.</p>';
        }
        ?>
    </div>

    <div id="main-chart-container" style="height: 400px; margin-top: 40px;">
        <h3 style="color: #495057;">Comparación Mensual por Jurisdicción</h3>
        <canvas id="mainChart"></canvas>
        <p class="text-muted" style="text-align: center; margin-top: 10px;">(Gráfico de barras que mostrará la comparación si se seleccionan múltiples meses)</p>
    </div>

    <div class="drill-down-area">
        <div class="breadcrumb-path">
            <span id="breadcrumb-path-content">Inicio</span>
            <button id="btn-back" style="display:none; margin-left: 15px; padding: 5px 10px;">← Volver</button>
        </div>
        
        <h3 id="drill-down-titulo">Desglose (Seleccione una Tarjeta)</h3>
        
        <div id="drill-down-content" class="card-grid" style="margin-top: 20px;">
            </div>
    </div>
    
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // URL base de tu aplicación CodeIgniter (importante para las llamadas AJAX)
    const BASE_URL = '<?php echo base_url("Dashboard/"); ?>';
    
    // La data inicial viene del controlador y está disponible en PHP. La convertimos a JS.
    const initialData = <?php echo json_encode($jurisdicciones_data); ?>;

    // Constante para mapear número de mes a nombre (útil para la etiqueta en el selector)
    const MONTH_NAMES = {
        '01': 'Enero', '02': 'Febrero', '03': 'Marzo', '04': 'Abril', 
        '05': 'Mayo', '06': 'Junio', '07': 'Julio', '08': 'Agosto',
        '09': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre'
    };

    // 1. ESTADO GLOBAL DEL DASHBOARD
    var dashboardState = {
        vista: 'jurisdicciones', 
        nivel: 1, 
        periodos: [], 
        filtros: {
            // Nombres de los filtros
            jurisdiccion: null,
            programa: null,
            proyecto: null,
            proveedor: null,
            
            // IDs NUMÉRICOS (¡NUEVOS!)
            jurisdiccion_id: null,
            programa_id: null,
            proyecto_id: null,
        },
        history: [] // Historial de filtros
    };

    // --- FUNCIONES DE RENDERIZADO Y LÓGICA CORE ---
    
    /**
     * Renderiza las tarjetas de resumen.
     */
    function renderCards(data, containerId, agrupacion) {
        var html = '';
        var campo_agrupacion = agrupacion;
        var nivel = dashboardState.nivel;

        if (data.length === 0) {
             if (nivel === 1 && dashboardState.periodos.length === 0) {
                 html = '<p class="alert alert-warning">No se encontraron datos de facturación en la base de datos.</p>';
             } else {
                 html = '<p>No se encontraron datos de gasto en este nivel para la selección actual.</p>';
             }
        } else {
            data.forEach(function(item) {
                var titulo = item[campo_agrupacion] || 'Sin Nombre/Asignación';
                
                // CRÍTICO: Obtener el ID numérico del item
                // Asume que el modelo devuelve un campo llamado [agrupacion]_agrupador (ej: 'programa_agrupador')
                var id_agrupador = item[campo_agrupacion + '_agrupador'] || 0; 
                
                var gasto_formato = parseFloat(item.gasto_total).toLocaleString('es-AR', { minimumFractionDigits: 2 });

                html += '<div class="data-card" ' +
                        // data-id para el nombre/descripción (lo que se muestra)
                        'data-id="' + titulo + '" ' +
                        // data-id-num para el ID numérico (lo que se usa para filtrar)
                        'data-id-num="' + id_agrupador + '" ' + 
                        'data-nivel="' + nivel + '" ' +
                        'data-agrupacion="' + campo_agrupacion + '">' +
                        
                        '<div class="card-header">' + titulo + '</div>' + 
                        
                        '<div class="card-metric">$ ' + gasto_formato + '<small>Total Facturado</small></div>' +
                        '<div class="card-metric-secondary" style="font-size: 0.9em; color: #6c757d;">' + 
                        item.facturas_procesadas + ' Facturas Procesadas' +
                        '</div>' +
                    '</div>';
            });
        }
        
        $('#' + containerId).html(html);
        attachCardClickEvents(); 
    }

    /**
     * Restaura los filtros a su estado inicial.
     */
    function resetFilters() {
         dashboardState.filtros = { 
            jurisdiccion: null, 
            programa: null, 
            proyecto: null, 
            proveedor: null,
            jurisdiccion_id: null,
            programa_id: null,
            proyecto_id: null,
        };
    }
    
    /**
     * Actualiza el área de navegación de la interfaz de usuario.
     */
    function updateUI() {
        var breadcrumb = 'Inicio';
        
        if (dashboardState.vista === 'jurisdicciones') {
            if (dashboardState.filtros.jurisdiccion) { breadcrumb += ' > ' + dashboardState.filtros.jurisdiccion; }
            if (dashboardState.filtros.programa) { breadcrumb += ' > ' + dashboardState.filtros.programa; }
            if (dashboardState.filtros.proyecto) { breadcrumb += ' > ' + dashboardState.filtros.proyecto; }
            if (dashboardState.filtros.dependencia) { breadcrumb += ' > ' + dashboardState.filtros.dependencia; } // Nivel 4
        } else if (dashboardState.vista === 'proveedores') {
            if (dashboardState.filtros.proveedor) { breadcrumb += ' > ' + dashboardState.filtros.proveedor; }
            if (dashboardState.filtros.jurisdiccion) { breadcrumb += ' > ' + dashboardState.filtros.jurisdiccion; }
            if (dashboardState.filtros.programa) { breadcrumb += ' > ' + dashboardState.filtros.programa; }
        }
        
        $('#breadcrumb-path-content').html(breadcrumb);
        
        // Manejo del botón Volver y visibilidad de contenedores
        if (dashboardState.nivel > 1) {
            $('#btn-back').show();
            $('#main-cards-container').hide();
            $('#main-chart-container').hide();
        } else {
            $('#btn-back').hide();
            $('#drill-down-content').empty(); 
            $('#main-cards-container').show();
            $('#main-chart-container').show();
        }

        var titulo_desglose = 'Seleccione una Tarjeta para desglosar';
        if (dashboardState.nivel > 1) {
            titulo_desglose = 'Desglose (' + dashboardState.nivel + '): ' + (dashboardState.vista === 'jurisdicciones' ? 'Jerarquía' : 'Gasto por Jurisdicción');
        }
        $('#drill-down-titulo').html(titulo_desglose);
    }

    // --- CARGA DE PERIODOS Y DASHBOARD ---
    
    /**
     * Carga los periodos disponibles del servidor y puebla el selector.
     */
    function cargarPeriodosDisponibles() {
        const url = '<?php echo base_url("Dashboard/get_periodos_disponibles"); ?>'; 
        
        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                poblarSelectorPeriodos(data);
                
                if (data.length > 0) {
                    const periodo_default = { mes: data[0].mes, anio: data[0].anio };
                    dashboardState.periodos = [periodo_default]; 
                    cargarDashboardNivel1();
                } else {
                    $('#main-cards-container').html('<p class="alert alert-warning">No hay datos de facturación en la base de datos para ningún periodo.</p>');
                    updateUI();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar periodos:", error);
                $('#main-cards-container').html('<p class="alert alert-danger">Error al obtener los periodos del servidor.</p>');
            }
        });
    }

    /**
     * Inserta los datos en el selector de periodos y configura el listener de cambio.
     */
    function poblarSelectorPeriodos(periodos) {
        const selector = $('#filtro_mes_comparativo');
        selector.empty(); 

        periodos.forEach((p, index) => {
            const label = `${MONTH_NAMES[p.mes] || p.mes}/${p.anio}`;
            const isActual = index === 0 ? ' (Actual)' : '';
            
            const valor_periodo = JSON.stringify([{ mes: p.mes, anio: p.anio }]);

            selector.append(
                $('<option>', {
                    value: valor_periodo, 
                    text: label + isActual,
                    selected: index === 0 
                })
            );
        });
        
        selector.off('change').on('change', function() {
            const nuevo_periodo_str = $(this).val(); 
            if (nuevo_periodo_str) {
                dashboardState.periodos = JSON.parse(nuevo_periodo_str);
            } else {
                 dashboardState.periodos = [];
            }
            
            // Al cambiar el periodo, REINICIAMOS al Nivel 1 y limpiamos todos los filtros (incluyendo IDs)
            dashboardState.nivel = 1;
            resetFilters(); 
            dashboardState.history = [];

            cargarDashboardNivel1(); 
            updateUI(); 
        });
    }
    
    /**
     * Función que realiza la llamada AJAX para cargar el Nivel 1 del dashboard.
     */
    function cargarDashboardNivel1() {
        var endpoint_name = (dashboardState.vista === 'jurisdicciones') ? 'get_gasto_por_jurisdiccion' : 'get_gasto_por_proveedor';
        var agrupacion = (dashboardState.vista === 'jurisdicciones') ? 'jurisdiccion' : 'proveedor';
        
        var url_completa = BASE_URL + endpoint_name; 

        $.ajax({
            url: url_completa, 
            type: 'POST',
            dataType: 'json',
            data: { 
                periodos: JSON.stringify(dashboardState.periodos),
                proveedor_seleccionado: JSON.stringify(dashboardState.filtros.proveedor) 
            },
            success: function(response) {
                renderCards(response, 'main-cards-container', agrupacion);
                updateUI();
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar el Nivel 1:", xhr.responseText);
                alert("Error al cargar el Nivel 1 del dashboard. URL: " + url_completa); 
                updateUI();
            }
        });
    }


    // --- MANEJO DE EVENTOS (CLIC EN TARJETAS Y VOLVER) ---

    function attachCardClickEvents() {
        $('.data-card').off('click'); 
        
        $('.data-card').on('click', function() {
            var id = $(this).data('id'); // Nombre (Ej: SECRETARIA DE DEPORTES)
            var id_num = $(this).data('id-num'); // ID Numérico (Ej: 15)
            var nivel_actual = $(this).data('nivel');
            
            if (nivel_actual >= 4) {
                 // **NIVEL 5: Carga el listado de facturas**
                 fetchFacturasData(); 
                 return;
            }

            var endpoint = '';
            var campo_agrupacion = '';

            // --- LÓGICA DE DRILL-DOWN PARA JURISDICCIONES ---
            if (dashboardState.vista === 'jurisdicciones') {
                if (nivel_actual === 1) { 
                    dashboardState.filtros.jurisdiccion = id;
                    dashboardState.filtros.jurisdiccion_id = id_num; // GUARDAMOS EL ID
                    endpoint = 'get_programas_by_jurisdiccion'; 
                    campo_agrupacion = 'programa';
                } else if (nivel_actual === 2) { 
                    dashboardState.filtros.programa = id;
                    dashboardState.filtros.programa_id = id_num; // GUARDAMOS EL ID
                    endpoint = 'get_proyectos_by_programa'; 
                    campo_agrupacion = 'proyecto';
                } else if (nivel_actual === 3) {
                    dashboardState.filtros.proyecto = id;
                    dashboardState.filtros.proyecto_id = id_num; // GUARDAMOS EL ID
                    endpoint = 'get_dependencias_by_proyecto'; 
                    campo_agrupacion = 'dependencia';
                }
            } 
            // --- LÓGICA DE DRILL-DOWN PARA PROVEEDORES (No implementada aquí) ---
            else if (dashboardState.vista === 'proveedores') {
                if (nivel_actual === 1) {
                    dashboardState.filtros.proveedor = id;
                    endpoint = 'jurisdicciones_by_proveedor'; 
                    campo_agrupacion = 'jurisdiccion';
                }
            }

            // Continuar si se encontró un endpoint
            if (endpoint) {
                // Guardar el estado anterior (filtros y IDs) y avanzar
                dashboardState.history.push(JSON.parse(JSON.stringify(dashboardState.filtros)));
                dashboardState.nivel = nivel_actual + 1;
                fetchDrillDownData(endpoint, campo_agrupacion, 'drill-down-content');
            }
        });
    }

    /**
     * Función genérica para manejar la llamada AJAX de drill-down.
     */
    function fetchDrillDownData(endpoint, agrupacion, containerId) {
        // Aseguramos que los filtros de fecha y proveedores se incluyan
        var data_to_send = {
            // Filtros de Nombres
            jurisdiccion: dashboardState.filtros.jurisdiccion,
            programa: dashboardState.filtros.programa,
            proyecto: dashboardState.filtros.proyecto,
            dependencia: dashboardState.filtros.dependencia,
            
            // NUEVO: IDs NUMÉRICOS
            jurisdiccion_id: dashboardState.filtros.jurisdiccion_id,
            programa_id: dashboardState.filtros.programa_id,
            proyecto_id: dashboardState.filtros.proyecto_id,
            
            // Filtros de Proveedor y Período
            proveedor_seleccionado: JSON.stringify(dashboardState.filtros.proveedor ? [dashboardState.filtros.proveedor] : []),
            periodos: JSON.stringify(dashboardState.periodos)
        };
        // 📢 NUEVA LÍNEA DE DEBUGGING EN CONSOLA DEL NAVEGADOR
    console.log("AJAX Payload (fetchDrillDownData):", data_to_send);
        
        $.ajax({
            url: BASE_URL + endpoint,
            type: 'POST',
            dataType: 'json',
            data: data_to_send, // Este objeto ahora incluye todos los IDs
            success: function(response) {
                if (response.error) {
                    alert('Error en el servidor: ' + response.error);
                    return;
                }
                renderCards(response, containerId, agrupacion);
                updateUI();
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX en " + endpoint + ":", xhr.responseText);
                alert("Error al cargar el desglose. Consulte la consola.");
            }
        });
    }

    // 5. MANEJO DEL BOTÓN VOLVER (BACK) 
    $('#btn-back').on('click', function() {
        if (dashboardState.nivel > 1) {
            dashboardState.nivel--;
            
            // Restaurar el estado anterior
            var previous_filters = dashboardState.history.pop(); 
            
            if (previous_filters) {
                 dashboardState.filtros = previous_filters;
            } else {
                 resetFilters(); // Reinicia todos los filtros si el historial está vacío
            }

            // Recargar los datos del nivel anterior
            if (dashboardState.nivel === 1) {
                cargarDashboardNivel1(); 
            } else if (dashboardState.nivel === 2) {
                var endpoint = (dashboardState.vista === 'jurisdicciones') ? 'get_programas_by_jurisdiccion' : 'jurisdicciones_by_proveedor';
                var agrupacion = (dashboardState.vista === 'jurisdicciones') ? 'programa' : 'jurisdiccion';
                fetchDrillDownData(endpoint, agrupacion, 'drill-down-content');
            } else if (dashboardState.nivel === 3) {
                 fetchDrillDownData('get_proyectos_by_programa', 'proyecto', 'drill-down-content');
            } else if (dashboardState.nivel === 4) {
                 fetchDrillDownData('get_dependencias_by_proyecto', 'dependencia', 'drill-down-content'); // Nivel 4 (Dependencias)
            }
            
            updateUI(); 
        }
    });

    /**
     * Función para manejar el cambio entre la vista Jurisdicciones y Proveedores.
     */
    function handleSwitchView(newView) {
        if (dashboardState.vista === newView) {
            return; 
        }
        
        // 1. Actualizar el estado y limpiar filtros de drill-down (incluyendo IDs)
        dashboardState.vista = newView;
        dashboardState.nivel = 1;
        resetFilters(); // Limpieza completa de filtros y IDs
        dashboardState.history = [];
        
        // 2. Actualizar la apariencia del switch
        $('#switch-jurisdicciones').removeClass('active');
        $('#switch-proveedores').removeClass('active');
        $('#switch-' + newView).addClass('active');

        // 3. Llamada AJAX para obtener los datos del Nivel 1 de la nueva vista
        cargarDashboardNivel1(); 
    }

    // 6. INICIALIZACIÓN
    $(document).ready(function() {
        cargarPeriodosDisponibles(); 
        
        $('#vista-switch').on('click', 'button', function() {
            var newView = $(this).data('vista');
            handleSwitchView(newView);
        });
        
        updateUI(); 
    });
    
</script>