<style>
	#chatr2 {
		width: 100%;
		min-height: 400px;
	}

	.select2-search {
		z-index: 50;
	}

	* {
		margin: 0;
		padding: 0;
	}

	#chart-container {
		position: relative;
		height: 100vh;
		overflow: none;
	}

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
		margin-left: -80px !important;

	}

	.dataTables_filter input {
		text-transform: uppercase;
	}
</style>


	<div class="row">
	<div id="testdata"> </div>
	</div>

<div class="panel panel-flat">

	<div class="panel-heading tablas">
		<div class="container-fluid row ">
			<div class="col">
				<label class="col" for="id_proveedor">

					<?php
					$js = array(
						'id' => 'id_proveedor',
						'class' => 'form-control',
						// 'multiple' => 'multiple',

					);
					?>

					<?= form_dropdown('id_proveedor', $select_proveedores, set_value('id_proveedor'), $js); ?>

					<script>
						// $('#id_proveedor').select2({
						// 	language: {
						// 		maximumSelected: function(e) {
						// 			var t = "Puede seleccionar " + e.maximum + " Proveedor";
						// 			e.maximum != 1 && (t += "s");
						// 			return t + '';
						// 		}
						// 	},
						// 	placeholder: 'PROVEEDORES',
						// 	maximumSelectionLength: 1,
						// 	// minimumResultsForSearch: "-1",
						// 	width: '100%',
						// 	closeOnSelect: true,
						// 	selectionCssClass: '',

						// 	// escapeMarkup: function(m) {
						// 	// 	return m;
						// 	// }
						// });
					</script>
				</label>
			</div>

			<div class="col">
				<label class="col" for="periodo_contable">
					<?php
					$js = array(
						'id' => 'periodo_contable',
						'class' => 'form-control',
						// 'multiple' => "multiple",
					);
					?>
					<?= form_dropdown('periodo_contable', $select_periodo_contable, '', $js); ?>
					<script>
						// $('#periodo_contable').select2({
						// 	language: {
						// 		maximumSelected: function(e) {
						// 			var t = "Puede seleccionar " + e.maximum + " Proveedor";
						// 			e.maximum != 1 && (t += "s");
						// 			return t + '';
						// 		}
						// 	},
						// 	placeholder: 'PERIODO CONTABLE',
						// 	tags: true,
						// 	maximumSelectionLength: 1,
						// 	minimumResultsForSearch: "-1",
						// 	width: '100%',
						// 	closeOnSelect: false,
						// 	selectionCssClass: '',
						// })
					</script>

				</label>
			</div>

			<div class="col">
				<label class="col" for="id_secretaria">

					<?php
					$js = array(
						'id' => 'id_secretaria',
						'class' => 'form-control',
						// 'multiple' => "multiple",

					);
					?>

					<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria'), $js); ?>

					<script>
						// $('#id_secretaria').select2({
						// 	// language: {
						// 	// 	maximumSelected: function(e) {
						// 	// 		var t = "Puede seleccionar " + e.maximum + " Secretaría";
						// 	// 		e.maximum != 1 && (t += "s");
						// 	// 		return t + '';
						// 	// 	}
						// 	// },
						// 	// placeholder: 'SECRETARIAS',
						// 	// maximumSelectionLength: 1,

						// 	// width: '100%',
						// 	// closeOnSelect: false,
						// 	// selectionCssClass: '',

						// 	// escapeMarkup: function(m) {
						// 	// 	return m;
						// 	// }
						// });
					</script>
				</label>
			</div>
			
			<div class="col-md-4 d-none">
				<div class="form-group form-group-feedback form-group-feedback-right  ">

					<?php
					$js = array(
						'id' => 'select_programa',
						'disabled' => 'disabled',
						'class' => ' select2 form-control custom-select ',
					);


					?>

					<?= form_dropdown('id_programa', $select_programas, set_value('id_programa', @$seleccion_programa), $js); ?>
					<?php echo form_error('id_programa', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
			<div class="col">
				<label class="col" for="id_secretaria">

					<?php
					$js = array(
						'id' => 'id_secretaria',
						'class' => 'form-control',
						// 'multiple' => "multiple",

					);
					?>

					<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria'), $js); ?>

					<script>
						// $('#id_secretaria').select2({
						// 	// language: {
						// 	// 	maximumSelected: function(e) {
						// 	// 		var t = "Puede seleccionar " + e.maximum + " Secretaría";
						// 	// 		e.maximum != 1 && (t += "s");
						// 	// 		return t + '';
						// 	// 	}
						// 	// },
						// 	// placeholder: 'SECRETARIAS',
						// 	// maximumSelectionLength: 1,

						// 	// width: '100%',
						// 	// closeOnSelect: false,
						// 	// selectionCssClass: '',

						// 	// escapeMarkup: function(m) {
						// 	// 	return m;
						// 	// }
						// });
					</script>
				</label>
			</div>
			
			<div class="col">
				<label class="col" for="id_secretaria">

					<?php
					$js = array(
						'id' => 'id_secretaria',
						'class' => 'form-control',
						// 'multiple' => "multiple",

					);
					?>

					<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria'), $js); ?>

					<script>
						// $('#id_secretaria').select2({
						// 	// language: {
						// 	// 	maximumSelected: function(e) {
						// 	// 		var t = "Puede seleccionar " + e.maximum + " Secretaría";
						// 	// 		e.maximum != 1 && (t += "s");
						// 	// 		return t + '';
						// 	// 	}
						// 	// },
						// 	// placeholder: 'SECRETARIAS',
						// 	// maximumSelectionLength: 1,

						// 	// width: '100%',
						// 	// closeOnSelect: false,
						// 	// selectionCssClass: '',

						// 	// escapeMarkup: function(m) {
						// 	// 	return m;
						// 	// }
						// });
					</script>
				</label>
			</div>
			
			<div class="col-md-4 d-none">
				<div class="form-group form-group-feedback form-group-feedback-right  ">

					<?php
					$js = array(
						'id' => 'select_programa',
						'disabled' => 'disabled',
						'class' => ' select2 form-control custom-select ',
					);


					?>

					<?= form_dropdown('id_programa', $select_programas, set_value('id_programa', @$seleccion_programa), $js); ?>
					<?php echo form_error('id_programa', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>

				</div>
			</div>
			<div class="col">
				<label class="col" for="id_secretaria">

					<?php
					$js = array(
						'id' => 'id_secretaria',
						'class' => 'form-control',
						// 'multiple' => "multiple",

					);
					?>

					<?= form_dropdown('id_secretaria', $select_secretarias, set_value('id_secretaria'), $js); ?>

					<script>
						// $('#id_secretaria').select2({
						// 	// language: {
						// 	// 	maximumSelected: function(e) {
						// 	// 		var t = "Puede seleccionar " + e.maximum + " Secretaría";
						// 	// 		e.maximum != 1 && (t += "s");
						// 	// 		return t + '';
						// 	// 	}
						// 	// },
						// 	// placeholder: 'SECRETARIAS',
						// 	// maximumSelectionLength: 1,

						// 	// width: '100%',
						// 	// closeOnSelect: false,
						// 	// selectionCssClass: '',

						// 	// escapeMarkup: function(m) {
						// 	// 	return m;
						// 	// }
						// });
					</script>
				</label>
			</div>
		
		<!--	<div class="col-2 ">
				<label class="">
					<input type="checkbox"  class="radio"  value="1" name="totalizador"  id="totalizador"/>
					<span data-popup="tooltip" style="color: lightgray">Comparar Datos</span>
				</label>
			</div> -->
			<div class="col">
				<button id="applyfilter" type="button" class="btn mb-1 btn-outline-dark btn-sm" style="width: 160px";><b><i class="icon-filter3"></i></b>Aplicar Filtros</button>
				<button id="resetfilter" type="button" class="btn mb-1 btn-outline-dark btn-sm"style="width: 160px";><b><i class="icon-reset"></i></b>Eliminar Filtros</button>
				<button id="descarga-exell" type="button" class="btn btn-outline-excel btn-sm"style="width: 160px";><b><i class="icon-file-excel"></i></b> DESCARGAR</button>
			</div>			
			
		</div>
		
		


	</div>

	<div class="panel">
		<div class="card tablas">
			<h5 class="card-title bg-titulo text-center text-dark">Gráfico de gastos por período</h5>
			<div class="card-header">
				<div id="chart-container">
					cart
				</div>
				
			</div>
		</div>

	</div>

		<div class="panel">
		<div class="card tablas">
		<table id="consolidados_dt" class="datatable-ajax table-bordered table-hover datatable-highlight" style="width: 100%">
					<thead>

						<tr>
							<th>Período Cont</th>
							<th>Proveedor</th>
							<th>Total</th>
						</tr>
					</thead>


				</table>

			</div>
			</div>



		<!-- <canvas id="myChart" width="" height="" aria-label="Hello ARIA World" role="img"></canvas> -->
		<div id="test">

		</div>
	</div>
	<script src="https://fastly.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.11/lodash.min.js"></script>
	<script>
		$(document).ready(function() {
			$("body").on(
				"change",
				"select#id_secretaria",
				function() {


					var dato = new FormData();
					dato.append("id", $(this).val());
					if ($(this).val() == 0) {
						$(
							"#tipo_pago,#select_programa,#select_dependencia, #select_proyecto"
						).attr("disabled", "disabled");
						return;
					}
					$.ajax({
						type: "POST",
						contentType: false,
						//    				dataType: 'json',
						data: dato,
						processData: false,
						cache: false,
						beforeSend: function() {
							$("#select_dependencia").empty();
							$("#select_proyecto").empty();
							$("#select_programa").empty();
							//   $("#tipo_pago").empty();
						},
						url: $("body").data("base_url") + "Admin/Dependencias/get_dependencias",
						success: function(result) {
							var obj = jQuery.parseJSON(result);

							if (Object.keys(obj.dependencias).length > 0) {
								$("#select_dependencia").removeAttr("disabled");
								$("#select_dependencia").append(
									'<option selected value="0">SELECCIONE DEPENDENCIA</option>'
								);

								$.each(obj.dependencias, function(id, value) {
									$("#select_dependencia").append(
										'<option value="' +
										value["id"] +
										'">' +
										value["dependencia"].toUpperCase() +
										"</option>"
									);
								});
							} else {
								$("#select_dependencia").append(
									'<option selected value="">SIN DEPENDENCIA</option>'
								);
								// $('#select_dependencia').attr('disabled','disabled');
							}

							if (Object.keys(obj.proyectos).length > 0) {
								$("#select_proyecto").removeAttr("disabled");
								$("#select_proyecto").append(
									'<option selected value="0">SELECCIONE PROYECTO</option>'
								);

								// $.each(obj.proyectos, function (id, value) {
								//   $("#select_proyecto").append(
								//     '<option value="' +
								//       value["id"] +
								//       '">' +
								//       value["id_interno"] +
								//       "  " +
								//       value["descripcion"].toUpperCase() +
								//       "</option>"
								//   );
								// });
							} else {
								$("#select_proyecto").append(
									'<option selected value="">SIN PROYECTOS</option>'
								);
								// $('#select_dependencia').attr('disabled','disabled');
							}

							if (Object.keys(obj.programas).length > 0) {
								$("#select_programa").removeAttr("disabled");
								$("#select_programa").append(
									'<option selected value="0">SELECCIONE PROGRAMA</option>'
								);

								$.each(obj.programas, function(id, value) {
									$("#select_programa").append(
										'<option value="' +
										value["id"] +
										'">' +
										value["id_interno"] +
										"  " +
										value["descripcion"].toUpperCase() +
										"</option>"
									);
								});
							} else {
								$("#select_programa").append(
									'<option selected value="">SIN PROGRAMAS</option>'
								);
								// $('#select_dependencia').attr('disabled','disabled');
							}

							//    					toastr.success('Registro Editado correctamente!', 'Categorías');
						},
						error: function(xhr, errmsg, err) {
							console.log(xhr.status + ": " + xhr.responseText);
						},
					});
				}
			);
		});
		$("body").on("click", "#applyfilter", function(e) {
			e.preventDefault();
			if (
				$('select[id="id_proveedor"] option:selected').val() == 0 ||
				$('select[id="id_secretaria"] option:selected').val() == 0 ||
				$('select[id="periodo_contable"] option:selected').val() == 0
			) {
				$.confirm({
					icon: "icon-alert",
					title: "Criterios de filtrado",
					content: "Seleccione opciones de filtrado",
					buttons: {
						cancel: {
							text: "Aceptar",
							btnClass: "btn-prymary",
							action: function() {
								return;
							},
						},
					},
				});

				return false;
			}
			initDatatable(false, 4);
		});

		$("body").on("click", "#descarga-exell", function(e) {
			e.preventDefault();
			$("body .buttons-excel").trigger("click");
		});

		$("body").on("change", "select#selectperiodo", function(e) {
			e.preventDefault();
			if ($(this).val() == "0") {
				initDatatable();
				return;
			}
			initDatatable($('select[id="selectperiodo"] option:selected').text(), 4);
		});

		function newexportaction(e, dt, button, config) {
			var self = this;
			var oldStart = dt.settings()[0]._iDisplayStart;
			dt.one("preXhr", function(e, s, data) {
				// Just this once, load all data from the server...
				data.start = 0;
				data.length = 2147483647;
				dt.one("preDraw", function(e, settings) {
					// Call the original action function
					if (button[0].className.indexOf("buttons-copy") >= 0) {
						$.fn.dataTable.ext.buttons.copyHtml5.action.call(
							self,
							e,
							dt,
							button,
							config
						);
					} else if (button[0].className.indexOf("buttons-excel") >= 0) {
						$.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
							$.fn.dataTable.ext.buttons.excelHtml5.action.call(
								self,
								e,
								dt,
								button,
								config
							) :
							$.fn.dataTable.ext.buttons.excelFlash.action.call(
								self,
								e,
								dt,
								button,
								config
							);
					} else if (button[0].className.indexOf("buttons-csv") >= 0) {
						$.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
							$.fn.dataTable.ext.buttons.csvHtml5.action.call(
								self,
								e,
								dt,
								button,
								config
							) :
							$.fn.dataTable.ext.buttons.csvFlash.action.call(
								self,
								e,
								dt,
								button,
								config
							);
					} else if (button[0].className.indexOf("buttons-pdf") >= 0) {
						$.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
							$.fn.dataTable.ext.buttons.pdfHtml5.action.call(
								self,
								e,
								dt,
								button,
								config
							) :
							$.fn.dataTable.ext.buttons.pdfFlash.action.call(
								self,
								e,
								dt,
								button,
								config
							);
					} else if (button[0].className.indexOf("buttons-print") >= 0) {
						$.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
					}
					dt.one("preXhr", function(e, s, data) {
						// DataTables thinks the first item displayed is index 0, but we're not drawing that.
						// Set the property to what it was before exporting.
						settings._iDisplayStart = oldStart;
						data.start = oldStart;
					});
					// Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
					setTimeout(dt.ajax.reload, 0);
					// Prevent rendering of the full data to the DOM
					return false;
				});
			});
			// Requery the server with the new one-time export settings
			dt.ajax.reload();
		}

		var dom = document.getElementById('chart-container');
		var myChart = echarts.init(dom);
		initDatatable(sarch = false, type = 0, table = '_consolidadosGr');

		function initgrap() {

		}

		function initDatatable(search = false, type = 0, table = '_consolidadosGr', url = "/Admin/Graphs") {

			// var fecha = '08/10/1943';

			// var edad = calcularEdad('05', '05', '1949'); // dia mes año nacimiento

			// console.log("Edadss:s", edad.anios, "años,", edad.meses, "meses y", edad.dias, "días.");

			// if((edad.anios >= 75) && (edad.dias > 0 && edad.meses > 0)){
			//   console.log('no puede');
			// }else{
			//   console.log('puedessss');
			// }

			var prove = false;
			var tipo_pago = false;
			var periodo_contable = false;
			secretaria = '';

			// desde los filtros 4
			if (type == 4) {
				prove = $("#id_proveedor").val();

				secretaria = $("#id_secretaria").find('option:selected').text();

				periodo_contable = $("#periodo_contable").val();

				if ($("#tipo-fecha").is(":checked")) {
					var fecha = $("#daterange2").val();
				}

				// var $select = $("#id_tipo_pago");
				// var value = $select.val();
				// var data = [];
				// value.forEach(function(valor, indice, array) {
				// 	data[indice] = $select.find("option[value=" + valor + "]").text();
				// });
			}

			$("#consolidados_dt").DataTable().destroy();
			var table = $("#consolidados_dt")
				.on("xhr.dt", function(e, settings, json, xhr) {
					// console.log(json.data);
				})
				.DataTable({
					fixedHeader: {
						header: true,
						// footer: true
					},
					dom: "Blfrtip",
					//   scrollX: true,
					//   scrollCollapse: true,
					//   scrollY: 300,

					paging: false,
					lengthMenu: [
						[10, 25, 50, 100, -1],
						[10, 25, 50, 100, "All"],
					],
					pageLength: '',
					order: [0, "desc"],
					buttons: [{
							extend: "excelHtml5",
							exportOptions: {
								columns: ":visible",
							},
							text: "Excel",
							titleAttr: "Excel",
							action: newexportaction,
							className: "d-none",
						},
						{
							extend: "colvis",
							text: "Ver / Ocultar",
							className: "",
						},
					],
					columnDefs: [{
							targets: [1, 2],
							visible: true,
							orderable: false
						},
						// {
						// 	targets: ['_all'],
						// 	visible: false
						// },

					],

					language: {
						url: "/assets/manager/js/plugins/tables/translate/spanish.json",
					},
					processing: true,
					serverSide: true,
					// responsive: true,
					type: "POST",

					// ordering:false,
					ajax: {
						data: {
							type: type,
							table: table,
							data_search: search,
							id_proveedor: prove,
							periodo_contable: periodo_contable,
							fecha: fecha,
							secretaria: secretaria,
						},
						url: url,
						type: "POST",
						error: function(jqXHR, textStatus, errorThrown) {
							alert(jqXHR.status + textStatus + errorThrown);
						},
					},
					"drawCallback": function(settings) {
						// console.log('config');
						// // console.log(settings);
						// console.log(settings);

						const periodos = settings.aoData.reduce((acc, item) => {
							// console.log('config');
							// console.log(item._aData[0]);
							if (!acc.includes(item._aData[0])) {
								acc.push(item._aData[0]);
							}
							return acc;
						}, [])

						console.log('periodos');
						console.log(periodos);

						acc = [];
						myconfig = [];

						const misdatos = settings.json.finales.reduce((datos, item) => {
							// console.log('config');
							// console.log(item);
							if (!datos.includes(item)) {
								datos.push(item);
							}
							return datos;
						}, [])

						// datos = [];
						console.log('datos');
						console.log(misdatos);

						const labelOption = {
							show: true,
							
							formatter: '{c}  {name|{a}}',
							
							rich: {
								name: {}
							}
						};
						option = {
							title: {
								text: settings.json.title,

								left: 'center'
							},
							tooltip: {
								trigger: 'axis',
								axisPointer: {
									type: 'shadow'
								}
							},
							legend: {
								align: 'auto',
								type: 'scroll',
								// right: 50,
								top: 20,
								bottom: 20,
							},
							grid: {
								left: '3%',
								right: '4%',
								bottom: '3%',
								containLabel: true
							},
							dataZoom: [{
									type: 'slider',
									start: 0,
									end: 100
								},
								{
									type: 'inside',
									start: 0,
									end: 100
								}
							],
							xAxis: [{
								inverse: true,
								data: periodos,
								type: 'category',
							}],
							yAxis: [{

								type: 'value',

							}],
							series: misdatos
						};

						myChart.setOption(option, true);
						myChart.resize();
					},
					initComplete: function() {

					},
				});
		}
	</script>