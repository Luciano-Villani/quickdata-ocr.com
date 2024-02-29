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
  if (type == 4) {
    prove = $("#id_proveedor").val();
    tipo_pago = $("#id_tipo_pago").val();
    periodo_contable = $("#periodo_contable").val();
  }

  $("#consolidados_dt").DataTable().destroy();
  var table = $("#consolidados_dt")
    .on("xhr.dt", function (e, settings, json, xhr) {
      // console.log(json.data);
    })
    .DataTable({
      fixedHeader: {
        header: true,
        // footer: true
    },
    paging: false,
    scrollCollapse: true,
    scrollX: true,
    scrollY: 300,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"],
      ],
      pageLength: 100,
      dom: "Blfrtip",
      scrollX: true,
      order: [0, "desc"],
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
      columnDefs: [],
      columnDefs: [
        {
          targets: [0, 1, 2],
          visible: false,
        },


        {
          targets: [14, 15, 16, 17, 18],
          //			className: 'dt-body-right',
          bSortable: false,
        },
        { className: "dt-center dt-nowrap", targets: [] },
        { targets: ["_all"], className: "dt-left dt-nowrap" },
      ],      
      columnDefs: [
        {
          targets: [0, 1, 2],
          visible: false,
        },
        {
          targets: [4],
          render: function (data, type, full, meta) {
            console.log('render 4')
            console.log(data)
            return data + " (" + full[1] + ")";
          },
          targets: [8],
          render: function (data, type, full, meta) {
            // console.log('full');
            // console.log(full);
            punto = ".";
            if (full[2] == "") {
              // full[2] = "";
              punto = "";
            }else{
              punto = + full[2]
            }
            return "PROG "+data+full[2];
          },
          targets: [9],
          render: function (data, type, full, meta) {
            
          
            punto = "";
            if (full[8] != "") {
           
              punto = "."+full[8];
            }else{
              
              punto = '';
            }
            return +data+punto;
          },
        },

        {
          targets: [14, 15, 16, 17, 18],
          //			className: 'dt-body-right',
          bSortable: false,
        },
        { className: "dt-center dt-nowrap", targets: [] },
        { targets: ["_all"], className: "dt-left dt-nowrap" },
      ],
      language: {
        url: "/assets/manager/js/plugins/tables/translate/spanish.json",
      },
      processing: true,
      serverSide: true,
      // responsive: true,
      type: "POST",
      order: [[0, "desc"]],
      dataSrc: "",
      ajax: {
        data: {
          type: type,
          table: "_consolidados",
          data_search: search,
          id_proveedor: prove,
          tipo_pago: tipo_pago,
          periodo_contable: periodo_contable,
        },
        url: "/Consolidados/list_dt",
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
  var range = $('input[name="daterange2"]').daterangepicker(
    {
      startDate: "-1m",
      endDate: '+1m',
      locale: {
        format: "DD/MM/YYYY",
        customRangeLabel: "Búsqueda avanzada",
      },
      ranges: {
        Hoy: [moment(), moment()],
        Ayer: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Últinmos 7 Días": [moment().subtract(6, "days"), moment()],
        "Últinmos 30 Días": [moment().subtract(29, "days"), moment()],
        "Este Mes": [moment().startOf("month"), moment().endOf("month")],
        "Mes Pasado": [
          moment().subtract(1, "month").startOf("month"),
          moment().subtract(1, "month").endOf("month"),
        ],
      },
      // opens: 'left'
    },
    function (start, end, label) {

      var strSearchvar = new Array();
      strSearchvar = start.format("YYYY-MM-DD") + "@" + end.format("YYYY-MM-DD");
      console.log('strSearchvar');
      console.log(strSearchvar);

        // $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

   
      if (validarSeleccionFecha()) {
        var tipo_fecha = $('input[name="tipo_fecha"]:checked').val();

        // console.log(start.format('YYYY-MM-DD'));

        //envio parametro true para que se selecciones todas los resultados
        var parametrosUrl =
          "?tipo_fecha=" +
          tipo_fecha +
          "&filtro=1&buscarFechas=" +
          strSearchvar;

        initDatatable(strSearchvar, 1);
        // $('#consolidados_dt').DataTable().search('<searchstring>');
      }
    }
  );
  range.on("cancel.daterangepicker", function () {});
  range.on("load.daterangepicker", function () {
 alert();
  });
  var drp = $('input[name="daterange2"]').data('daterangepicker');
  // var start = moment().subtract(29, 'days');
  // var end = moment();
  


  // console.log(drp.startDate.format('DD-MM-YYYY'));
  // console.log(drp.endDate.format('DD-MM-YYYY'));
  initDatatable();
  var base_url = $("body").data("base_url");

  $("body").on("click", "#resetfilter", function (e) {
    e.preventDefault();

    $("#id_proveedor").val('').trigger('change') ;
    $("#id_tipo_pago").val('').trigger('change') ;
    $("#periodo_contable").val('').trigger('change') ;
    
    // $("#id_tipo_pago").prop("selectedIndex", 0);
    initDatatable();
  });

  $("body").on("click", "#applyfilter", function (e) {
    e.preventDefault();

    if (
      $("#id_proveedor").val().length === 0 && $("#id_tipo_pago").val().length === 0 && $("#periodo_contable").val().length === 0) {
      $.confirm({
        icon: "icon-alert",
        title: "Criterios de filtrado",
        content: "Seleccione opciones de filtrado",
        buttons: {
          cancel: {
            text: "Aceptar",
            btnClass: "btn-prymary",
            action: function () {
              return;
            },
          },
        },
      });

      return false;
    }
    initDatatable(false, 4);

    console.log($("#id_proveedor").val());
    console.log($("#id_tipo_pago").val());
  });

  $("body").on("click", "#descarga-exell", function (e) {
    e.preventDefault();
    $("body .buttons-excel").trigger("click");
  });

  $("body").on("change", "select#selectperiodo", function (e) {
    e.preventDefault();
    if ($(this).val() == "0") {
      initDatatable();
      return;
    }
    initDatatable($('select[id="selectperiodo"] option:selected').text(), 4);
  });

  document.querySelectorAll("a.toggle-vis").forEach((el) => {
    el.addEventListener("click", function (e) {
      e.preventDefault();

      let columnIdx = e.target.getAttribute("data-column");
      let column = table.column(columnIdx);

      // Toggle the visibility
      column.visible(!column.visible());
    });
  });





  function validarSeleccionFecha() {
    var accesorios = document.querySelectorAll(
      'input[name="tipo_fecha"]:checked'
    );
    if (accesorios.length <= 0) {
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Debe seleccionar un tipo de Fecha!",
      });
      return false;
    }
    return true;
  }

  function filterData(tableParams) {
    $("#consolidados_dt").DataTable().destroy();
    $("#consolidados_dt")
      .DataTable({
        ajax: {
          data: {
            table: "_consolidados",
            date_search: tableParams,
            search: { value: "" },
            draw: 1,
            start: 1,
            length: 10,
          },
          url: base_url + "Consolidados/list_dt",

          type: "POST",
        },
      })
      .ajax.reload();
  }

  $("body").on("click", "span.borrar_dato", function (e) {
    e.preventDefault();

    var dato = new FormData();
    var file = $(this).data("file");
    var lote = $(this).data("lote");
    dato.append("file", file);
    dato.append("lote", lote);
    $.confirm({
      autoClose: "cancel|10000",
      title: "Eliminar Datos y archivos",
      content: "Confirma eliminar datos del archivo : " + file + "  ?",
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
              url: $("body").data("base_url") + "Consolidados/delete",
              success: function (result) {
                initDatatable();
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
  });
});
