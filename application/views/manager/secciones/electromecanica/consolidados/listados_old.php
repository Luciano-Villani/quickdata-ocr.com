
<style>
	.my-class-section {
		color: #000;

	}

	.select2.select2-container {
		width: 100% !important;
	}


	.select2-container {
		width: 100% !important;
	}

	.select2-search:after {
		content: '' !important;
	}

	.card {
		container {
 		 padding-left: 0 !important;
 		 padding-right: 0 !important;
		  width: 100% !important;
	}
	}
	.tablas {
		width: 1300px !important;
		margin-left: -92px!important;
		

	}
	.custom-select {
    height: 4px; /* Ajusta la altura según sea necesario */
    line-height: 40px; /* Asegura que el texto esté centrado verticalmente */
	}
	.excel {
		width: 160px !important;
		margin-left: 290px!important;
		margin-top: 90px!important;
	}
	.tablasH {
		width: 1300px !important;
		margin-left: -92px!important;
		height: 300px !important;
	}	

	#provider-chart {
    width: 300px; /* O el ancho que necesites */
    height: 80px; /* Altura fija */
	}
	.btn-collapse {
        background-color: #C9D4E6; /* Color de fondo */
        border: none; /* Sin borde */
        color: #000; /* Color del texto */
        padding: 10px 15px; /* Espaciado interno */
        display: flex; /* Para alinear íconos y texto */
        align-items: center; /* Centrar verticalmente */
        cursor: pointer; /* Cambiar el cursor al pasar sobre el botón */
        transition: background-color 0.3s ease; /* Efecto de transición para el color */
		height: 20px !important; /* Altura del botón */
    }

    .btn-collapse:hover {
        background-color: #AAB6C1; /* Color de fondo al pasar el mouse */
    }

    .arrow {
        transition: transform 0.3s ease; /* Animación al girar */
        margin: 0 5px; /* Espaciado entre flechas y texto */
    }
    .btn-totales {
        background-color: #344F6E; /* Color de fondo */
        color: #ffffff; /* Color del texto */
    } 
       
.dt-button-collection {
  background-color: #f8f9fa; /* Color de fondo */
  border: 1px solid #ced4da; /* Borde */
  border-radius: 5px; /* Esquinas redondeadas */
  padding: 10px; /* Espaciado interno */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Sombra */
}

.dt-button-collection button {
  background-color: #007bff; /* Color de fondo */
  color: white; /* Color del texto */
  border: none; /* Sin borde */
  border-radius: 3px; /* Esquinas redondeadas */
  padding: 5px 10px; /* Espaciado interno */
  cursor: pointer; /* Cambia el cursor al pasar el mouse */
  transition: background-color 0.3s; /* Transición suave */
}

.custom-colvis-menu {
    background-color: #f8f9fa; /* Color de fondo */
    border: 1px solid #ced4da; /* Borde */
    border-radius: 5px; /* Esquinas redondeadas */
    padding: 10px; /* Espaciado interno */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Sombra */
    display: flex;
    flex-wrap: wrap; /* Permite que los botones fluyan a la siguiente fila */
    max-width: 900px; /* Ancho máximo del menú */
    
}


.custom-colvis-menu button {
    display: flex; /* Habilita flexbox */
    justify-content: center; /* Centra el contenido horizontalmente */
    align-items: center; /* Centra el contenido verticalmente */
    flex: 1 1 calc(20% - 10px); /* Dos botones por fila */
    margin: 5px; /* Espaciado entre botones */
    white-space: normal; /* Permitir texto en múltiples líneas */
    min-width: 70px; /* Ancho mínimo */
    background-color: #113966; /* Color de fondo */
    color: white; /* Color del texto */
    border: none; /* Sin borde */
    border-radius: 3px; /* Esquinas redondeadas */
    padding: 5px 10px; /* Espaciado interno */
    cursor: pointer; /* Cambia el cursor al pasar el mouse */
    transition: background-color 0.3s; /* Transición suave */
}

.custom-colvis-menu button:hover {
    background-color: #9DA8B3; /* Color al pasar el mouse */
}
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: auto; /* O puedes especificar un ancho */
    background-color: rgba(0, 0, 0, 0.75);
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    bottom: 125%; /* Posición de la tooltip */
    left: 50%;
    margin-left: -60px; /* Ajuste de la posición */
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}

	

