
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
		height: 140px !important;

	}

</style>

<div class="card tablasH" style="margin-top: -15px">
<h5 class="card-title bg-titulo text-center text-dark"> Filtros y Descarga de Reportes Electromecánica </h5>

	<div class="card-header">
		<div class="row" style="margin-top: -10px";>

		<div class="col-2">
		<div id="provider-chart" style="width: 100%; height: 75px;"></div>
		</div>


		<label class="col-2" for="id_proveedor">

				<?php
				// Preparar opciones para el dropdown
				$proveedores_dropdown = [];

				foreach ($select_proveedores as $id => $proveedor) {
					if (is_array($proveedor)) {
						// Usar solo el nombre para el dropdown
						$proveedores_dropdown[$id] = $proveedor['nombre'];
					} else {
						$proveedores_dropdown[$id] = $proveedor;
					}
				}

				// Opciones adicionales para el dropdown
				$js = array(
					'id' => 'id_proveedor',
					'class' => 'custom-select',
					'multiple' => "multiple",
				);

				// Renderizar el dropdown
				echo form_dropdown('id_proveedor', $proveedores_dropdown, set_value('id_proveedor'), $js);
				?>

				<script>
					$('#id_proveedor').select2({
						placeholder: 'PROVEEDORES',
						minimumResultsForSearch: "-1",
						width: '100%',
						closeOnSelect: false,
						selectionCssClass: '',
					});
				</script>
			</label>
		
			
			

				<!-- Filtro de Mes FC -->
			<label class="col-1" for="id_mes_fc">
				<?php
				// Definimos las opciones del filtro con los meses.
				$meses_fc = array(
					'' => 'Seleccionar Mes', // Opción por defecto.
					'01' => 'Enero',
					'02' => 'Febrero',
					'03' => 'Marzo',
					'04' => 'Abril',
					'05' => 'Mayo',
					'06' => 'Junio',
					'07' => 'Julio',
					'08' => 'Agosto',
					'09' => 'Septiembre',
					'10' => 'Octubre',
					'11' => 'Noviembre',
					'12' => 'Diciembre',
				);

				// Creamos el select para `mes_fc` con las opciones de los meses.
				$js = array(
					'id' => 'id_mes_fc',
					'class' => 'form-control',
				);
				?>
				<?= form_dropdown('id_mes_fc', $meses_fc, '', $js); ?>

				<!-- Script para inicializar el dropdown con select2 -->
				<script>
					$('#id_mes_fc').select2({
						placeholder: 'MES FC',
						minimumResultsForSearch: "-1", // Deshabilitar la búsqueda.
						width: '100%',
						closeOnSelect: true,
					});
				</script>
			</label>

					<!-- Filtro de Año FC -->
			<label class="col-1" for="anio_fc">
				<?php
				// Preparar opciones para el dropdown
				$anios_dropdown = ['']; // Opción predeterminada

				foreach ($select_anios as $anio) {
					$anios_dropdown[$anio] = $anio; // Suponiendo que $anio ya contiene los años
				}

				// Opciones adicionales para el dropdown
				$js = array(
					'id' => 'id_anio_fc',
					'class' => 'form-control',
				);

				// Renderizar el dropdown sin selección predeterminada
				echo form_dropdown('anio_fc', $anios_dropdown, '', $js);
				?>
				<script>
					$(document).ready(function() {
						// Inicializar el dropdown y establecer su valor a null al cargar la página
						$('#id_anio_fc').val(null).trigger('change');

						// Inicializar Select2 
						$('#id_anio_fc').select2({
							placeholder: 'AÑO FC',
							minimumResultsForSearch: "-1",
							width: '100%',
							closeOnSelect: true,
						});

						// Forzar que el valor del dropdown sea nulo al cargar la página
						$('#id_anio_fc').val(null).trigger('change');
					});
				</script>
			</label>


			<div class="col-2 ">
			<label class="">
			<input type="checkbox" id="cosfi_filter" value="true">
			Cos Fi inferiores a 0.95
			</label>
			<label class="">
			<input type="checkbox" id="tgfi_filter" value="true">
			Tg Fi mayores a 0.33
			</label>



			</div>


			<div class="col-2" style = "margin: -10px";>
				<label class="">
					<input type="checkbox"  class="radio"  value="1" name="tipo_fecha"  id="tipo-fecha" />
					<span data-popup="tooltip">Fecha de Consolidación</span>
				</label>
				<div class="col" style = "margin: -10px";>
					<input type="text" name="daterange2" id="daterange2" class="form-control ">
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

