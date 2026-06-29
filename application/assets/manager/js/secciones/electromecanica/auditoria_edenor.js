(function ($) {
  "use strict";

  var root = null;
  var periodos = [];

  function htmlEscape(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function badgeTarifa(tarifa) {
    var cls = String(tarifa || "").toLowerCase();
    return '<span class="edenor-badge ' + cls + '">' + htmlEscape(tarifa || "-") + "</span>";
  }

  function shortPeriodo(label) {
    return String(label || "").split(" ")[0] || "Actual";
  }

  function variacionIcon(value) {
    value = parseInt(value || 0, 10);
    if (value > 0) {
      return '<span class="text-success font-weight-bold">&uarr; +' + value + '</span>';
    }
    if (value < 0) {
      return '<span class="text-danger font-weight-bold">&darr; ' + value + '</span>';
    }
    return '<span class="text-primary font-weight-bold">= 0</span>';
  }

  function renderPeriodos() {
    var actual = $("#periodo_actual");
    var base = $("#periodo_base");
    actual.empty();
    base.empty();

    if (!periodos.length) {
      actual.append('<option value="">Sin periodos importados</option>');
      base.append('<option value="">Sin periodo comparable</option>');
      actualizarLinkReporte();
      return;
    }

    base.append('<option value="">Sin periodo comparable</option>');
    periodos.forEach(function (p) {
      actual.append('<option value="' + p.periodo + '">' + htmlEscape(p.label) + "</option>");
      base.append('<option value="' + p.periodo + '">' + htmlEscape(p.label) + "</option>");
    });

    actual.val(periodos[0].periodo);
    seleccionarBaseAnterior();
    actualizarLinkReporte();
  }

  function seleccionarBaseAnterior() {
    var actual = $("#periodo_actual").val();
    var idx = periodos.findIndex(function (p) { return p.periodo === actual; });
    if (idx >= 0 && periodos[idx + 1]) {
      $("#periodo_base").val(periodos[idx + 1].periodo);
    } else {
      $("#periodo_base").val("");
    }
    actualizarLinkReporte();
  }

  function actualizarLinkReporte() {
    var actual = $("#periodo_actual").val();
    var base = $("#periodo_base").val();
    var btn = $("#edenorReporteBtn");

    if (!actual) {
      btn.attr("href", "#").addClass("disabled");
      return;
    }

    var url = $("body").data("base_url") + "Electromecanica/AuditoriaEdenor/reporte"
      + "?periodo_actual=" + encodeURIComponent(actual)
      + "&periodo_base=" + encodeURIComponent(base || "");
    btn.attr("href", url).removeClass("disabled");
  }

  function renderSimpleTable(selector, headers, rows, renderRow) {
    var table = $(selector);
    var html = "<thead><tr>";
    headers.forEach(function (header) {
      html += "<th>" + htmlEscape(header) + "</th>";
    });
    html += "</tr></thead><tbody>";

    if (!rows || !rows.length) {
      html += '<tr><td colspan="' + headers.length + '" class="edenor-empty">Sin datos para mostrar</td></tr>';
    } else {
      rows.forEach(function (row) {
        html += "<tr>" + renderRow(row) + "</tr>";
      });
    }

    html += "</tbody>";
    table.html(html);
  }

  function tarifaRows(resumen) {
    var rows = [];
    Object.keys(resumen || {}).forEach(function (key) {
      rows.push(resumen[key]);
    });
    rows.sort(function (a, b) {
      return String(a.tarifa).localeCompare(String(b.tarifa));
    });
    return rows;
  }

  function renderComparacion(payload) {
    var data = payload.data || {};
    var kpis = data.kpis || {};

    $("#kpi_actual").text(kpis.actual_total || 0);
    $("#kpi_base").text(kpis.base_total || 0);
    $("#kpi_nuevas").text(kpis.nuevas || 0);
    $("#kpi_faltantes").text(kpis.faltantes || 0);
    $("#kpi_bimestrales").text(kpis.bimestrales || 0);
    $("#kpi_tarifa").text(kpis.cambios_tarifa || 0);

    $("#kpi_actual_label").text("Total " + shortPeriodo(data.periodo_actual_label));
    $("#kpi_base_label").text("Total " + shortPeriodo(data.periodo_base_label));
    $("#titulo_resultado_periodos").text("Resultado " + shortPeriodo(data.periodo_actual_label) + " vs " + shortPeriodo(data.periodo_base_label));
    $("#titulo_movimientos").text("Altas, bajas, bimestrales y recategorizadas - " + shortPeriodo(data.periodo_actual_label));

    renderSimpleTable("#tabla_resultado_periodos", ["Tarifa", shortPeriodo(data.periodo_base_label), shortPeriodo(data.periodo_actual_label), "Resultado"], data.tarifas_comparativo || [], function (row) {
      return "<td>" + badgeTarifa(row.tarifa) + "</td><td>" + row.base + "</td><td>" + row.actual + "</td><td>" + variacionIcon(row.variacion) + "</td>";
    });
    renderSimpleTable("#tabla_movimientos", ["Movimiento", "Cuenta", "Tarifa anterior", "Tarifa actual", "Factura"], data.movimientos || [], function (row) {
      var tipo = row.tipo_movimiento || "-";
      var cls = tipo === "Alta" ? "text-success" : (tipo === "Baja" ? "text-danger" : (tipo === "Bimestral" ? "text-warning" : "text-primary"));
      return '<td class="' + cls + ' font-weight-bold">' + htmlEscape(tipo) + "</td><td>" + htmlEscape(row.nro_cuenta) + "</td><td>" + badgeTarifa(row.tarifa_anterior) + "</td><td>" + badgeTarifa(row.tarifa_actual) + "</td><td>" + htmlEscape(row.nro_factura) + "</td>";
    });
  }

  function cargarComparacion() {
    var actual = $("#periodo_actual").val();
    if (!actual) {
      renderComparacion({ data: {} });
      return;
    }

    $.post($("body").data("base_url") + "Electromecanica/AuditoriaEdenor/comparar", {
      periodo_actual: actual,
      periodo_base: $("#periodo_base").val()
    }, function (resp) {
      if (resp.periodos) {
        periodos = resp.periodos;
      }
      renderComparacion(resp);
    }, "json").fail(function () {
      alert("No se pudo cargar la comparacion.");
    });
  }

  function cargarEvolutivo() {
    $.post($("body").data("base_url") + "Electromecanica/AuditoriaEdenor/evolutivo", {}, function (resp) {
      renderSimpleTable("#tabla_evolutivo", ["Periodo", "Total", "AP", "T1", "T2", "T3", "Altas", "Bajas", "Bimestrales", "Recateg."], resp.data || [], function (row) {
        return "<td>" + htmlEscape(row.label) + "</td><td>" + row.total + "</td><td>" + row.AP + "</td><td>" + row.T1 + "</td><td>" + row.T2 + "</td><td>" + row.T3 + "</td><td>" + row.nuevas + "</td><td>" + row.faltantes + "</td><td>" + (row.bimestrales || 0) + "</td><td>" + row.cambios_tarifa + "</td>";
      });
    }, "json").fail(function () {
      alert("No se pudo cargar el evolutivo.");
    });
  }

  function importarArchivo(reemplazar) {
    var form = document.getElementById("edenorImportForm");
    var data = new FormData(form);
    data.append("reemplazar", reemplazar ? "1" : "0");

    $.ajax({
      url: $("body").data("base_url") + "Electromecanica/AuditoriaEdenor/importar",
      method: "POST",
      data: data,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (resp) {
        if (resp.status === "exists") {
          if (confirm(resp.mensaje + "\n\nDesea reemplazarlo?")) {
            importarArchivo(true);
          }
          return;
        }

        if (resp.status !== "success") {
          alert(resp.mensaje || "No se pudo importar el archivo.");
          return;
        }

        periodos = resp.periodos || periodos;
        renderPeriodos();
        $("#periodo_actual").val(resp.periodo);
        seleccionarBaseAnterior();
        cargarComparacion();
        cargarEvolutivo();
        $("#archivo_txt").val("");
      },
      error: function () {
        alert("No se pudo importar el archivo.");
      }
    });
  }

  $(function () {
    root = $("#edenorAuditoria");
    periodos = root.data("periodos") || [];
    renderPeriodos();
    cargarComparacion();
    cargarEvolutivo();

    $("#periodo_actual").on("change", function () {
      seleccionarBaseAnterior();
      cargarComparacion();
    });
    $("#periodo_base").on("change", function () {
      actualizarLinkReporte();
      cargarComparacion();
    });

    $(".edenor-tab").on("click", function () {
      $(".edenor-tab").removeClass("active");
      $(this).addClass("active");
      var tab = $(this).data("tab");
      $("#tab_comparacion").toggle(tab === "comparacion");
      $("#tab_evolutivo").toggle(tab === "evolutivo");
    });

    $("#edenorImportForm").on("submit", function (e) {
      e.preventDefault();
      importarArchivo(false);
    });
  });
})(jQuery);
