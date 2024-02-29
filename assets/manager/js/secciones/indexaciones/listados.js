function newexportaction(e, dt, button, config) {
  var self = this;
  var oldStart = dt.settings()[0]._iDisplayStart;
  dt.one("preXhr", function (e, s, data) {
    // Just this once, load all data from the server...
    data.start = 0;
    data.length = 2147483647;
    dt.one("preDraw", function (e, settings) {
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
        $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.excelHtml5.action.call(
              self,
              e,
              dt,
              button,
              config
            )
          : $.fn.dataTable.ext.buttons.excelFlash.action.call(
              self,
              e,
              dt,
              button,
              config
            );
      } else if (button[0].className.indexOf("buttons-csv") >= 0) {
        $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.csvHtml5.action.call(
              self,
              e,
              dt,
              button,
              config
            )
          : $.fn.dataTable.ext.buttons.csvFlash.action.call(
              self,
              e,
              dt,
              button,
              config
            );
      } else if (button[0].className.indexOf("buttons-pdf") >= 0) {
        $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.pdfHtml5.action.call(
              self,
              e,
              dt,
              button,
              config
            )
          : $.fn.dataTable.ext.buttons.pdfFlash.action.call(
              self,
              e,
              dt,
              button,
              config
            );
      } else if (button[0].className.indexOf("buttons-print") >= 0) {
        $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
      }
      dt.one("preXhr", function (e, s, data) {
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
function initDatatable(search = false, type = 0) {
  var prove = false;
  var tipo_pago = false;
  var periodo_contable = false;

  // desde los filtros 4
  // if (type == 4) {
  //   prove = $("#id_proveedor").val();
  //   tipo_pago = $("#id_tipo_pago").val();
  //   periodo_contable = $("#periodo_contable").val();
  // }

  $("#indexaciones_dt").DataTable().destroy();
  var table = $("#indexaciones_dt")
    .on("xhr.dt", function (e, settings, json, xhr) {
      // console.log(json.data);
    })
    .DataTable({
      fixedHeader: {
        header: true,
      },
      autoWidth: false,
      paging: true,
      scrollCollapse: true,
      scrollX: true,
      scrollY: 300,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"],
      ],
      pageLength: 20,
      dom: "Blfrtip",
      scrollX: true,
      buttons: [
        {
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

      columnDefs: [
        {
          targets: ["_all"],
          className: "dt-left",
        },
        {
          targets: [0, 1],
          visible: false,
        },
        { width: "", orderable: false, targets: [7] },
        {
          // targets: [4],
          // render: function (data, type, full, meta) {
          //   console.log('render 4')
          //   console.log(data)
          //   return data + " (" + full[1] + ")";
          // },
        },
      ],
      language: {
        url: "/assets/manager/js/plugins/tables/translate/spanish.json",
      },
      processing: true,
      serverSide: true,
      // responsive: true,
      type: "POST",
      order: [2, "desc"],
      dataSrc: "",
      ajax: {
        data: {
          type: type,
          table: "_indexaciones",
          data_search: search,
          id_proveedor: prove,
          tipo_pago: tipo_pago,
          periodo_contable: periodo_contable,
        },
        url: "/Indexaciones/list_dt",
        type: "POST",
        error: function (jqXHR, textStatus, errorThrown) {
          alert(jqXHR.status + textStatus + errorThrown);
        },
      },

      initComplete: function () {
        this.api()

          .columns([3]) // This is the hidden jurisdiction column index
          .every(function () {
            var column = this;

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                // if (
                //   $("#periodo_contable").find("option[value='" + d + "']")
                //     .length
                // ) {
                //   $("#periodo_contable").val("").trigger("change");
                // } else {
                //   // Create the DOM option that is pre-selected by default
                //   var newState = new Option(d, d);
                //   // Append it to the select
                //   $("#periodo_contable").append(newState).trigger("change");
                // }
              });
          });
      },
    });
}

$(document).ready(function () {
	var base_url = $("body").data("base_url");
	var action = $("body").data("data_action");
	
  disabled = 'disabled=""';
  if (action == "xxx") {
    var disabled = 'disabled="disabled"';
	$(".collapse").collapse("toggle");
  }
});

$(document).ready(function () {
  if ($("body").data("data_action") == "Editar") {
    $(".collapse").collapse("toggle");

    $("#tipo_pago,#select_programa,#select_dependencia, #select_proyecto").removeAttr("disabled");
  } else {
  }

  $("form#form-validate-jquery").validate({
    rules: {
      id_secretaria: { required: true, min: 1 },
      nro_cuenta: "required",
      expediente: "required",
      id_proveedor: { required: true, min: 1 },
      tipo_pago: { required: true, min: 1 },
    },
    messages: {
      id_proveedor: "requerido",
      id_secretaria: "requerido",
      nro_cuenta: "requerido",
      tipo_pago: "requerido",
      expediente: "requerido",
    },
  });

  initDatatable();

  // $('#select_dependencia').attr('disabled','disabled');
  $("body").on("click", "a.borrar_file", function (e) {
    e.preventDefault();

    var dato = new FormData();
    var id = $(this).data("id");
    var tabla = $("body").data("tabla");
    dato.append("id", id);
    dato.append("tabla", tabla);
    dato.append("campo", "id");

    $.confirm({
      autoClose: "cancel|10000",
      title: "Eliminar Datos",
      content: 'Confirma eliminar el registro??',
      buttons: {
        confirm: {
          text: "Borrar",
          btnClass: "btn-blue",
          action: function () {
            eliminarDatos(dato);
          },
        },
      //   somethingElse: {
      //     text: 'Archivos y datos',
      //     btnClass: 'btn-blue',
      //     action: function(){
      //       dato.append("deletefile", false);
      //       eliminarDatos(dato);
      //     }
      // },
        cancel: {
          text: "Cancelar",
          btnClass: "btn-red",
          action: function () {},
        },
      },
    });
  });

  function eliminarDatos(dato){
    $.ajax({
      type: "POST",
      contentType: false,
      dataType: "json",
      data: dato,
      processData: false,
      cache: false,
      beforeSend: function () {},
      url: $("body").data("base_url") + "Indexaciones/delete",
      success: function (result) {
        alertas(result);
        initDatatable();
      },
      error: function (xhr, errmsg, err) {
        console.log(xhr.status + ": " + xhr.responseText);
      },
    });
  }
  $("#form-validate-jquery").on("change","select#select_secretaria",function () {
      $("#tipo_pago").removeAttr("disabled");
      var dato = new FormData();
      dato.append("id", $(this).val());
	if($(this).val() == 0){
		$("#tipo_pago,#select_programa,#select_dependencia, #select_proyecto").attr("disabled","disabled");
		return;
	}
      $.ajax({
        type: "POST",
        contentType: false,
        //    				dataType: 'json',
        data: dato,
        processData: false,
        cache: false,
        beforeSend: function () {
          $("#select_dependencia").empty();
          $("#select_proyecto").empty();
          $("#select_programa").empty();
          //   $("#tipo_pago").empty();
        },
        url: $("body").data("base_url") + "Admin/Dependencias/get_dependencias",
        success: function (result) {
          var obj = jQuery.parseJSON(result);
          console.log("resultwwwww");
          console.log(Object.keys(obj.dependencias).length);
          console.log(result);

          if (Object.keys(obj.dependencias).length > 0) {
            $("#select_dependencia").removeAttr("disabled");
            $("#select_dependencia").append(
              '<option selected value="0">SELECCIONE DEPENDENCIA</option>'
            );

            $.each(obj.dependencias, function (id, value) {
              $("#select_dependencia").append(
                '<option value="' +
                  value["id"] +
                  '">' +
                  value["dependencia"].toUpperCase()+
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

            $.each(obj.proyectos, function (id, value) {
              $("#select_proyecto").append(
                '<option value="' +
                  value["id_interno"] +
                  '">' +
				  value["descripcion"].toUpperCase()+
                  "</option>"
              );
            });
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

            $.each(obj.programas, function (id, value) {
              $("#select_programa").append(
                '<option value="' +
                  value["id_interno"] +
                  '">' +
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
        error: function (xhr, errmsg, err) {
          console.log(xhr.status + ": " + xhr.responseText);
        },
      });
    }
  );
});