</style>

<div class="card tablas" style="margin-top: -15px;">
<button type="button" data-toggle="collapse" data-target="#collapseFilters" class="btn btn-collapse">
    <i class="icon-arrow-down5 arrow" id="arrow-icon"></i>Filtros y reportes</button>
	

    
    <div class="collapse" id="collapseFilters">
        <div class="card-body" style="height: 150px;">
            <div class="row">

                <div class="col-3">
                    <div id="provider-chart"></div>
                    <div style="margin: 10px;">
					<button type="button" id="btn_totales_por_mes" class="btn btn-secondary">Totales por Mes</button>
                    <button type="button" id="btn_totales_por_tarifa" class="btn btn-secondary">Totales por Tarifa</button>
                    </div>
                </div>

                                <!-- Contenedor para el mensaje de procesamiento -->
                <div id="processing-message" style="display: none; margin: 10px;">
                    <p>Calculando Totales por mes... Por favor espere.</p>
                </div>

    <label class="col-1" for="id_proveedor">
    <?php
    // Preparar opciones para el dropdown
    $proveedores_dropdown = [];
    foreach ($select_proveedores as $id => $proveedor) {
        // Verificamos si $proveedor es un array y tomamos el nombre
        $proveedor_name = is_array($proveedor) ? $proveedor['nombre'] : $proveedor;
        
        // Eliminar el prefijo "EDENOR-" o "EDENOR -" (con espacio adicional o sin él)
        $proveedor_name = preg_replace('/^EDENOR[\s-]+/', '', $proveedor_name);  // Usamos expresión regular para eliminar "EDENOR-" y los espacios

        // Eliminar caracteres invisibles o espacios adicionales
        $proveedor_name = trim($proveedor_name);  // Eliminar espacios en los extremos

        // Asignar el nombre limpio (sin "EDENOR-" o "EDENOR -")
        $proveedores_dropdown[$id] = $proveedor_name;
    }

    // Renderizar el dropdown
    $js = array(
        'id' => 'id_proveedor',
        'class' => 'custom-select',
        'multiple' => "multiple",
    );
    echo form_dropdown('id_proveedor', $proveedores_dropdown, '', $js); // Dejar vacío el valor por defecto
    ?>
    <script>
        $(document).ready(function() {
            $('#id_proveedor').select2({
                placeholder: 'TARIFA',  // Definir el placeholder
                minimumResultsForSearch: "-1",
                width: '100%',
                closeOnSelect: true,
            }).trigger('change');  // Forzar el cambio después de la inicialización
        });
    </script>
</label>



<!-- Filtro de Mes FC -->
<label class="col-1" for="id_mes_fc">
    <?php
    $meses_fc = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
        '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
        '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
        '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    ];
    $js = ['id' => 'id_mes_fc', 'class' => 'form-control', 'multiple' => 'multiple'];
    echo form_dropdown('id_mes_fc[]', $meses_fc, '', $js); // Dejar vacío el valor por defecto
    ?>
    <script>
        $(document).ready(function() {
            $('#id_mes_fc').select2({
                placeholder: 'Mes FC',  // Definir el placeholder
                minimumResultsForSearch: "-1",
                width: '100%',
                closeOnSelect: false,
            });

            // Forzar la visualización del placeholder al cargar la página
            $('#id_mes_fc').trigger('change');  // Esto es para asegurar que el placeholder sea visible
        });
    </script>