<div class="card-header" style="margin-top: -15px">
<div id="consulta"></div>
<div id="request"></div>
	
		<table id="consolidados_dt" class="datatable-ajax table-bordered table-hover datatable-highlight" style="width: auto">
			
			<thead>
				
				<tr>
				
					<th>Proveedor</th>
                    <th>Nro Factura</th>
                    <th>Nro Cuenta</th>
                    <th>Nro Medidor</th>
                    <th>Dependencia</th>
                    <th>Dirección de Consumo</th>
                    <th>Nombre Cliente</th>
                    <th>Consumo</th>
                    <th>U. Med</th>
                    <th>Cosfi</th>
                    <th>Tgfi</th>
                    <th>Importe Total</th>
                    <th>Mes Fc</th>
                    <th>Año Fc</th>
                    <th>Vencimiento</th>
					<th></th>
					
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
                console.warn("No hay datos visibles en la tabla para mostrar en el gráfico.");
                return;
            }

            // Crear un objeto para contar la cantidad de registros por proveedor
            var conteoProveedores = {};

            datosVisibles.forEach(function (item) {
                // Asegúrate de que `item[15]` es la posición correcta del `id_proveedor`.
                var idProveedor = item[16]; // Asumiendo que `id_proveedor` está en la columna 15.

                // Agregar la letra "T" al idProveedor para mostrar en el gráfico
                var idProveedorModificado = `T${idProveedor}`;

                // Verificar si ya existe el proveedor en el objeto de conteo y sumar
                if (conteoProveedores[idProveedorModificado]) {
                    conteoProveedores[idProveedorModificado] += 1; // Aumentar el conteo de registros
                } else {
                    conteoProveedores[idProveedorModificado] = 1; // Inicializar el conteo
                }
            });

            // Convertir el objeto en arrays de proveedores y sus cantidades
            var proveedores = Object.keys(conteoProveedores);
            var cantidades = Object.values(conteoProveedores);

            console.log("Proveedores antes de ordenar:", proveedores); // Depuración

            // Ordenar los proveedores en base a su ID numérico (extrayendo el número de la cadena 'T')
            proveedores.sort(function (a, b) {
                var numeroA = parseInt(a.replace('T', ''));
                var numeroB = parseInt(b.replace('T', ''));
                return numeroA - numeroB;
            });

            console.log("Proveedores después de ordenar:", proveedores); // Depuración

            // Ordenar las cantidades en función del nuevo orden de los proveedores
            var cantidadesOrdenadas = proveedores.map(function (proveedor) {
                return conteoProveedores[proveedor];
            });

            console.log("Cantidades después de ordenar:", cantidadesOrdenadas); // Depuración

            // Actualizar las opciones del gráfico con los datos visibles
            myChart.setOption({
                xAxis: { data: proveedores }, // Actualiza los datos del eje X con los proveedores
                series: [{ data: cantidadesOrdenadas }] // Actualiza los datos de la serie con las cantidades (número de registros)
            });
        }, 300); // Tiempo de espera para asegurarse de que la tabla esté lista
    }

    // Escuchar el evento `draw` de DataTable para actualizar el gráfico
    $('#consolidados_dt').on('draw.dt', function () {
        actualizarGrafico(); // Llamar a la función actualizarGrafico después de que se dibuja la tabla
    });

    // Puedes invocar `actualizarGrafico()` aquí si quieres que se ejecute una vez al cargar la página por primera vez.
  });
</script>

















