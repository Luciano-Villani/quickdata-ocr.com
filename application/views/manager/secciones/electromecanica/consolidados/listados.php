
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


	

</style>

<div class="card tablas" style="margin-top: -15px;">
<button type="button" data-toggle="collapse" data-target="#collapseFilters" class="btn btn-collapse">
    <i class="icon-arrow-down5 arrow" id="arrow-icon"></i>Filtros y reportes</button>
	

    
    <div class="collapse" id="collapseFilters">
        <div class="card-body" style="height: 150px;">
            <div class="row">

                <div class="col-3">
                    <div id="provider-chart"></div>
					<label style="margin: 25px;"><input type="checkbox" id="totales_por_mes" value="true">Totales por mes</label>
                    <label><input type="checkbox" id="totales_por_tarifa" value="true">Totales por tarifa</label>
                </div>

                <label class="col-1" for="id_proveedor">
                    <?php
                    // Preparar opciones para el dropdown
                    $proveedores_dropdown = [];
                    foreach ($select_proveedores as $id => $proveedor) {
                        $proveedores_dropdown[$id] = is_array($proveedor) ? $proveedor['nombre'] : $proveedor;
                    }

                    // Renderizar el dropdown
                    $js = array(
                        'id' => 'id_proveedor',
                        'class' => 'custom-select',
                        'multiple' => "multiple",
                    );
                    echo form_dropdown('id_proveedor', $proveedores_dropdown, set_value('id_proveedor'), $js);
                    ?>
                    <script>
                        $('#id_proveedor').select2({
                            placeholder: 'TARIFA',
                            minimumResultsForSearch: "-1",
                            width: '100%',
                            closeOnSelect: true,
                        });
                    </script>
                </label>

                <!-- Filtro de Mes FC -->
                <label class="col-1" for="id_mes_fc">
                    <?php
                    // Opciones de meses
                    $meses_fc = [
                        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
                        '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                        '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
                        '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
                    ];
                    $js = ['id' => 'id_mes_fc', 'class' => 'form-control', 'multiple' => 'multiple'];
                    ?>
                    <?= form_dropdown('id_mes_fc[]', $meses_fc, '', $js); ?>
                    <script>
                        $('#id_mes_fc').select2({
                            placeholder: 'Mes FC',
                            minimumResultsForSearch: "-1",
                            width: '100%',
                            closeOnSelect: false,
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
                    <label><input type="checkbox" id="filtrar_por_cuenta" value="true" disabled> Cuentas únicas</label>
                    
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
				
                <th>Proveedor</th>
                <th>Tarifa</th>
                <th>Nro Factura</th>
                <th>Nro Cuenta</th>
                <th>Medidor</th>
                <th>Dependencia</th>
                <th>Dirección de Consumo</th>
                <th>Nombre Cliente</th>
                <th>Cons kWh/kW"</th>
                <th>Cosfi</th>
                <th>Tgfi</th>
                <th>E Activa kWh</th>                         <!-- 12 -->
                <th>E Reactiva kVArh</th>                         <!-- 54 -->
                <th>Importe $</th>
                <th>Vencimiento</th>
                <th>Impuestos $</th>
                <th>Bimestre</th>
                <th>Liquidación</th>                   <!-- Coincide con target 17 -->
                <th>Cargo Variable Hasta $</th>          <!-- Coincide con target 18 -->
                <th>Cargo Fijo $</th>                    <!-- Coincide con target 19 -->
                <th>Cargo Var $</th>              <!-- Coincide con target 20 -->
                <th>Cargo Var > $</th>                 <!-- Coincide con target 21 -->
                <th>Otros Conceptos $</th>                <!-- Coincide con target 22 -->
                <th>Conceptos Eléctricos $</th>           <!-- Coincide con target 23 -->
                <th>Energía Inyectada</th>              <!-- Coincide con target 24 -->
                <th>Pot Punta</th>                      <!-- Coincide con target 25 -->
                <th>Pot Fuera Punta Cons</th>           <!-- Coincide con target 26 -->
                <th>Energía Punta Act</th>              <!-- Coincide con target 27 -->
                <th>Energía Resto Act</th>              <!-- Coincide con target 28 -->
                <th>Energía Valle Act</th>              <!-- Coincide con target 29 -->
                <th>Energía Reac Act</th>               <!-- Coincide con target 30 -->
                <th>Cargo Pot Contratada $</th>           <!-- Coincide con target 31 -->
                <th>Cargo Pot Ad $</th>                   <!-- Coincide con target 32 -->
                <th>Cargo Pot Excedente $</th>            <!-- Coincide con target 33 -->
                <th>Recargo TGFI $</th>                   <!-- Coincide con target 34 -->
                <th>Consumo Pico Vigente</th>           <!-- Coincide con target 35 -->
                <th>Cargo Pico $</th>                     <!-- Coincide con target 36 -->
                <th>Consumo Resto Vigente</th>          <!-- Coincide con target 37 -->
                <th>Cargo Resto $</th>                    <!-- Coincide con target 38 -->
                <th>Consumo Valle Vigente</th>          <!-- Coincide con target 39 -->
                <th>Cargo Valle $</th>                    <!-- Coincide con target 40 -->
                <th>E Actual</th>                       <!-- Coincide con target 41 -->
                <th>Cargo Contratado $</th>               <!-- Coincide con target 42 -->
                <th>Cargo Adquirido $</th>                <!-- Coincide con target 43 -->
                <th>Cargo Excedente $</th>                <!-- Coincide con target 44 -->
                <th>Cargo Variable $</th>                 <!-- Coincide con target 45 -->
                <th>Total Vencido $</th>                  <!-- Coincide con target 46 -->
                <th>E Reactiva kVArh </th>                    <!-- Coincide con target 47 -->
                <th>U.Med</th>                         <!-- Coincide con target 48 -->
                <th>Días Cons</th>                         <!-- 49 -->
                <th>Días Comp</th>                         <!-- 50 -->
                <th>Cons DC kWh</th>                         <!-- 51 -->
                <th>Período Consumo</th>                         <!-- 52 -->
                <th>Mes Fc</th>                             <!-- 53 -->
                <th>Año Fc</th>                         <!-- 54 -->
                <th>Subsidio</th>                       <!-- 55 -->
                <th></th>                               <!-- Columna vacía -->

					
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


    // Inicializar el gráfico en su contenedor
    var myChart = echarts.init(document.getElementById('provider-chart'));
    var option = {
        title: {
            text: '',
            left: 'center',
            textStyle: {
                fontSize: 14 // Tamaño de tipografía ajustado para el título
            }
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
        },
        xAxis: {
            type: 'category',
            data: [], // Inicialmente vacío
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
            data: [], // Inicialmente vacío
            itemStyle: {
                color: '#0C2847'
            }
        }]
    };
    myChart.setOption(option); // Establecer la opción inicial del gráfico

    // Función para actualizar el gráfico con los datos visibles en la tabla
    function actualizarGrafico() {
        setTimeout(function () {
            var tabla = $('#consolidados_dt').DataTable();
            var datosVisibles = tabla.rows({ filter: 'applied' }).data().toArray();

            console.log("Datos visibles en la tabla:", datosVisibles); // Depuración

            if (datosVisibles.length === 0) {
               // console.warn("No hay datos visibles en la tabla para mostrar en el gráfico.");
                return;
            }

            var conteoProveedoresOMeses = {};
            datosVisibles.forEach(function (item, index) {
                //console.log(`Contenido completo de la fila ${index}:`, item);
                var idProveedor = item[57]; // Ajusta según la columna correcta para el proveedor.
                var mes = item[12]; // Ajusta según la columna correcta para el mes.

                //console.log(`Fila ${index}: idProveedor = ${idProveedor}, mes = ${mes}`);

                if ($('#totales_por_mes').is(':checked')) {
                    if (mes) { 
                        conteoProveedoresOMeses[mes] = (conteoProveedoresOMeses[mes] || 0) + 1;
                    }
                } else {
                    if (idProveedor) { 
                        var idProveedorModificado = `T${idProveedor}`;
                        conteoProveedoresOMeses[idProveedorModificado] = (conteoProveedoresOMeses[idProveedorModificado] || 0) + 1;
                    }
                }
            });

            var categorias = Object.keys(conteoProveedoresOMeses);
            var cantidades = Object.values(conteoProveedoresOMeses);

            categorias.sort(function (a, b) {
                var numA = parseInt(a.replace('T', ''));
                var numB = parseInt(b.replace('T', ''));
                return numA - numB; // Orden ascendente
            });

            var cantidadesOrdenadas = categorias.map(function (categoria) {
                return conteoProveedoresOMeses[categoria];
            });

            //console.log("Categorías (meses/proveedores):", categorias); // Depuración
            console.log("Cantidades ordenadas:", cantidadesOrdenadas); // Depuración

            if (categorias.length === 0 || cantidadesOrdenadas.length === 0) {
                //console.warn("No hay categorías o cantidades disponibles para actualizar el gráfico.");
                return;
            }

            myChart.setOption({
                xAxis: { data: categorias },
                series: [{ data: cantidadesOrdenadas }]
            });
        }, 300); // Tiempo de espera para asegurarse de que la tabla esté lista
    }

    $('#consolidados_dt').on('draw.dt', function () {
        actualizarGrafico(); // Llamar a la función actualizarGrafico después de que se dibuja la tabla
    });

    $('#totales_por_mes').change(function () {
    var tabla = $('#consolidados_dt').DataTable();

    // Si el checkbox 'totales por mes' se selecciona, desmarca 'totales por tarifa'
    if ($(this).is(':checked')) {
        $('#totales_por_tarifa').prop('checked', false);
        tabla.page.len(-1).draw(); // Muestra todas las filas
    } else {
        tabla.page.len(50).draw(); // Vuelve al paginado de 50 filas
    }

    actualizarGrafico(); // Actualiza el gráfico después del cambio
});

$('#totales_por_tarifa').change(function () {
    var tabla = $('#consolidados_dt').DataTable();

    // Si el checkbox 'totales por tarifa' se selecciona, desmarca 'totales por mes'
    if ($(this).is(':checked')) {
        $('#totales_por_mes').prop('checked', false);
        tabla.page.len(-1).draw(); // Muestra todas las filas
    } else {
        tabla.page.len(50).draw(); // Vuelve al paginado de 50 filas
    }

    actualizarGrafico(); // Actualiza el gráfico después del cambio
});

    

   

    // Lógica de filtrado y estado del checkbox
    var $filtrarPorCuenta = $('#filtrar_por_cuenta');
    var $selectMesFc = $('#id_mes_fc');

    $filtrarPorCuenta.prop('disabled', true);

    function actualizarEstadoCheckbox() {
        var mesesSeleccionados = $selectMesFc.val();
        $filtrarPorCuenta.prop('disabled', mesesSeleccionados && mesesSeleccionados.length < 12);
        if ($filtrarPorCuenta.prop('disabled')) {
            $filtrarPorCuenta.prop('checked', false);
        }
    }

    $selectMesFc.change(function () {
        actualizarEstadoCheckbox();
    });

    function filtrarPorCuenta() {
    var tabla = $('#consolidados_dt').DataTable();
    var mesesSeleccionados = $selectMesFc.val(); // Meses seleccionados

    if (!mesesSeleccionados || mesesSeleccionados.length < 2) {
       // console.warn("Debe seleccionar al menos dos meses para usar el filtro por cuenta.");
        return;
    }

    var cuentasPorMes = {};
    var datosVisibles = tabla.rows({ filter: 'applied' }).data().toArray();

    // Agrupar cuentas por mes
    datosVisibles.forEach(function (item) {
        var mes = item[12]; // Columna correcta para el mes
        var cuenta = item[2]; // Columna correcta para la cuenta

        if (mesesSeleccionados.includes(mes)) {
            cuentasPorMes[cuenta] = cuentasPorMes[cuenta] || [];
            cuentasPorMes[cuenta].push(mes);
        }
    });

    // Filtrar cuentas que solo aparecen en un mes
    var cuentasFiltradas = Object.keys(cuentasPorMes).filter(function (cuenta) {
        return cuentasPorMes[cuenta].length === 1; // Aparece en solo un mes
    });

    //console.log("Cuentas filtradas:", cuentasFiltradas); // Depuración

    // Iterar sobre las filas y aplicar el filtrado
    tabla.rows().every(function () {
        var cuenta = this.data()[2]; // Columna correcta para la cuenta

        // Si el checkbox está marcado, ocultar las cuentas filtradas
        if ($filtrarPorCuenta.is(':checked')) {
            var shouldShow = cuentasFiltradas.includes(cuenta);
            this.nodes().to$().toggle(shouldShow); // Mostrar u ocultar según el filtro
            //console.log(`Cuenta: ${cuenta}, Mostrar: ${shouldShow}`); // Depuración
        } else {
            this.nodes().to$().show(); // Mostrar todas las filas si el filtro está desactivado
        }
    });

    tabla.draw(); // Asegúrate de redibujar la tabla
}


    $filtrarPorCuenta.change(function () {
        filtrarPorCuenta();
    });

    $selectMesFc.change(function () {
        if ($filtrarPorCuenta.is(':checked')) {
            filtrarPorCuenta();
        }
    });

    actualizarEstadoCheckbox();
    actualizarGrafico(); // Llamar a la función inicial
});


// Manejo de los íconos de colapso
$('#collapseFilters').on('shown.bs.collapse', function () {
    $('#arrow-icon').removeClass('icon-arrow-down5').addClass('icon-arrow-up5');
    
    // Reinicializa los select2 al expandir el colapsable
    $('#id_proveedor').select2({
        placeholder: "Tarifa",
        // otras configuraciones si es necesario
    });
// Si tienes otros select2, reinicialízalos aquí
$('#id_mes_fc').select2({
        placeholder: "Mes FC",
        // otras configuraciones si es necesario
    });
    // Si tienes otros select2, reinicialízalos aquí
    $('#id_anio_fc').select2({
        placeholder: "Año",
        
        // otras configuraciones si es necesario
    });
    
   



});
console.log($('#id_anio_fc').length); // Debería ser 1 si el elemento existe

// Evento para manejar el colapso oculto
$('#collapseFilters').on('hidden.bs.collapse', function () {
    $('#arrow-icon').removeClass('icon-arrow-up5').addClass('icon-arrow-down5');
});


// **Fin del script**

</script>