</label>


                <!-- Filtro de Año FC -->
                <label class="col-1" for="anio_fc">
                <?php
                // Opciones de años
                $anios_dropdown = ['' => 'Selecciona un año']; // Esta opción actúa como el placeholder
                foreach ($select_anios as $anio) {
                    $anios_dropdown[$anio] = $anio;
                }
                $js = ['id' => 'id_anio_fc', 'class' => 'form-control'];
                echo form_dropdown('anio_fc', $anios_dropdown, '', $js);
                ?>
                </label>

                <div class="col-2">
                    <label><input type="checkbox" id="cosfi_filter" value="true"> Cos Fi inferiores a 0.95</label>
                    <label><input type="checkbox" id="cons_filter" value="true"> Cosumo en 0 para T1/T2</label>
                    <label><input type="checkbox" id="const3_filter" value="true"> Cosumo en 0 para T3</label>
                    <label><input type="checkbox" id="cuentas_unicas_filter" value="true"> Altas / Bajas</label>
                    <label><input type="checkbox" id="comentarios_filter" value="true"> Cuentas con seguimiento</label>

                    
                </div>

                <div class="col-2" style="margin: -10px;">
                    <label><input type="checkbox" class="radio" value="1" name="tipo_fecha" id="tipo-fecha" /> <span data-popup="tooltip">Fecha de Consolidación</span></label>
                    <div class="col" style="margin: -10px;">
                        <input type="text" name="daterange2" id="daterange2" class="form-control">
                    </div>
                </div>

                <div class="col-2">
                    <button id="applyfilter" type="button" class="btn btn-outline-dark btn-sm" title="Aplicar filtros"><b><i class="icon-filter3"></i></b></button>
                    <button id="resetfilter" type="button" class="btn btn-outline-dark btn-sm" title="Eliminar filtros"><b><i class="icon-reset"></i></b></button>
                    <button id="descarga-exell" type="button" class="btn btn-outline-excel btn-sm" title="Descargar reporte"><b><i class="icon-file-excel"></i></b></button>
                </div>

            </div>
        </div>
    </div>
</div>


<style>
	#consolidados_dt_filter,
	#consolidados_dt_length {
		/* float: left; */
	}

	.dataTables_filter input{
		text-transform: uppercase;
	}
	div.dt-button-collection {
		width: auto !important;

	}

	div[role='menu'] {
		display: flex !important;
		width: auto !important;
		
	}
	div.dataTables_wrapper {
    /* width: 1200px !important; */
       
    }
</style>
<div class="card tablas" style="margin-top: -15px">
<h5 class="card-title bg-titulo text-center text-dark"> Facturas Consolidadas Electromecánica</h5>

<div id="loading-spinner" style="display:none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
  <div class="spinner-border text-primary" role="status">
    <span class="sr-only">Loading...</span>
  </div>
</div>

