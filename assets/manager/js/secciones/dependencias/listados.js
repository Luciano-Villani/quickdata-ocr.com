$(document).ready(function () {
  initDatatable();
  
  
  var base_url = $("body").data("base_url");





  $("body").on("click", "a.borrar_dato", function (e) {
    e.preventDefault();

    var dato = new FormData();
    dato.append("id", $(this).data("id"));
    if($(this).data('estado')!= 1){
      $.confirm({
        autoClose: "cancel|10000",
        title: "Eliminar Datos",
        content: "Confirma eliminar datos ?",
        buttons: {
          confirm: {
            text: "Borrar",
            btnClass: "btn-blue",
            action: function () {
              $.ajax({
                type: "POST",
                contentType: false,
                dataType: "json",
                data: dato,
                processData: false,
                cache: false,
                beforeSend: function () {},
                url: $("body").data("base_url") + "Proyectos/delete",
                success: function (result) {
                  alertas(result);
                  table.ajax.reload( null, false );
                },
                error: function (xhr, errmsg, err) {
                  console.log(xhr.status + ": " + xhr.responseText);
                },
              });
            },
          },
          cancel: {
            text: "Cancelar",
            btnClass: "btn-red",
            action: function () {},
          },
        },
      });
    }else{
      $.confirm({
        title: "Borrar Dependencia",
        content:
          "La dependencia posee una indexación",
        buttons: {
          cancel: {
            text: "Cancelar",
            btnClass: "btn-red",
            action: function () {
              return;
            },
          },
        },
      });
    }

  });
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
  table =  $("#dependencias_dt").DataTable({
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"],
    ],
    pageLength: 25,
    dom: "Blfrtip",
     buttons: [
       //   'copy','colvis'
      ],
      order: [0, "desc"],
      columnDefs: [
        { width: "1%", className: "dt-left dt-nowrap", targets: "_all" },
        { width: "1%", orderable: false, targets: [0,4] },
        { visible: false, targets: [0,1] },
        
      ],
      language: {
        url:$("body").data("base_url") + "assets/manager/js/plugins/tables/translate/spanish.json",
      },
      type: "POST",
 
      processing: true,
      serverSide: true,
      // responsive: true,
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
  