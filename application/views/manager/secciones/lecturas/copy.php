<?php
if (isset($_SERVER['HTTP_REFERER'])) {
	$retorno = $_SERVER['HTTP_REFERER'];
} else {
	$retorno = "/Admin";
}

// $file = $this->Manager_model->getWhere('_datos_api','id='.$result->id);
// $a=json_decode($file->dato_api);
// $totalIndices = count($a->document->inference->pages[0]->prediction->fecha_emision->values);
// $fecha_emision = '';
// for ($paso = 0; $paso < $totalIndices; $paso++) {
//     $fecha_emision .= '' . trim($a->document->inference->pages[0]->prediction->fecha_emision->values[$paso]->content);
// }
// echo '<pre>';
// var_dump(fecha_es($fecha_emision,'Y-m-d')); 
// echo '</pre>';

?>



<div class="card ">
    <div class="card-header header-elements-inline">
        <h5 class="card-title bg-titulo text-center text-dark">Facturas con múltiples cuentas / Imputar las diferentes cuentas a esta factura</h5>
        <div class="header-elements">
            <div class="list-icons">
            <a href="<?= $retorno ?>" type="button" class="mt-3 btn-agregar bg-buton-blue btn"><b><i class="icon-backward"></i></b> Volver</a>
               <!-- <a class="list-icons-item" data-action="collapse"></a> -->
            </div>
            

        </div>
    </div>


	<div class="card-body">
		<?php

		if ($result && file_exists($result->nombre_archivo)) {
		?>
			<embed src="<?= base_url($result->nombre_archivo . '#toolbar=1&navpanes=3&scrollbar=1&zoom=110') ?>" type="application/pdf" width="100%" height="500px">
		<?php
		} else {
			echo 'no existe el archivo PDF';
		}
		?>
	</div>

</div>

