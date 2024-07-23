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
        <h5 class="card-title bg-titulo text-center text-dark">Factura con múltiples cuentas</h5>
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
        <div class="card-header ">
            <?php

            $archivo = explode('/', $result->nombre_archivo, 4);

            ?>
            <h5 class="mb-0">Datos de la Factura Nro: <?= $result->nro_factura ?> - Lote # <?= $result->id ?> - Nombre del archivo: <?= $archivo[3] ?> </h5>

        </div>
        <?php

        ?>

        <?php echo form_open(base_url('Lecturas/copy'), array('id' => 'form-lineas')); ?>

        <div class="card-body row container-fluid">

            <div class="col-md-8">
                <div class="row mb-3">

                    <div class="col-12">
                        <label class="form-label">Fecha de emisión: <?php echo  fecha_es($result->fecha_emision) ?></label>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Vencimiento: <?php echo  fecha_es($result->vencimiento_del_pago) ?></label>
                    </div>

                </div>
                <div class="row">
                    <input type="hidden" name="id_registro" value="<?= $result->id ?> " class="form-control">
                    <input type="hidden" id="id_multiple" name="id_multiple" value="" class="form-control">
                    <div class="col-md-2">
                        <label class="form-label">Nro de cuenta:</label>
                        <input type="text" name="nro_cuenta" class="form-control">
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
                        <input value="" readonly="total" name="total" id="total" type="text" class="form-control input-sm input" />
                        <?php echo form_error('total', '<div class="invalid-feedback" style="display:block;">', "</div>"); ?>
                    </div>

                </div>
                <div class="row mt-4">
                    <div class="col my-3">
                        <button type="submit" class="btn btn-agregar bg-buton-blue btn-labeled btn-labeled-right"><b><i class="icon-plus3"></i></b>Agregar cuenta</button>
                    </div>
                </div>
                <div class="row mt-3">
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

            <?php if ($this->ion_auth->is_super() || $this->ion_auth->is_admin()) { ?>
                <?= $lineas ?>
            <?php } ?>

        </div>
        <?php echo form_close(); ?>

        <div class="card-header ">
            <h5 class="mb-0">Datos de indexación</h5>
        </div>
        <div class="card-body row container-fluid">
            <div class="card">

                <table id="indexaciones_dt" class="table datatable-show-all dataTable no-footer">
                    <thead>
                        <tr>
                            <th>Expediente</th>
                            <th>Nro de cuenta</th>
                            <th>Secretaria</th>
                            <th>Dependencia</th>
                            <th>Programa</th>
                            <th>Proyecto</th>
                            <th>Proveedor</th>
                            <th>Tipo pago</th>

                        </tr>
                    </thead>

                </table>

            </div>
        </div>

    </div>

<?php
}

?>

<script>
    $().ready(function() {

        var vari = $("form#form-lineas").validate({
            rules: {
                nro_cuenta: {
                    required: function(element) {

                    }

                },
                cargo_fijo: {
                    required: true,
                    
                },
                variable_1: {
                    required: false,
                   
                },
                variable_2: {
                    required: false,
                    
                },
                total: {
                    
                    required: true,
                },
            },
            messages: {
                nro_cuenta: "requerido",
                cargo_fijo: "requerido",
                variable_1: "requerido",
                variable_2: "requerido",
                total: " A imputar",
            },
            submitHandler: function(e) {
                var frm = $("#form-lineas"); //Identificamos el formulario por su id
                var datos = frm.serialize(); //Serializamos sus datos

                //Preparamos la petición Ajax
                var request = $.ajax({
                    url: frm.prop("action"), //Leerá la url en la etiqueta action del formulario (archivo.php)
                    method: frm.prop('method'), //Leerá el método en etiqueta method del formulario
                    data: datos, //Variable serializada más arriba 
                    dataType: "json" //puede ser de otro tipo
                });

                //Este bloque se ejecutará si no hay error en la petición
                request.done(function(respuesta) {
                    console.log(respuesta);
                    $('input#id_multiple').val(respuesta.lasId);
                    // console.log(respuest); //foo es una propiedad (clave), del json que devuelve el servidor
                    //Tratamos a respuesta según sea el tipo  y la estructura               
                });

                //Este bloque se ejecuta si hay un error
                request.fail(function(jqXHR, textStatus) {
                    alert("Hubo un error: " + textStatus);
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

    // Sumamos y aseguramos que el resultado tenga 2 decimales
    var total = (ppfijo + ppvari1 + ppvari2).toFixed(2);

    var iptotal = $("#total");
    iptotal.val(total);

    console.log('fijo  -  variable  variable');
    console.log(total);
}

    $(document).ready(function() {
        $("body").on("click", "span.borrar-file", function(e) {
            e.preventDefault();
            var dato = new FormData();
            var id = $(this).data("id_file");
            var tabla = $(this).data("tabla");
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
                                    alertas(result);
                                    console.log('mytablemytable');
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