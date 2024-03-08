$(document).ready(function () {
  initDatatable();
  
  
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



  $("body").on("change", "select#search_secretaria", function (e) {
    e.preventDefault();
    if ($(this).val() == "0") {
      initDatatable();
      return;
    }
    initDatatable($('select[id="search_secretaria"] option:selected').text(), 4);
  });

function initDatatable(search = false, type = 0){
  $("#dependencias_dt").DataTable().destroy();
   $("#dependencias_dt").DataTable({
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"],
    ],
    pageLength: 25,
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
        url:$("body").data("base_url") + "assets/manager/js/plugins/tables/translate/spanish.json",
      },
      type: "POST",
 
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        data: {
          type: type,
          table: "_dependencias",
          data_search: search,
        },
        url: $("body").data("base_url") + "Dependencias/list_dt",
        type: "POST",
        error: function (jqXHR, textStatus, errorThrown) {
          alert(jqXHR.status + textStatus + errorThrown);
        },
      },
    });
    
  }
  });
  