<?php
if ($result) {
?>
<div class="card">
	<div class="card-header">
	<div class="mt-1" style="background-color: #10355E; color: white; margin-top: -20px;">
	<h5 class="card-title bg-titulo text-center text-dark">Datos generales de la factura</h5>
	</div>
	
    <?php
    $archivo = explode('/', $result->nombre_archivo, 4);
    ?>
	
    <h5 class="row pt-2">
	<div class="col-3">
            <label class="form-label">
			<strong>Nro de factura:</strong> <?= $result->nro_factura ?>
            </label>
        </div>
		<div class="col-3">
            <label class="form-label">
			<strong>Archivo:</strong> <?= $archivo[3] ?>
            </label>
        </div>
		<div class="col-3">
            <label class="form-label">
			<strong>Emisión:</strong> <?php echo fecha_es($result->fecha_emision) ?>
            </label>
        </div>
		<div class="col-3">
            <label class="form-label">
			<strong>Vencimiento:</strong> <?php echo fecha_es($result->vencimiento_del_pago) ?>
            </label>
        </div>

     </h5>
    </div>
	
	


		<?php
		

		?>

		<?php echo form_open(base_url('Lecturas/copy'), array('id' => 'form-lineas')); ?>
		

		<div class="row card-header">
			
			<div class="col-md-8">
				<div class="col mb-3" style="background-color: #C9D4E6;">



				

				<h6 class="text-center">Utilice este formulario para agregar cuentas / cargos</h6>

				</div>
				<div class="row">
					<input readonly type="hidden" name="id_registro" value="<?= $result->id ?>" class="form-control">
					<input readonly type="hidden" id="id_multiple" name="id_multiple" value="<?= $result->id ?>" class="form-control">
					<div class="col-md-2">
						<label class="form-label">Nro de cuenta:</label>
						<input type="text" id="nro_cuenta" name="nro_cuenta" class="form-control">
						<?php echo form_error('nro_cuenta', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
					<div class="col-md-2">
						<label class="form-label sumar">Cargo fijo:</label>
						<input value="" id="cargo_fijo" name="cargo_fijo" type="text" class="form-control input-sm input" onchange="sumar()" />
						<?php echo form_error('cargo_fijo', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
					<div class="col-md-2">
						<label class="form-label sumar">Variable 1:</label>
						<input value="" id="variable_1" name="variable_1" type="text" class="form-control input-sm input" onchange="sumar()" />
						<?php echo form_error('variable_1', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
					<div class="col-md-2">
						<label class="form-label sumar">Variable 2:</label>
						<input value="" id="variable_2" name="variable_2" type="text" class="form-control input-sm input" onchange="sumar()" />
						<?php echo form_error('variable_2', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
					<div class="col-md-2">
						<label class="form-label">Total:</label>
						<input value="" name="total" id="total" type="text" class="form-control input-sm input" />
						<?php echo form_error('total', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
					<div class="col-md-2"  style="margin-top: 5px;">
						<button type="submit" class="mt-3 btn-agregar bg-buton-blue btn"><b><i class="icon-plus3"></i></b> Agregar</button>
						
					</div>

				</div>
				
				<div class="row mt-3 d-none">
					<div class="col-md-3">
						<label class="form-label">Total Ingresado:</label>
						<input name="total_importe_ingresado" type="text" class="form-control" placeholder="0" value="">
						<?php echo form_error('total_importe_ingresado', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
					<div class="col-md-3 d-none">
						<label class="form-label">Total Importe Factura:</label>
						<input name="total_importe" type="text" class="form-control" placeholder="Total importe" value="">
						<?php echo form_error('total_importe', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
					</div>
				</div>
			</div>
			<style>
				/* div#multilinea{
					text-align: -webkit-center;
				} */
			</style>
			<div id='multilinea' class="col-4 ">
				<?= $lineas ?>
			</div>
		</div>


		<?php echo form_close(); ?>

		<div class="card-header ">
			<h5 class="mb-0">Datos de indexación</h5>
		</div>
		<div class="card-body  ">
			<div class="card">

				<table id="indexaciones_dst" class="table datatable-show-all dataTable no-footer">
					<thead>
						<tr>
							<th>Expediente</th>
							<th>Nro de cuenta</th>
							<th>Secretaria</th>
							<th>Dependencia</th>
							<th>Programa</th>
							<th>Proyecto</th>
							<th>proveedor</th>
							<th>Tipo pago</th>

						</tr>
					</thead>

				</table>

			</div>
		</div>


	<?php
}

	?>

	<script>
		$().ready(function() {
			// $("#multilinea").html("<img src='http://www.drogbaster.it/loading/loading25.gif'>");
			var base_url = $("body").data("base_url");
			var dato = new FormData();
			dato.append("nro_cuenta", $("body").data("nro_cuenta"));

			var tabla_index = $('#indexaciones_dt').DataTable({
				dom: "frtip",
				columnDefs: [{
						targets: -1,
						//			className: 'dt-body-right',
						bSortable: false,
					},
					{
						visible: false,
						targets: [0]
					}
				],
				language: {
					url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
				},
				serverSide: true,
				type: "POST",
				createdRow: function(row, data, dataIndex) {
					console.log("data");
					console.log(data);
					console.log(row);

				},
				ajax: {
					data: {
						"nro_cuenta": $("body").data("nro_cuenta")
					},
					url: "/Admin/Lecturas/indexaciones_dt",
					type: "POST",
					error: function(jqXHR, textStatus, errorThrown) {
						alert(jqXHR.status + textStatus + errorThrown);
					},
				},

			});

			var vari = $("form#form-lineas").validate({
				rules: {
					nro_cuenta: {
						required: function(element) {

						}

					},
					cargo_fijo: {
						required: true,
						min: 0
					},
					variable_1: {
						required: false,
						
					},
					variable_2: {
						required: false,
						
					},

				},
				messages: {
					nro_cuenta: "requerido",
					cargo_fijo: "requerido",
					
				},

	submitHandler: function(form, e) {
    e.preventDefault();
    var formData = $(form).serialize();
    var URL = $(form).attr("action");

    // Guarda el contenido actual del contenedor de líneas en una variable
    var originalContent = $("div#multilinea").html();

    // Muestra el spinner de carga
    $("div#multilinea").html('<img src="http://www.drogbaster.it/loading/loading25.gif">').css({
        'text-align': 'center'
    });

    $.ajax({
        url: URL,
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            console.log('Respuesta del servidor:', response);

            if (response.status === 'success') {
                // Si la operación fue exitosa, actualiza el contenido con el nuevo HTML
                $("div#multilinea").css({'text-align': ''});
                $("div#multilinea").html(response.html);
				// <<<<< AQUI DEBES AGREGAR LA LINEA PARA ACTUALIZAR EL TOTAL FACTURADO >>>>>
                $('h3.panel-title').text('Total facturado: $ ' + response.totalFactura);

                // Limpia los campos del formulario
                $(form).find("input[type=text], input[type=number], textarea").val('');
                $(form).find("select").prop('selectedIndex', 0);
            } else if (response.status === 'error') {
                // Si el servidor detecta un error, restaura el contenido original
                // y muestra la alerta.
                $("div#multilinea").css({'text-align': ''});
                $("div#multilinea").html(originalContent);
                alert(response.message);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            // Maneja errores de comunicación del servidor
            // Restaura el contenido original y muestra la alerta.
            $("div#multilinea").html(originalContent).css({'text-align': ''});
            alert("Ocurrió un error al procesar la solicitud: " + textStatus);
            console.error(xhr.responseText);
        }
    });
}

			});

		})

		function sumar() {
            var fijo = $("#cargo_fijo");
            var variable_1 = $("#variable_1");
            var variable_2 = $("#variable_2");

            var ppfijo = isNaN(parseFloat(fijo.val())) ? 0 : parseFloat(fijo.val());
            var ppvari1 = isNaN(parseFloat(variable_1.val())) ? 0 : parseFloat(variable_1.val());
            var ppvari2 = isNaN(parseFloat(variable_2.val())) ? 0 : parseFloat(variable_2.val());

            var total = ppfijo + ppvari1 + ppvari2;
            $("#total").val(total.toFixed(2));
        }

        // Asegúrate de que la función sumar se llame en los eventos adecuados
        $("#cargo_fijo, #variable_1, #variable_2").on("input", sumar);
    

		
		$(document).ready(function() {
			$("body").on("click", "span.borrar-file", function(e) {
				e.preventDefault();
				var dato = new FormData();
				var id = $(this).data("id_file");
				var tabla = $(this).data("tabla");
				var importe = $(this).data("importe");
				dato.append("id", id);
				dato.append("tabla", tabla);
				dato.append("campo", "id");
				dato.append("deletefile", false);

				$.confirm({
					autoClose: "cancel|10000",
					title: "Eliminar Datos",
					content: "Confirma eliminar el registro?",
					buttons: {
						confirm: {
							text: "Borrar",
							btnClass: "btn-blue",
							action: function() {
								$.ajax({
									type: "POST",
									contentType: false,
									dataType: "json",
									data: dato,
									processData: false,
									cache: false,
									beforeSend: function() {},
									url: $("body").data("base_url") + "Lotes/deletefile",
									success: function(result) {

										console.log('mytablemytable');
										console.log(id);
										$("body").find("[data-file='" + id + "']").html('');

										importe_ant = $("span#totalingresado").html() - importe;

										console.log($("span#totalingresado").html(importe_ant));
										// $("h3#totalingresado").html('$');

										// $(".datatable-ajax").DataTable().ajax.reload()
									},
									error: function(xhr, errmsg, err) {
										console.log(xhr.status + ": " + xhr.responseText);
									},
								});
							},
						},
						cancel: {
							text: "Cancelar",
							btnClass: "btn-red",
							action: function() {},
						},
					},
				});
			});
		});
		
   

	</script>

	<!-- 
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>

<div id="pdfContainer"></div> 
-->