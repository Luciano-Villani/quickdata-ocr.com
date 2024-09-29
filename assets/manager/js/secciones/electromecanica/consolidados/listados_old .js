function newexportaction(e, dt, button, config) {
  var self = this;
  var oldStart = dt.settings()[0]._iDisplayStart;
  
  dt.one("preXhr", function (e, s, data) {
    data.start = 0;
    data.length = 2147483647;
    
    dt.one("preDraw", function (e, settings) {
      if (button[0].className.indexOf("buttons-copy") >= 0) {
        $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-excel") >= 0) {
        $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config)
          : $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-csv") >= 0) {
        $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config)
          : $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-pdf") >= 0) {
        $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config)
          ? $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config)
          : $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf("buttons-print") >= 0) {
        $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
      }
      
      dt.one("preXhr", function (e, s, data) {
        settings._iDisplayStart = oldStart;
        data.start = oldStart;
      });
      
      setTimeout(dt.ajax.reload, 0);
      return false;
    });
  });
  
  dt.ajax.reload();
}

function initDatatable(search = false, type = 0) {
  var prove = $("#id_proveedor").val() || [];
  var tipo_pago = $("#id_tipo_pago").val() || [];
  var periodo_contable = $("#periodo_contable").val() || [];
  var fecha = $("#tipo-fecha").is(":checked") ? $("#daterange2").val() : false;

  

  $("#consolidados_dt").DataTable().destroy();
  
  var table = $("#consolidados_dt")
   
    .DataTable({
      fixedHeader: {
        header: true,
      },
      dom: "Blfrtip",
      scrollX: true,
      scrollCollapse: true,
      scrollY: 300,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"],
      ],
      pageLength: 25,
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
          targets: [0], // Ajusta los índices según las nuevas columnas
          title: "Proveedor",
        },
        {
          targets: [1],
          title: "Nro factura",
        },
        {
          targets: [2],
          title: "Nro cuenta",
        },
        {
          targets: [3],
          title: "Nro medidor",
        },
        {
          targets: [4],
          title: "Dependencia",
        },
        {
          targets: [5],
          title: "Dirección de Consumo",
        },
        {
          targets: [6],
          title: "Nombre Cliente",
        },
        {
          targets: [7],
          title: "Consumo",
        },
        {
          targets: [8],
          title: "U.Med",
        },
        {
          targets: [9],
          title: "cosfi",
        },
        {
          targets: [10],
          title: "tgfi",
        },
        {
          targets: [11],
          title: "Importe Total",
        },
        {
          targets: [12],
          title: "Mes Fc",
        },
        {
          targets: [13],
          title: "Año Fc",
        },
        {
          targets: [14],
          title: "Vencimiento",
        },
      ],
      language: {
        url: "/assets/manager/js/plugins/tables/translate/spanish.json",
      },
      processing: true,
      serverSide: true,
      type: "POST",
      order: false,
      ajax: {
        data: {
          type: type,
          table: "_consolidados_canon",
          data_search: search,
          id_proveedor: prove,
          tipo_pago: tipo_pago,
          periodo_contable: periodo_contable,
          fecha: fecha,
        },
        url: "/Electromecanica/Consolidados/list_dt_canon",
        type: "POST",
       
       

        
      },
    });
}

$(document).ready(function () {
  $('input[name="daterange2"]').daterangepicker({
    showDropdowns: true,
    locale: {
      applyLabel: "Aplicar",
      cancelLabel: "Cancelar",
      format: "DD/MM/YYYY",
      customRangeLabel: "Búsqueda avanzada",
    },
    ranges: {
      'Hoy': [moment(), moment()],
      'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Ultimos 7 días': [moment().subtract(6, 'days'), moment()],
      'Ultimos 30 días': [moment().subtract(29, 'days'), moment()],
      'Este mes': [moment().startOf('month'), moment().endOf('month')],
      'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
    },
  }, function(start, end, label) {
    console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
  });
  
  var range = $('input[name="daterange2d"]').daterangepicker({
    showDropdowns: true,
    showCustomRangeLabel: true,
    locale: {
      format: "DD/MM/YYYY",
      customRangeLabel: "Búsqueda avanzada",
    },
    ranges: {
      Hoy: [moment(), moment()],
      Ayer: [moment().subtract(1, "days"), moment().subtract(1, "days")],
      "Últimos 7 Días": [moment().subtract(6, "days"), moment()],
      "Últimos 30 Días": [moment().subtract(29, "days"), moment()],
      "Este Mes": [moment().startOf("month"), moment().endOf("month")],
      "Mes Pasado": [
        moment().subtract(1, "month").startOf("month"),
        moment().subtract(1, "month").endOf("month"),
      ],
    },
  }, function(start, end, label) {
    if (validarSeleccionFecha()) {
      var tipo_fecha = $('input[name="tipo_fecha"]:checked').val();
      var strSearchvar = start.format("YYYY-MM-DD") + "@" + end.format("YYYY-MM-DD");
      var parametrosUrl = "?tipo_fecha=" + tipo_fecha + "&filtro=1&buscarFechas=" + strSearchvar;
      // initDatatable(strSearchvar, 1);
      // $('#consolidados_dt').DataTable().search('<searchstring>');
    }
  });
  
  range.on("cancel.daterangepicker", function () {});
  range.on("load.daterangepicker", function () {});
  
  var drp = $('input[name="daterange2"]').data("daterangepicker");
  initDatatable();
  
  var base_url = $("body").data("base_url");
  
  $("body").on("click", "#resetfilter", function (e) {
    e.preventDefault();
    $("#tipo-fecha").prop('checked', false);
    $("#id_proveedor").val("").trigger("change");
    $("#id_tipo_pago").val("").trigger("change");
    $("#periodo_contable").val("").trigger("change");
    $('#daterange2').data('daterangepicker').setEndDate(new Date);
    $('#daterange2').data('daterangepicker').setStartDate(new Date);
    initDatatable();
  });
  
  $("body").on("click", "#applyfilter", function (e) {
    e.preventDefault();
    initDatatable(false, 4);
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
      column.visible(!column.visible());
    });
  });
  
  function validarSeleccionFecha() {
    var accesorios = document.querySelectorAll('input[name="tipo_fecha"]:checked');
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
            table: "_consolidados_canon",
            date_search: tableParams,
            search: { value: "" },
            draw: 1,
            start: 1,
            length: 10,
          },
          url: base_url + "Consolidados/list_dt_canon",
          type: "POST",
        },
      })
      .ajax.reload();
  }
  
  function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
      if (new Date().getTime() - start > milliseconds) {
        break;
      }
    }
  }
  
  $("body").on("click", "span.borrar_dato", function (e) {
    e.preventDefault();
    var dato = new FormData();
    var file = $(this).data("file");
    var lote = $(this).data("lote");

    // Mostrar los valores en la consola para verificar
    console.log("File:", file);
    console.log("Lote:", lote);

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
                        url: $("body").data("base_url") + "Electromecanica/Consolidados/delete",
                        success: function (result) {
                            $("body #applyfilter").trigger('click');
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
