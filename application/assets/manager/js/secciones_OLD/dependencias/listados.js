$(document).ready(function () {
  var base_url = $("body").data("base_url");

  if ($("body").data("data_action") == "Editar") {
    $(".collapse").collapse("toggle");
  }
  $("form#form-validate-jquery").validate({
    rules: {
      id_secretaria: {
        required: true,
        min: 1,
      },
      dependencia: "required",
      direccion: "required",
    },
    messages: {
      id_secretaria: "Seleccione una opción",
      dependencia: "El campo Dependencia es requerido",
      direccion: "El campo Dirección es requerido",
    },
  });

  $("#dependencias_dt").DataTable({
    pageLength: 10,
    dom: "Blfrtip",
    buttons: [
      //   'copy','colvis'
    ],
    columnDefs: [
    //   { visible: false, targets: [1] },
      { width: "1%", className: "dt-center dt-nowrap", targets: "_all" },
      { width: "1%", orderable: false, targets: [3] },
      
    ],
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    type: "POST",
    dataSrc: "",
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      data: { table: "_dependencias" },
      url: base_url + "Dependencias/list_dt",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
  });
});