<div class="card-header" style="margin-top: -15px">
<div id="consulta"></div>
<div id="request"></div>

	
		<table id="consolidados_dt" class="datatable-ajax table-bordered table-hover datatable-highlight" style="width: auto">
			
			<thead>
				
				<tr>
				
                <th>Proveedor</th>                          <!-- 0 -->
                <th>Medidor</th>                            <!-- 1 -->
                <th>Categoría</th>                          <!-- 2 -->
                <th>Tensión</th>                            <!-- 3 -->
                <th>Nro Factura</th>                        <!-- 4 -->
                <th>Nro Cuenta</th>                         <!-- 5 -->
                <th>Medidor</th>                            <!-- 6 -->
                <th>Dependencia</th>                        <!-- 7 -->
                <th>Dirección de Consumo</th>               <!-- 8 -->
                <th>Nombre Cliente</th>                     <!-- 9 -->
                <th>Cons kWh/kW"</th>                       <!-- 10 -->
                <th>Cosfi</th>                              <!-- 11 -->
                <th>Tgfi</th>                               <!-- 12 -->
                <th>E Activa kWh</th>                       <!-- 13 -->
                <th>E Reactiva kVArh</th>                   <!-- 14 -->
                <th>P Registrada</th>                       <!-- 15 -->
                <th>Importe $</th>                          <!-- 16 -->
                <th>Vencimiento</th>                        <!-- 17 -->
                <th>Impuestos $</th>                        <!-- 18 -->
                <th>Bimestre</th>                           <!-- 19 -->
                <th>Liquidación</th>                        <!-- 20 -->
                <th>P Contr Kw</th>                         <!-- 21 -->
                <th>Cargo Fijo $</th>                       <!-- 22 -->
                <th>Cargo Var $</th>                        <!-- 23 -->
                <th>Cargo Var > $</th>                      <!-- 24 -->
                <th>Otros Conceptos $</th>                  <!-- 25 -->
                <th>Conceptos Eléctricos $</th>             <!-- 26 -->
                <th>P Excedida</th>                         <!-- 27 -->
                <th>Pot Punta</th>                          <!-- 28 -->
                <th>Pot Fuera Punta</th>                    <!-- 29 -->
                <th>Energía Punta</th>                      <!-- 30 -->
                <th>Energía Resto</th>                      <!-- 31 -->
                <th>Energía Valle</th>                      <!-- 32 -->
                <th>Energía Reac Act</th>                   <!-- 33 -->
                <th>Cargo Pot Contratada $</th>             <!-- 34 -->
                <th>Cargo Pot Ad $</th>                     <!-- 35 -->
                <th>Cargo Pot Excedida $</th>              <!-- 36 -->
                <th>Recargo TGFI $</th>                     <!-- 37 -->
                <th>Cons.Pico Vigente</th>               <!-- 38 -->
                <th>Cargo Pico $</th>                       <!-- 39 -->
                <th>Cons.Resto Vigente</th>              <!-- 40 -->
                <th>Cargo Resto $</th>                      <!-- 41 -->
                <th>Cons.Valle Vigente</th>              <!-- 42 -->
                <th>Cargo Valle $</th>                      <!-- 43 -->
                <th>E Actual</th>                           <!-- 44 -->
                <th>Cargo Contratado $</th>                 <!-- 45 -->
                <th>Cargo Adquirido $</th>                  <!-- 46 -->
                <th>Cargo Excedente $</th>                  <!-- 47 -->
                <th>Cargo Variable $</th>                   <!-- 48 -->
                <th>Total Vencido $</th>                    <!-- 49 -->
                <th>E Reactiva kVArh</th>                   <!-- 50 -->
                <th>U.Med</th>                              <!-- 51 -->
                <th>Días Cons</th>                          <!-- 52 -->
                <th>Días Comp</th>                          <!-- 53 -->
                <th>Cons DC kWh</th>                        <!-- 54 -->
                <th>Período Consumo</th>                    <!-- 55 -->
                <th>Mes Fc</th>                             <!-- 56 -->
                <th>Año Fc</th>                             <!-- 57 -->
                <th>Subsidio</th>                           <!-- 58 -->
                <th>Car Var Hasta kw</th>                   <!-- 59 -->
                <th>Cons.Pico Anterior</th>              <!-- 60 -->
                <th>Cons.Resto Anterior</th>             <!-- 61 -->
                <th>Cons.Valle Anterior</th>             <!-- 62 -->
                <th>Energía Inyectada</th>                  <!-- 63 -->
                <th>Cargo Cant</th>                        <!-- 64 -->

                <th>Acc.</th>                               <!-- 65 -->
                                 
					
				</tr>
                


            
			</thead>
			<!--<tfoot>
				<tr>
					
				</tr>
			</tfoot> -->

		</table>

	</div>
</div>

<script src="https://fastly.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.11/lodash.min.js"></script>

