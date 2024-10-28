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
  //console.log("Proveedor: ", prove);
 // var tipo_pago = $("#id_tipo_pago").val() || [];
  //var periodo_contable = $("#periodo_contable").val() || [];
  var fecha = $("#tipo-fecha").is(":checked") ? $("#daterange2").val() : false;
  var mes_fc = $('#id_mes_fc').val() || []; // Captura el valor de `mes_fc` en una variable.
  //var anio_fc = $('#id_anio_fc').val() || []; // Captura el valor de `anio_fc` en una variable.
  var anio_fc = $('#id_anio_fc').val() && $('#id_anio_fc').val().length > 0 ? $('#id_anio_fc').val() : null;
  var cosfiFilter = $('#cosfi_filter').is(':checked'); // Captura el estado del checkbox
  var tgfiFilter = $('#tgfi_filter').is(':checked'); // Captura el estado del checkbox


  

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
      pageLength: 50,
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
        { targets: [0], title: "Proveedor", data: 0, visible: false }, // Utiliza el índice 0
        { targets: [1], title: "Tarifa", data: 48 },
        { targets: [2], title: "Nro factura", data: 1 },
        { targets: [3], title: "Nro cuenta", data: 2 },
        { targets: [4], title: "Nro medidor", data: 3 },
        { targets: [5], title: "Dependencia", data: 4 },
        
        { targets: [6], title: "Dirección de Consumo", data: 5 },
        
        { targets: [7], title: "Nombre Cliente", data: 6 },
        { targets: [8], title: "Cons kWh/kW", data: 7 },
        { targets: [9], title: "Cosfi", data: 9 },
        { targets: [10], title: "Tgfi", data: 10 },
        { targets: [11], title: "E Activa kWh", data: 53 },
        { targets: [12], title: "E Reactiva kVArh", data: 54 },
        { targets: [13], title: "Importe $", data: 11 },
        { targets: [14], title: "Vencimiento", data: 14 },
        { targets: [15], title: "Impuestos $", data: 15 },
        { targets: [16], title: "Bimestre", data: 16 },
        { targets: [17], title: "Liquidación", data: 17 }, // Liquidación
        { targets: [18], title: "Cargo Variable Hasta $", data: 18 }, // Cargo Variable Hasta
        { targets: [19], title: "Cargo Fijo $", data: 19 }, // Cargo Fijo
        { targets: [20], title: "Cargo Var $", data: 20 }, // Monto Cargo Var Hasta
        { targets: [21], title: "Cargo Var > $", data: 21 }, // Moto Var Mayor
        { targets: [22], title: "Otros Conceptos $", data: 22 }, // Otros Conceptos
        { targets: [23], title: "Conceptos Eléctricos $", data: 23 }, // Conceptos Eléctricos
        { targets: [24], title: "Energía Inyectada", data: 24 }, // Energía Inyectada
        { targets: [25], title: "Pot Punta", data: 25 }, // Pot Punta
        { targets: [26], title: "Pot Fuera Punta Cons", data: 26 }, // Pot Fuera Punta Cons
        { targets: [27], title: "Energía Punta Act", data: 27 }, // Energía Punta Act
        { targets: [28], title: "Energía Resto Act", data: 28 }, // Energía Resto Act
        { targets: [29], title: "Energía Valle Act", data: 29 }, // Energía Valle Act
        { targets: [30], title: "Energía Reac Act", data: 30 }, // Energía Reac Act
        { targets: [31], title: "Cargo Pot Contratada $", data: 31, visible: false }, // Cargo Pot Contratada
        { targets: [32], title: "Cargo Pot Ad $", data: 32 , visible: false}, // Cargo Pot Ad
        { targets: [33], title: "Cargo Pot Excedente $", data: 33, visible: false}, // Cargo Pot Excedente
        { targets: [34], title: "Recargo TGFI $", data: 34 }, // Recargo TGFI
        { targets: [35], title: "Consumo Pico Vigente", data: 35 }, // Consumo Pico Vigente
        { targets: [36], title: "Cargo Pico $", data: 36 }, // Cargo Pico
        { targets: [37], title: "Consumo Resto Vigente", data: 37 }, // Consumo Resto Vigente
        { targets: [38], title: "Cargo Resto $", data: 38 }, // Cargo Resto
        { targets: [39], title: "Consumo Valle Vigente", data: 39, visible: false }, // Consumo Valle Vigente
        { targets: [40], title: "Cargo Valle $", data: 40 }, // Cargo Valle
        { targets: [41], title: "E Actual", data: 41 }, // E Actual
        { targets: [42], title: "Cargo Contratado", data: 42 }, // Cargo Contratado
        { targets: [43], title: "Cargo Adquirida $", data: 43 }, // Cargo Adquirido
        { targets: [44], title: "Cargo Excedente $", data: 44 }, // Cargo Excedente
        { targets: [45], title: "Cargo Variable $", data: 45 }, // Cargo Variable
        { targets: [46], title: "Total Vencido $", data: 46 }, // Total Vencido
        { targets: [47], title: "E Reactiva kVArh", data: 47 }, // Energía Reactiva Consumida
        
        { targets: [48], title: "U.Med", data: 8, visible: false },
        { targets: [49], title: "Días Cons", data: 49 },

        { targets: [50], title: "Días Comp", data: 50 },
        { targets: [51], title: "Cons DC kWh", data: 51 },
        { targets: [52], title: "Período Consumo", data: 52 },
        { targets: [53], title: "Mes Fc", data: 12 },
        { targets: [54], title: "Año Fc", data: 13 },
        { targets: [55], title: "Subsidio", data: 55 },


        
        

        {
          targets: [56], // ID Proveedor
          data: 56,      // Utiliza el índice 16 para 'id_proveedor'
          visible: true,
          searchable: false
        }
     
        
      ],
      
      language: {
        url: "/assets/manager/js/plugins/tables/translate/spanish.json",
      },
      processing: true,
      serverSide: true,
     // type: "POST",
      order: false,
      ajax: {
        data: {
          type: type,
          table: "_consolidados_canon",
          data_search: search,
          id_proveedor: prove,
          //tipo_pago: tipo_pago,
          //periodo_contable: periodo_contable,
          fecha: fecha,
          mes_fc:mes_fc,
          anio_fc:anio_fc,
          cos_fi:cosfiFilter,
          tg_fi:tgfiFilter
          
        },
        url: "/Electromecanica/Consolidados/list_dt_canon",
        type: "POST",
        
       
       

        
      },
    });

    $(document).ready(function () {
      // Inicializar DataTable
      var table = $('#consolidados_dt').DataTable();
    
      // Función para aplicar visibilidad a las columnas según el proveedor seleccionado
      function aplicarVisibilidad() {
        var selectedProveedor = $("#id_proveedor").val(); // Obtener proveedor seleccionado
        console.log(selectedProveedor);
    
        if (table.columns().count() > 10) { // Verificar que existen al menos 11 columnas
          if (selectedProveedor == '1') {
            table.column(10).visible(false);  // Tgfi
            table.column(11).visible(false);
            table.column(12).visible(false);
            table.column(18).visible(false);
            table.column(25).visible(false);   // Pot Punta
            table.column(26).visible(false);   // Pot Fuera Punta Cons
            table.column(27).visible(false);   // Energía Punta Act
            table.column(28).visible(false);   // Energía Resto Act
            table.column(29).visible(false);   // Energía Valle Act
            table.column(30).visible(false);   // Energía Reac Act
            table.column(31).visible(false);   // Cargo Pot Contratada
            table.column(32).visible(false);   // Cargo Pot Ad
            table.column(33).visible(false);   // Cargo Pot Excedente
            table.column(34).visible(false);   // Recargo TGFI
            table.column(35).visible(false);   // Consumo Pico Vigente
            table.column(36).visible(false);   // Cargo Pico
            table.column(37).visible(false);   // Consumo Resto Vigente
            table.column(38).visible(false);   // Cargo Resto
            table.column(39).visible(false);   // Consumo Valle Vigente
            table.column(40).visible(false);   // Cargo Valle
            table.column(41).visible(false);   // E Actual
            table.column(42).visible(false);   // Cargo Contratado
            table.column(43).visible(false);   // Cargo Adquirido
            table.column(44).visible(false);   // Cargo Excedente
            table.column(45).visible(false);   // Cargo Variable
            table.column(47).visible(false)
          

          } else if (selectedProveedor == '2') {
            table.column(10).visible(false); // Tgfi
            table.column(16).visible(false); // Bimestre
            table.column(17).visible(false); // Liquidación
            table.column(18).visible(false); // Cargo Variable Hasta
            table.column(20).visible(false); // Cargo Var
            table.column(21).visible(false); // Cargo Var >
            table.column(25).visible(false); // Pot Punta
            table.column(26).visible(false); // Pot Fuera Punta Cons
            table.column(27).visible(false); // Energía Punta Act
            table.column(28).visible(false); // Energía Resto Act
            table.column(29).visible(false); // Energía Valle Act
            table.column(30).visible(false); // Energía Reac Act
            table.column(34).visible(false); // Recargo TGFI
            table.column(35).visible(false); // Consumo Pico Vigente
            table.column(36).visible(false); // Cargo Pico
            table.column(37).visible(false); // Consumo Resto Vigente
            table.column(38).visible(false); // Cargo Resto
            table.column(39).visible(false); // Consumo Valle Vigente
            table.column(40).visible(false); // Cargo Valle
            table.column(41).visible(false); // E Actual
            table.column(47).visible(false); // E Reac Cons
            table.column(49).visible(false); // Días Cons
            table.column(50).visible(false); // Días Comp
            table.column(51).visible(false); // cons dc

          } else if (selectedProveedor == '3') {
            table.column(8).visible(false);   // Cons kWh
            table.column(9).visible(false);   // Cosfi
            table.column(11).visible(false);
            table.column(12).visible(false);
            table.column(16).visible(false);  // Bimestre
            table.column(17).visible(false);  // Liquidación
            table.column(18).visible(false);  // Cargo Variable Hasta
            table.column(19).visible(false);  // Cargo Fijo
            table.column(20).visible(false);  // Cargo Var
            table.column(21).visible(false);  // Cargo Var >
            table.column(22).visible(false);  // Otros Conceptos
            table.column(23).visible(false);  // Conceptos Eléctricos
            table.column(31).visible(false);  // Cargo Pot Contratada
            table.column(32).visible(false);  // Cargo Pot Ad
            table.column(33).visible(false);  // Cargo Pot Excedente
            table.column(42).visible(false);  // Cargo Contratado
            table.column(43).visible(false);  // Cargo Adquirido
            table.column(44).visible(false);  // Cargo Excedente
            table.column(45).visible(false);  // Cargo Variable
            table.column(49).visible(false);  // Días Cons
            table.column(50).visible(false);  // Días Comp
            table.column(51).visible(false);  // Cons DC
            table.column(52).visible(false);  // e activa
            table.column(53).visible(false);  // e reactiva
          }
        } 
        
      }
    
      // Listener para el cambio en el select de proveedor
     // $("#id_proveedor").on("change", function () {
   //     aplicarVisibilidad(); // Aplicar visibilidad al cambiar proveedor
    // });
    
      // Ejecutar aplicarVisibilidad cada vez que se redibuja la tabla (incluye el filtrado)
     table.on('draw', function () {
      aplicarVisibilidad();
     });
    
      // Listener para el botón de aplicar filtro
     // $("#applyfilter").on("click", function () {
     //   // Aquí puedes aplicar los filtros y luego llamar a `table.draw()` si es necesario
      //  //table.draw(); // Ejecuta los filtros en la DataTable y dispara el evento draw
     // });
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
    //$("#id_tipo_pago").val("").trigger("change");
    //$("#periodo_contable").val("").trigger("change");
    $("#id_mes_fc").val("").trigger("change");
    $("#id_anio_fc").val("").trigger("change");
    $('#daterange2').data('daterangepicker').setEndDate(new Date);
    $('#daterange2').data('daterangepicker').setStartDate(new Date);

    // Resetear los nuevos checkboxes (cosfi y tgfi)
    $("#cosfi_filter").prop('checked', false); // Restablecer el checkbox de Cos Fi
    $("#tgfi_filter").prop('checked', false);  // Restablecer el checkbox de Tg Fi


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
