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
  var secretaria = false;

  // desde los filtros 4
  if (type == 4) {
    prove = $("#id_proveedor").val();
    tipo_pago = $("#id_tipo_pago").val();
    periodo_contable = $("#periodo_contable").val();
    secretaria = $("#id_secretaria").val();

    if ($("#tipo-fecha").is(":checked")) {
      var fecha = $("#daterange2").val();
    }
    var $select = $("#id_tipo_pago");
    var value = $select.val();
    var data = [];
    if (value && value.length) {
      value.forEach(function (valor, indice, array) {
        data[indice] = $select.find("option[value=" + valor + "]").text();
      });
    }
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
      dom: "Blfrtip",
      scrollX: true,
      scrollCollapse: true,
      scrollY: 300,

      // paging: false,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"],
      ],
      pageLength: 25,
      // order: [1, "desc"],
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
          targets: [6, 16],
          visible: false,
        },
        {
          // render: function (data, type, row) {
          //   // console.log(row);
          //   return "PROG " + data;
          // },

          // targets: 5,
        },
        {
          render: function (data, type, row) {
            return data + "." + row[5];
          },
          targets: 6,
        },
      ],
      
      language: {
        url: "/assets/manager/js/plugins/tables/translate/spanish.json",
      },
      processing: true,
      serverSide: true,
      // responsive: true,
      type: "POST",
      order: false,
      // ordering:false,
      ajax: {
        data: {
          type: type,
          table: "_consolidados",
          data_search: search,
          id_proveedor: prove,
          tipo_pago: data,
          periodo_contable: periodo_contable,
          id_secretaria: secretaria,
          fecha: fecha,
        },
        url: "/Consolidados/list_dt",
        type: "POST",
        error: function (jqXHR, textStatus, errorThrown) {
          alert(jqXHR.status + textStatus + errorThrown);
        },
      },

      initComplete: function () {
        this.api()

          .columns([4]) // This is the hidden jurisdiction column index
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

var modoReporteActual = "consolidados";
var actualizandoFiltrosReporteFinal = false;
var filtrosConsolidadosTimer = null;

function escapeHtml(value) {
  return String(value == null ? "" : value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatMoney(value) {
  var number = parseFloat(value || 0);
  return number.toLocaleString("es-AR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function obtenerFiltrosReporteFinal() {
  var tipoPagoIds = $("#id_tipo_pago").val();
  var tipoPagoTextos = [];

  if (tipoPagoIds && tipoPagoIds.length) {
    var $select = $("#id_tipo_pago");
    tipoPagoIds.forEach(function (valor) {
      tipoPagoTextos.push($select.find("option[value=" + valor + "]").text());
    });
  }

  return {
    id_proveedor: $("#id_proveedor").val() || [],
    tipo_pago: tipoPagoTextos,
    periodo_contable: $("#periodo_contable").val() || [],
    id_secretaria: $("#id_secretaria").val() || [],
    fecha: $("#tipo-fecha").is(":checked") ? $("#daterange2").val() : "",
  };
}

function syncSelectOptions($select, opciones) {
  var selected = $select.val() || [];
  var validValues = {};
  var html = "";

  opciones.forEach(function (opcion) {
    validValues[String(opcion.id)] = true;
    html += '<option value="' + escapeHtml(opcion.id) + '">' + escapeHtml(opcion.text) + "</option>";
  });

  $select.html(html);
  selected = selected.filter(function (value) {
    return validValues[String(value)];
  });
  $select.val(selected).trigger("change.select2");
}

function seleccionarPeriodoActualSiCorresponde() {
  var periodoActual = window.REPORTE_FINAL_PERIODO_ACTUAL || "";
  var $periodo = $("#periodo_contable");

  if (!periodoActual || ($periodo.val() && $periodo.val().length)) {
    return;
  }

  if ($periodo.find('option[value="' + periodoActual.replace(/"/g, '\\"') + '"]').length) {
    $periodo.val([periodoActual]).trigger("change.select2");
  }
}

function textosSeleccionados($select) {
  var textos = [];
  ($select.val() || []).forEach(function (valor) {
    var texto = $select.find('option[value="' + String(valor).replace(/"/g, '\\"') + '"]').text();
    if (texto) {
      textos.push(texto.trim());
    }
  });
  return textos;
}

function agregarChip(chips, etiqueta, valores) {
  if (!valores || !valores.length) {
    return;
  }
  var texto = valores.length > 1 ? valores.length + " seleccionados" : valores[0];
  chips.push('<span class="filtro-chip">' + escapeHtml(etiqueta) + ': ' + escapeHtml(texto) + '</span>');
}

function actualizarChipsFiltros() {
  var chips = [];
  agregarChip(chips, "Proveedor", textosSeleccionados($("#id_proveedor")));
  agregarChip(chips, "Secretaria", textosSeleccionados($("#id_secretaria")));
  agregarChip(chips, "Tipo de pago", textosSeleccionados($("#id_tipo_pago")));
  agregarChip(chips, "Periodo", textosSeleccionados($("#periodo_contable")));
  if ($("#tipo-fecha").is(":checked")) {
    agregarChip(chips, "Fecha consolidacion", [$("#daterange2").val()]);
  }
  $("#filtros-activos").html(chips.join(""));
}

function aplicarFiltrosConsolidadosDebounced() {
  actualizarChipsFiltros();
  clearTimeout(filtrosConsolidadosTimer);
  filtrosConsolidadosTimer = setTimeout(function () {
    if (modoReporteActual === "reporte_final") {
      if (!actualizandoFiltrosReporteFinal) {
        actualizarOpcionesReporteFinal(cargarReporteFinal);
      }
    } else {
      initDatatable(false, 4);
    }
  }, 250);
}

function actualizarOpcionesReporteFinal(callback) {
  if (actualizandoFiltrosReporteFinal) {
    if (callback) {
      callback();
    }
    return;
  }

  actualizandoFiltrosReporteFinal = true;
  $.ajax({
    url: "/Consolidados/reporte_final_opciones",
    type: "POST",
    dataType: "json",
    data: obtenerFiltrosReporteFinal(),
    success: function (response) {
      if (response && response.status === "success" && response.opciones) {
        syncSelectOptions($("#id_tipo_pago"), response.opciones.tipo_pago || []);
        syncSelectOptions($("#periodo_contable"), response.opciones.periodo_contable || []);
        syncSelectOptions($("#id_secretaria"), response.opciones.secretaria || []);
      }
    },
    complete: function () {
      actualizandoFiltrosReporteFinal = false;
      seleccionarPeriodoActualSiCorresponde();
      if (callback) {
        callback();
      }
    },
  });
}

function renderReporteFinal(response) {
  var $tbody = $("#reporte_final_preview tbody");
  var filas = response.filas || [];

  $("#reporte-final-titulo").text(response.titulo || "REPORTE FINAL");
  $("#reporte-final-resumen").text(
    (response.cantidad || 0) + " facturas - Total: $" + formatMoney(response.total || 0)
  );

  if (!filas.length) {
    $tbody.html('<tr><td colspan="13" class="text-center text-muted">No hay registros para los filtros aplicados.</td></tr>');
    return;
  }

  var html = "";
  filas.forEach(function (fila) {
    if (fila.tipo === "detalle") {
      html += "<tr>" +
        "<td>" + escapeHtml(fila.proveedor) + "</td>" +
        "<td>" + escapeHtml(fila.expediente) + "</td>" +
        "<td>" + escapeHtml(fila.secretaria) + "</td>" +
        "<td>" + escapeHtml(fila.dependencia) + "</td>" +
        "<td>" + escapeHtml(fila.jurisdiccion) + "</td>" +
        "<td>" + escapeHtml(fila.programa) + "</td>" +
        "<td>" + escapeHtml(fila.objeto) + "</td>" +
        "<td>" + escapeHtml(fila.tipo_pago) + "</td>" +
        "<td>" + escapeHtml(fila.nro_cuenta) + "</td>" +
        "<td>" + escapeHtml(fila.nro_factura) + "</td>" +
        "<td>" + escapeHtml(fila.periodo) + "</td>" +
        "<td>" + escapeHtml(fila.vencimiento) + "</td>" +
        '<td class="importe">' + formatMoney(fila.importe) + "</td>" +
      "</tr>";
    } else if (fila.tipo === "subtotal_programa") {
      html += '<tr class="fila-subtotal"><td colspan="5"></td><td>' + escapeHtml(fila.programa) + '</td><td colspan="6"></td><td class="importe">' + formatMoney(fila.importe) + "</td></tr>";
    } else {
      html += '<tr class="fila-subtotal"><td colspan="4"></td><td>' + escapeHtml(fila.jurisdiccion) + '</td><td colspan="7"></td><td class="importe">' + formatMoney(fila.importe) + "</td></tr>";
    }
  });

  $tbody.html(html);
}

function cargarReporteFinal() {
  $("#reporte_final_preview tbody").html('<tr><td colspan="13" class="text-center text-muted">Generando vista previa...</td></tr>');

  $.ajax({
    url: "/Consolidados/reporte_final_preview",
    type: "POST",
    dataType: "json",
    data: obtenerFiltrosReporteFinal(),
    success: function (response) {
      if (!response || response.status !== "success") {
        $("#reporte_final_preview tbody").html('<tr><td colspan="13" class="text-center text-danger">No se pudo generar el reporte.</td></tr>');
        return;
      }
      renderReporteFinal(response);
    },
    error: function () {
      $("#reporte_final_preview tbody").html('<tr><td colspan="13" class="text-center text-danger">Error de conexion al generar el reporte.</td></tr>');
    },
  });
}

function setModoReporte(modo) {
  modoReporteActual = modo;
  $(".modo-reporte-btn").removeClass("active btn-primary").addClass("btn-outline-primary");
  $('.modo-reporte-btn[data-modo="' + modo + '"]').addClass("active btn-primary").removeClass("btn-outline-primary");

  if (modo === "reporte_final") {
    $("#vista-consolidada-card").addClass("d-none");
    $("#reporte-final-card").removeClass("d-none");
    $("#descarga-principal").addClass("d-none");
    seleccionarPeriodoActualSiCorresponde();
    actualizarOpcionesReporteFinal(cargarReporteFinal);
  } else {
    $("#vista-consolidada-card").removeClass("d-none");
    $("#reporte-final-card").addClass("d-none");
    $("#descarga-principal").removeClass("d-none");
  }
}

$(document).ready(function () {

    $('input[name="daterange2"]').daterangepicker({
        // autoUpdateInput: false,
        showDropdowns: true,
        locale:{
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
            'Mes pasado': [moment().subtract(1, 'month').startOf('month'), 
            moment().subtract(1, 'month').endOf('month')
            ]
        },

    }, function(start, end, label) {
        console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
    });
    var range = $('input[name="daterange2d"]').daterangepicker(
        {
            "showDropdowns": true,
            // startDate: "-1m",
            // endDate: "+1m",
            showCustomRangeLabel:true,
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
            // $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

            if (validarSeleccionFecha()) {
                var tipo_fecha = $('input[name="tipo_fecha"]:checked').val();

                var strSearchvar = new Array();
                strSearchvar =
                    start.format("YYYY-MM-DD") + "@" + end.format("YYYY-MM-DD");

                //envio parametro true para que se selecciones todas los resultados
                var parametrosUrl =
                    "?tipo_fecha=" +
                    tipo_fecha +
                    "&filtro=1&buscarFechas=" +
                    strSearchvar;

                // initDatatable(strSearchvar, 1);
                // $('#consolidados_dt').DataTable().search('<searchstring>');
            }
        }
    );
    range.on("cancel.daterangepicker", function () {});
    range.on("load.daterangepicker", function () {});
    var drp = $('input[name="daterange2"]').data("daterangepicker");
    // var start = moment().subtract(29, 'days');
    // var end = moment();

    // console.log(drp.startDate.format('DD-MM-YYYY'));
    // console.log(drp.endDate.format('DD-MM-YYYY'));
    initDatatable();
    var base_url = $("body").data("base_url");

    $("body").on("click", "#resetfilter", function (e) {
        e.preventDefault();
        $("#tipo-fecha").prop('checked',false);
        $("#id_proveedor").val("").trigger("change");
        $("#id_secretaria").val("").trigger("change");
        $("#id_tipo_pago").val("").trigger("change");
        $("#periodo_contable").val("").trigger("change");

        $('#daterange2').data('daterangepicker').setEndDate(new Date);
        $('#daterange2').data('daterangepicker').setStartDate(new Date);

        // $("#id_tipo_pago").prop("selectedIndex", 0);
        if (modoReporteActual === "reporte_final") {
            seleccionarPeriodoActualSiCorresponde();
            actualizarOpcionesReporteFinal(cargarReporteFinal);
        } else {
            initDatatable();
        }
        actualizarChipsFiltros();
    });

    $("body").on("click", "#applyfilter", function (e) {
        e.preventDefault();

        if (modoReporteActual === "reporte_final") {
            actualizarOpcionesReporteFinal(cargarReporteFinal);
        } else {
            initDatatable(false, 4);
        }
    });

    $("body").on("click", ".modo-reporte-btn", function (e) {
        e.preventDefault();
        setModoReporte($(this).data("modo"));
    });

    $("body").on("click", "#toggle-filtros-consolidados", function (e) {
        e.preventDefault();
        var $card = $(".filtros-consolidados-card");
        var colapsado = !$card.hasClass("filtros-colapsados");
        $card.toggleClass("filtros-colapsados", colapsado);
        $(this)
            .attr("aria-expanded", colapsado ? "false" : "true")
            .html(colapsado ? '<i class="icon-arrow-down12"></i> Mostrar filtros' : '<i class="icon-arrow-up12"></i> Ocultar filtros');
    });

    $("body").on("change", "#id_proveedor, #id_secretaria, #id_tipo_pago, #periodo_contable, #tipo-fecha, #daterange2", function () {
        aplicarFiltrosConsolidadosDebounced();
    });

    $("body").on("click", "#descargar-reporte-final", function (e) {
        e.preventDefault();
        var params = obtenerFiltrosReporteFinal();
        window.open("/Consolidados/descargar_reporte_final?" + $.param(params), "_blank");
    });

    $("body").on("click", "#descarga-exell", function (e) {
        e.preventDefault();
        $("body .buttons-excel").trigger("click");
    });
    
    // *******************************************************************
// 🚨 NUEVO MANEJADOR ÚNICO: #descarga-principal
// *******************************************************************
$("body").on("click", "#descarga-principal", function (e) {
    e.preventDefault();

    // 1. Recolección y Validación de Parámetros
    var prove = $("#id_proveedor").val();
    var secretaria = $("#id_secretaria").val();
    var tipo_pago_ids = $("#id_tipo_pago").val();
    var periodo_contable = $("#periodo_contable").val();
    var fecha_checked = $("#tipo-fecha").is(":checked");
    
    // Comprueba si AL MENOS UN filtro tiene datos
    var is_filtered = (
        (prove && prove.length > 0) ||
        (secretaria && secretaria.length > 0) ||
        (tipo_pago_ids && tipo_pago_ids.length > 0) ||
        (periodo_contable && periodo_contable.length > 0) ||
        (fecha_checked) 
    );
    
    if (!is_filtered) {
        // Bloquear si no hay filtros aplicados
        Swal.fire({
            icon: 'warning',
            title: 'Filtro Requerido',
            text: 'Para descargar reportes o archivos, debe aplicar al menos un filtro (Proveedor, Secretaria, Tipo de Pago, Periodo o Rango de Fechas).',
            confirmButtonText: 'Entendido'
        });
        return; // Detiene la ejecución si no hay filtros
    }


    // 2. Si hay filtros, inicia la pregunta de descarga
    $.confirm({
        autoClose: 'cancel|10000',
        title: 'Opciones de Descarga',
        content: '¿Desea incluir los archivos PDF en la descarga del reporte?',
        buttons: {
            // Opción SI: Descargar Excel + PDFs (ZIP)
            si: {
                text: 'Sí, Descargar ZIP (Excel + PDFs)',
                btnClass: 'btn-blue',
                action: function () {
                    // 1a. Disparar descarga de Excel
                    $("body .buttons-excel").trigger("click"); 
                    console.log('Descarga de Excel iniciada.');

                    // 1b. Obtener y ejecutar la lógica de descarga de PDFs (la lógica anterior de #descarga-pdfs)
                    
                    // La fecha solo se usa si el checkbox estaba marcado (validado en 'is_filtered')
                    var fecha = fecha_checked ? $("#daterange2").val() : null;
                    
                    var params = {};
                    if (prove && prove.length > 0) {
                        params.id_proveedor = prove;
                    }
                    if (secretaria && secretaria.length > 0) {
                        params.id_secretaria = secretaria;
                    }
                    if (tipo_pago_ids && tipo_pago_ids.length > 0) {
                        var $select = $("#id_tipo_pago");
                        var tipo_pago_text = [];
                        tipo_pago_ids.forEach(function (valor) {
                            tipo_pago_text.push($select.find("option[value=" + valor + "]").text());
                        });
                        params.tipo_pago = tipo_pago_text; 
                    }
                    if (periodo_contable && periodo_contable.length > 0) {
                        params.periodo_contable = periodo_contable;
                    }
                    if (fecha) { 
                        params.fecha = fecha;
                    }
                    
                    var base_url = $("body").data("base_url");
                    // Ajusta la ruta si es necesario (ej. 'Admin/Consolidados/descargar_pdfs')
                    var controller_method = 'Consolidados/descargar_pdfs'; 
                    var url = base_url + controller_method + '?' + $.param(params);

                    window.open(url, '_blank');
                    console.log('Descarga de PDFs (ZIP) iniciada con filtros:', params);
                }
            },
            // Opción NO: Descargar solo Excel
            no: {
                text: 'No, solo Descargar Excel',
                btnClass: 'btn-green',
                action: function () {
                    // Solo dispara la descarga del archivo Excel
                    $("body .buttons-excel").trigger("click");
                    console.log('Solo Descarga de Excel iniciada.');
                }
            },
            cancel: {
                text: 'Cancelar',
                btnClass: 'btn-red',
                action: function () {
                    // No hace nada
                    console.log('Descarga cancelada por el usuario.');
                }
            }
        }
    });
});
    // ======================================================================
    

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
        dato.append("file", file);
        dato.append("lote", lote);
        $.confirm({
            autoClose: "cancel|10000",
            title: "Eliminar Datos y archivos",
            content: "Confirma eliminar datos del archivo : " + file + "  ?",
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
                                // initDatatable();
                            
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
    
    // ----------------------------------------------------------------------
    // ----------------------------------------------------------------------
    // ----------------------------------------------------------------------
    // CÓDIGO FINAL CORREGIDO: SIMULACIÓN DE DOBLE CLIC (SOLUCIÓN DEFINITIVA)
    // ----------------------------------------------------------------------

    // Variables para gestionar el temporizador y el conteo de clics
    var timer = 0;
    var clickCount = 0; 
    var clickDelay = 250; 

    // 1. Limpieza Agresiva: Anulamos CUALQUIER manejador existente.
    $('.datatable-ajax tbody').off('click', 'tr');
    $('.datatable-ajax tbody').off('dblclick', 'tr'); 

    // 2. Manejador ÚNICO de CLIC (simula la lógica de simple y doble)
    $('.datatable-ajax tbody').on('click', 'tr', function (e) {
        
        var self = this;
        clickCount++; 

        if (clickCount === 1) {
            timer = setTimeout(function() {
                // LÓGICA DE CLIC SIMPLE (Selección)
                $(self).closest('.datatable-ajax').find('tbody tr.selected').removeClass('selected');
                $(self).addClass('selected');
                
                console.log('Evento: Clic Simple (Selección)');
                clickCount = 0; 
            }, clickDelay);

        } else if (clickCount === 2) {
            // LÓGICA DE DOBLE CLIC (Redirección)
            
            clearTimeout(timer); 
            
            // 🚨 CAMBIO CRÍTICO: Búsqueda del enlace principal con la URL de redirección
            var $link = $(self).find('a[title="Ver detalles y seguimiento"]'); 
            
            if ($link.length) {
                var url = $link.attr('href');
                window.open(url, '_blank');
                console.log('Evento: Doble Clic SIMULADO (Redirección) a URL:', url);
            } else {
                console.error('Doble Clic: No se encontró el enlace principal para redirección.');
            }

            clickCount = 0;
        }
        
    });

});