<script>
$(document).ready(function () {
    var myChart = echarts.init(document.getElementById('provider-chart'));
    
    var option = {
        title: {
            text: '',
            left: 'center',
            textStyle: { fontSize: 14 }
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
        },
        xAxis: {
            type: 'category',
            data: [],
            axisLabel: { rotate: 30 }
        },
        yAxis: {
            type: 'value',
            axisLabel: { show: false },
            axisLine: { show: true },
            axisTick: { show: true },
            splitLine: { show: true }
        },
        series: [{
            name: 'Cantidad',
            type: 'bar',
            data: [],
            itemStyle: { color: '#0C2847' }
        }]
    };

    myChart.setOption(option);

    var mostrarPorMes = false;

    // Función para obtener los filtros seleccionados
    function obtenerFiltros() {
        // Capturamos los valores de los filtros de la interfaz (multiple select)
        var filtros = {
            proveedor: $('#id_proveedor').val(),  // Filtro de proveedores (múltiple)
            meses: $('#id_mes_fc').val(),         // Filtro de meses (múltiple)
            anio: $('#id_anio_fc').val()          // Filtro de año
        };

        return filtros;
    }

    function actualizarGrafico() {
    var filtros = obtenerFiltros(); // Obtener los filtros actuales

    $.ajax({
        url: "/Electromecanica/Consolidados/obtener_datos_grafico", 
        type: "POST",
        dataType: "json",
        data: {
            agrupar_por_mes: mostrarPorMes, 
            filtros: filtros  // Enviar los filtros junto con la solicitud
        },
        success: function (respuesta) {
            var categorias = [];
            var cantidades = [];

            respuesta.forEach(function (item) {
                var categoria = mostrarPorMes ? item.mes : item.id_proveedor;

                // Si estamos mostrando por proveedor (mostrarPorMes = false)
                if (!mostrarPorMes) {
                    // Solo aplicar los reemplazos a los proveedores
                    if (categoria == 1 || categoria == 2 || categoria == 3) {
                        categoria = 'T' + categoria;  // Agregar 'T' a 1, 2, 3
                    } else if (categoria == 5) {
                        categoria = 'AP';  // Reemplazar 5 por 'AP'
                    }
                }

                categorias.push(categoria);
                cantidades.push(item.cantidad);
            });

            myChart.setOption({
                xAxis: { data: categorias },
                series: [{ data: cantidades }]
            });
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener los datos del gráfico:', error);
        }
    });
}

    function actualizarBotones() {
        if (mostrarPorMes) {
            $('#btn_totales_por_mes').addClass('btn-totales').removeClass('btn-secondary');
            $('#btn_totales_por_tarifa').removeClass('btn-totales').addClass('btn-secondary');
        } else {
            $('#btn_totales_por_tarifa').addClass('btn-totales').removeClass('btn-secondary');
            $('#btn_totales_por_mes').removeClass('btn-totales').addClass('btn-secondary');
        }

        actualizarGrafico(); // Actualizar el gráfico directamente
    }

    $('#btn_totales_por_mes').click(function () {
        mostrarPorMes = true;
        actualizarBotones();
    });

    $('#btn_totales_por_tarifa').click(function () {
        mostrarPorMes = false;
        actualizarBotones();
    });

    $('#applyfilter').click(function () {
        // Llamar al actualizar gráfico con un retraso de 1 segundo (1000 ms)
        setTimeout(function() {
            actualizarGrafico();  // Actualizar el gráfico después de un pequeño delay
        }, 1000);  // Puedes ajustar este tiempo según tus necesidades
    });

    $('#resetfilter').click(function () {
        // Restablecer los valores de los filtros
        $('#id_proveedor').val([]).trigger('change');  // Restablecer el filtro de proveedores
        $('#id_mes_fc').val([]).trigger('change');     // Restablecer el filtro de meses
        $('#id_anio_fc').val('').trigger('change');    // Restablecer el filtro de año
        
        // Llamar al actualizar gráfico con un retraso de 1 segundo (1000 ms)
        setTimeout(function() {
            actualizarGrafico();  // Actualizar el gráfico después de un pequeño delay
        }, 1000);  // Puedes ajustar este tiempo según tus necesidades
    });

    // Inicialización de los filtros Select2
    $('#id_proveedor').select2({
        placeholder: 'TARIFA',
        minimumResultsForSearch: "-1",
        width: '100%',
        closeOnSelect: true
    });

    $('#id_mes_fc').select2({
        placeholder: 'Mes FC',
        minimumResultsForSearch: "-1",
        width: '100%',
        closeOnSelect: false
    });

    $('#id_anio_fc').select2({
        placeholder: 'Selecciona un año',
        minimumResultsForSearch: "-1",
        width: '100%',
        closeOnSelect: true
    });

    // Cuando el colapsable de filtros se muestra, volver a inicializar Select2 para asegurar que los placeholders estén visibles
    $('#collapseFilters').on('shown.bs.collapse', function () {
        $('#id_proveedor').select2({
            placeholder: 'TARIFA',
            minimumResultsForSearch: "-1",
            width: '100%',
            closeOnSelect: true
        });

        $('#id_mes_fc').select2({
            placeholder: 'Mes',
            minimumResultsForSearch: "-1",
            width: '100%',
            closeOnSelect: false
        });

        $('#id_anio_fc').select2({
            placeholder: 'Año',
            minimumResultsForSearch: "-1",
            width: '100%',
            closeOnSelect: true
        });
    });

    actualizarBotones(); // Llamar la primera vez al cargar la página
    actualizarGrafico(); // Llamar la primera vez al cargar el gráfico
});
</script>





