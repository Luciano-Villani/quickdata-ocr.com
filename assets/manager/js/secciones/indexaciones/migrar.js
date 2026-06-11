$(document).ready(function () {
  var baseUrl = $("body").data("base_url") || "/";
  var cuentaActual = null;
  var cuentasDependencia = [];

  $(".select2").select2({ width: "100%" });

  function showAlert(type, message) {
    var alert = $("#migrar_alert");
    alert
      .removeClass("migrar-hidden alert-success alert-danger alert-warning alert-info")
      .addClass("alert-" + type)
      .text(message);
  }

  function clearAlert() {
    $("#migrar_alert")
      .addClass("migrar-hidden")
      .removeClass("alert-success alert-danger alert-warning alert-info")
      .text("");
  }

  function textOrDash(value) {
    return value && String(value).trim() !== "" ? value : "-";
  }

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function structureText(item, fieldCode, fieldName) {
    var code = item[fieldCode] || "";
    var name = item[fieldName] || "";
    return textOrDash((code + " " + name).trim());
  }

  function resetDestino() {
    $("#migrar_secretaria").val("0").trigger("change.select2");
    $("#migrar_programa")
      .empty()
      .append('<option value="">Seleccione programa</option>')
      .prop("disabled", true)
      .trigger("change.select2");
    $("#migrar_proyecto")
      .empty()
      .append('<option value="0">Sin proyecto</option>')
      .prop("disabled", true)
      .trigger("change.select2");
    $("#migrar_dependencia")
      .empty()
      .append('<option value="">Seleccione dependencia</option>')
      .prop("disabled", true)
      .trigger("change.select2");
    $("#mover_dependencia_actual").prop("checked", false);
    $("#mover_dependencia_wrap").addClass("migrar-hidden");
    $("#btn_guardar_migracion").prop("disabled", true);
    $("#migrar_resumen").hide().empty();
  }

  function setActual(data) {
    cuentaActual = data.indexacion;
    cuentasDependencia = data.cuentas_dependencia || [];

    $("#id_indexacion_actual").val(cuentaActual.id);
    $("#id_dependencia_actual").val(cuentaActual.id_dependencia || "");
    $("#id_secretaria_actual").val(cuentaActual.id_secretaria || "");

    $("#actual_proveedor").text(textOrDash(cuentaActual.proveedor_nombre));
    $("#actual_cuenta").text(textOrDash(cuentaActual.nro_cuenta));
    $("#actual_tipo_pago").text(textOrDash(cuentaActual.tipo_pago_nombre));
    $("#actual_expediente").text(textOrDash(cuentaActual.expediente));
    $("#actual_secretaria").text(textOrDash(cuentaActual.secretaria_nombre));
    $("#actual_programa").text(structureText(cuentaActual, "programa_codigo", "programa_nombre"));
    $("#actual_proyecto").text(structureText(cuentaActual, "proyecto_codigo", "proyecto_nombre"));
    $("#actual_dependencia").text(
      textOrDash(
        [
          cuentaActual.dependencia_nombre || "",
          cuentaActual.dependencia_direccion ? " - " + cuentaActual.dependencia_direccion : "",
        ].join("")
      )
    );

    $("#total_cuentas_dependencia").text(cuentasDependencia.length);
    renderCuentasDependencia();
    $("#migrar_panel").removeClass("migrar-hidden");
    resetDestino();
    loadHistorial();
  }

  function structureLabel(row, prefix) {
    var isNew = prefix === "nuevo";
    var secretaria = row[isNew ? "secretaria_nueva_nombre" : "secretaria_anterior_nombre"] || "-";
    var programaCodigo = row[isNew ? "programa_nuevo_codigo" : "programa_anterior_codigo"] || "";
    var programaNombre = row[isNew ? "programa_nuevo_nombre" : "programa_anterior_nombre"] || "";
    var proyectoCodigo = row[isNew ? "proyecto_nuevo_codigo" : "proyecto_anterior_codigo"] || "";
    var proyectoNombre = row[isNew ? "proyecto_nuevo_nombre" : "proyecto_anterior_nombre"] || "";
    var dependencia = row[isNew ? "dependencia_nueva_nombre" : "dependencia_anterior_nombre"] || "-";
    var programa = $.trim(programaCodigo + " " + programaNombre) || "-";
    var proyecto = $.trim(proyectoCodigo + " " + proyectoNombre) || "Sin proyecto";

    return (
      "<strong>" +
      escapeHtml(secretaria) +
      "</strong><br><span class=\"migrar-muted\">Prog: " +
      escapeHtml(programa) +
      "<br>Proy: " +
      escapeHtml(proyecto) +
      "<br>Dep: " +
      escapeHtml(dependencia) +
      "</span>"
    );
  }

  function renderHistorial(rows) {
    var body = $("#migrar_historial_body");
    body.empty();

    if (!rows || !rows.length) {
      $("#migrar_historial_wrap").addClass("migrar-hidden");
      $("#migrar_historial_empty").removeClass("migrar-hidden").text("La cuenta no registra migraciones.");
      return;
    }

    $.each(rows, function (_, row) {
      var reverted = parseInt(row.revertida || 0, 10) === 1;
      var status = reverted
        ? '<span class="migrar-status migrar-status-reverted">Revertida</span>'
        : '<span class="migrar-status migrar-status-active">Vigente</span>';
      var action = reverted
        ? '<span class="migrar-muted">-</span>'
        : '<button type="button" class="btn btn-outline-danger btn-sm btn-revertir-migracion" data-id="' +
          row.id +
          '"><i class="icon-undo2"></i> Revertir</button>';

      body.append(
        "<tr>" +
          "<td>" +
          escapeHtml(row.fecha_add || "-") +
          "</td>" +
          "<td><strong>" +
          escapeHtml(row.nro_cuenta || "-") +
          "</strong><br><span class=\"migrar-muted\">" +
          escapeHtml(row.proveedor_nombre || "") +
          "</span></td>" +
          "<td>" +
          escapeHtml(row.alcance || "-") +
          (parseInt(row.mover_dependencia || 0, 10) === 1 ? '<br><span class="badge badge-info">Movio dependencia</span>' : "") +
          "</td>" +
          "<td>" +
          structureLabel(row, "anterior") +
          "</td>" +
          "<td>" +
          structureLabel(row, "nuevo") +
          "</td>" +
          "<td>" +
          escapeHtml(row.observacion || "-") +
          (reverted && row.observacion_reversion
            ? '<hr class="my-1"><small><strong>Reversion:</strong> ' + escapeHtml(row.observacion_reversion) + "</small>"
            : "") +
          "</td>" +
          "<td>" +
          status +
          (reverted && row.fecha_reversion ? '<br><small class="migrar-muted">' + escapeHtml(row.fecha_reversion) + "</small>" : "") +
          "</td>" +
          '<td class="text-center">' +
          action +
          "</td>" +
          "</tr>"
      );
    });

    $("#migrar_historial_empty").addClass("migrar-hidden");
    $("#migrar_historial_wrap").removeClass("migrar-hidden");
  }

  function loadHistorial() {
    if (!cuentaActual) {
      renderHistorial([]);
      return;
    }

    $.ajax({
      url: baseUrl + "Admin/Indexaciones/historial_migracion",
      type: "POST",
      dataType: "json",
      data: {
        id_indexacion: cuentaActual.id,
        nro_cuenta: cuentaActual.nro_cuenta,
      },
      success: function (result) {
        if (result.status !== "success") {
          $("#migrar_historial_wrap").addClass("migrar-hidden");
          $("#migrar_historial_empty")
            .removeClass("migrar-hidden")
            .text(result.mensaje || "No se pudo cargar el historial.");
          return;
        }
        renderHistorial((result.data || {}).historial || []);
      },
      error: function () {
        $("#migrar_historial_wrap").addClass("migrar-hidden");
        $("#migrar_historial_empty").removeClass("migrar-hidden").text("Error cargando historial.");
      },
    });
  }

  function renderCuentasDependencia() {
    var box = $("#lista_cuentas_dependencia");
    if ($("input[name='alcance_migracion']:checked").val() !== "dependencia") {
      box.addClass("migrar-hidden").empty();
      return;
    }

    if (!cuentasDependencia.length) {
      box.removeClass("migrar-hidden").html("<em>No hay otras cuentas asociadas.</em>");
      return;
    }

    var html = cuentasDependencia
      .map(function (item) {
        return (
          '<div class="mb-1"><strong>' +
          textOrDash(item.nro_cuenta) +
          "</strong> <span class=\"migrar-muted\">" +
          textOrDash(item.proveedor_nombre) +
          "</span></div>"
        );
      })
      .join("");
    box.removeClass("migrar-hidden").html(html);
  }

  function fillSelect(select, placeholder, rows, formatter, emptyText, emptyValue) {
    var target = $(select);
    target.empty();
    if (!rows || !rows.length) {
      target.append('<option value="' + (emptyValue || "") + '">' + emptyText + "</option>");
      target.prop("disabled", true).trigger("change.select2");
      return;
    }

    target.append('<option value="">' + placeholder + "</option>");
    $.each(rows, function (_, item) {
      target.append('<option value="' + item.id + '">' + formatter(item) + "</option>");
    });
    target.prop("disabled", false).trigger("change.select2");
  }

  function loadOptions(idSecretaria, idPrograma) {
    return $.ajax({
      url: baseUrl + "Admin/Indexaciones/opciones_migracion",
      type: "POST",
      dataType: "json",
      data: {
        id_secretaria: idSecretaria || 0,
        id_programa: idPrograma || 0,
      },
    });
  }

  function updateMoverDependenciaVisibility() {
    var secretariaDestino = parseInt($("#migrar_secretaria").val() || 0, 10);
    var secretariaActual = parseInt($("#id_secretaria_actual").val() || 0, 10);
    if (cuentaActual && secretariaDestino > 0 && secretariaDestino !== secretariaActual) {
      $("#mover_dependencia_wrap").removeClass("migrar-hidden");
    } else {
      $("#mover_dependencia_actual").prop("checked", false);
      $("#mover_dependencia_wrap").addClass("migrar-hidden");
    }
    updateDependenciaState();
  }

  function updateDependenciaState() {
    if ($("#mover_dependencia_actual").is(":checked")) {
      $("input[name='alcance_migracion'][value='dependencia']").prop("checked", true);
      renderCuentasDependencia();
      $("#migrar_dependencia").prop("disabled", true).trigger("change.select2");
    } else if ($("#migrar_dependencia option").length > 1) {
      $("#migrar_dependencia").prop("disabled", false).trigger("change.select2");
    }
    updateResumen();
  }

  function selectedText(selector) {
    var text = $(selector).find("option:selected").text();
    return text && text.trim() ? text.trim() : "-";
  }

  function canSave() {
    if (!cuentaActual) return false;
    if (parseInt($("#migrar_secretaria").val() || 0, 10) <= 0) return false;
    if (parseInt($("#migrar_programa").val() || 0, 10) <= 0) return false;
    if (!$("#mover_dependencia_actual").is(":checked") && parseInt($("#migrar_dependencia").val() || 0, 10) <= 0) return false;
    return true;
  }

  function updateResumen() {
    if (!cuentaActual) {
      $("#migrar_resumen").hide().empty();
      $("#btn_guardar_migracion").prop("disabled", true);
      return;
    }

    var ok = canSave();
    $("#btn_guardar_migracion").prop("disabled", !ok);

    if (!ok) {
      $("#migrar_resumen").hide().empty();
      return;
    }

    var alcance = $("input[name='alcance_migracion']:checked").val();
    var cantidad = alcance === "dependencia" ? cuentasDependencia.length : 1;
    var dependenciaDestino = $("#mover_dependencia_actual").is(":checked")
      ? $("#actual_dependencia").text() + " (se mueve a la nueva secretaría)"
      : selectedText("#migrar_dependencia");

    $("#migrar_resumen")
      .html(
        "<strong>Resumen:</strong> se migrará" +
          (cantidad > 1 ? "n " : " ") +
          "<strong>" +
          cantidad +
          "</strong> cuenta" +
          (cantidad > 1 ? "s" : "") +
          " hacia <strong>" +
          selectedText("#migrar_secretaria") +
          "</strong>, programa <strong>" +
          selectedText("#migrar_programa") +
          "</strong>, proyecto <strong>" +
          selectedText("#migrar_proyecto") +
          "</strong>, dependencia <strong>" +
          dependenciaDestino +
          "</strong>. No se modifican consolidados históricos."
      )
      .show();
  }

  $("#btn_buscar_migracion").on("click", function () {
    clearAlert();
    var nroCuenta = $.trim($("#migrar_nro_cuenta").val());
    if (!nroCuenta) {
      showAlert("warning", "Ingrese un número de cuenta.");
      return;
    }

    $.ajax({
      url: baseUrl + "Admin/Indexaciones/buscar_migracion",
      type: "POST",
      dataType: "json",
      data: { nro_cuenta: nroCuenta },
      beforeSend: function () {
        $("#btn_buscar_migracion").prop("disabled", true).text("Buscando...");
      },
      success: function (result) {
        if (result.status !== "success") {
          $("#migrar_panel").addClass("migrar-hidden");
          showAlert("danger", result.mensaje || "No se pudo buscar la cuenta.");
          return;
        }
        setActual(result.data);
        showAlert("success", "Cuenta encontrada. Revise la estructura actual y seleccione el destino.");
      },
      error: function () {
        showAlert("danger", "Error buscando la cuenta.");
      },
      complete: function () {
        $("#btn_buscar_migracion").prop("disabled", false).html('<i class="icon-search4"></i> Buscar cuenta');
      },
    });
  });

  $("#migrar_nro_cuenta").on("keypress", function (e) {
    if (e.which === 13) {
      $("#btn_buscar_migracion").trigger("click");
    }
  });

  $("input[name='alcance_migracion']").on("change", function () {
    renderCuentasDependencia();
    updateResumen();
  });

  $("#migrar_secretaria").on("change", function () {
    var idSecretaria = parseInt($(this).val() || 0, 10);
    $("#migrar_programa").empty().append('<option value="">Cargando programas...</option>').prop("disabled", true).trigger("change.select2");
    $("#migrar_dependencia").empty().append('<option value="">Cargando dependencias...</option>').prop("disabled", true).trigger("change.select2");
    $("#migrar_proyecto").empty().append('<option value="0">Sin proyecto</option>').prop("disabled", true).trigger("change.select2");

    if (idSecretaria <= 0) {
      resetDestino();
      return;
    }

    loadOptions(idSecretaria, 0).done(function (result) {
      var data = result.data || {};
      fillSelect(
        "#migrar_programa",
        "Seleccione programa",
        data.programas || [],
        function (item) {
          return item.id_interno + " - " + String(item.descripcion || "").toUpperCase();
        },
        "Sin programas",
        ""
      );
      fillSelect(
        "#migrar_dependencia",
        "Seleccione dependencia",
        data.dependencias || [],
        function (item) {
          var direccion = item.direccion ? " - " + item.direccion : "";
          return String(item.dependencia || "").toUpperCase() + direccion;
        },
        "Sin dependencias",
        ""
      );
      updateMoverDependenciaVisibility();
    });
  });

  $("#migrar_programa").on("change", function () {
    var idPrograma = parseInt($(this).val() || 0, 10);
    var idSecretaria = parseInt($("#migrar_secretaria").val() || 0, 10);
    $("#migrar_proyecto").empty().append('<option value="0">Sin proyecto</option>').prop("disabled", true).trigger("change.select2");

    if (idPrograma <= 0) {
      updateResumen();
      return;
    }

    loadOptions(idSecretaria, idPrograma).done(function (result) {
      var data = result.data || {};
      fillSelect(
        "#migrar_proyecto",
        "Seleccione proyecto",
        data.proyectos || [],
        function (item) {
          return item.id_interno + " - " + String(item.descripcion || "").toUpperCase();
        },
        "Sin proyecto",
        "0"
      );
      if (!data.proyectos || !data.proyectos.length) {
        $("#migrar_proyecto").prop("disabled", false).trigger("change.select2");
      }
      updateResumen();
    });
  });

  $("#migrar_proyecto, #migrar_dependencia, #mover_dependencia_actual").on("change", function () {
    updateDependenciaState();
  });

  $("#btn_guardar_migracion").on("click", function () {
    if (!canSave()) {
      showAlert("warning", "Complete la estructura destino antes de confirmar.");
      return;
    }

    var alcance = $("input[name='alcance_migracion']:checked").val();
    var cantidad = alcance === "dependencia" ? cuentasDependencia.length : 1;
    var message =
      "Confirma migrar " +
      cantidad +
      " cuenta" +
      (cantidad > 1 ? "s" : "") +
      "? Esta acción no modifica consolidados históricos.";

    if (!window.confirm(message)) {
      return;
    }

    $.ajax({
      url: baseUrl + "Admin/Indexaciones/guardar_migracion",
      type: "POST",
      dataType: "json",
      data: {
        id_indexacion: $("#id_indexacion_actual").val(),
        alcance: alcance,
        id_secretaria: $("#migrar_secretaria").val(),
        id_programa: $("#migrar_programa").val(),
        id_proyecto: $("#migrar_proyecto").val() || 0,
        id_dependencia: $("#migrar_dependencia").val(),
        mover_dependencia: $("#mover_dependencia_actual").is(":checked") ? 1 : 0,
        observacion: $("#migrar_observacion").val(),
      },
      beforeSend: function () {
        $("#btn_guardar_migracion").prop("disabled", true).text("Guardando...");
      },
      success: function (result) {
        if (result.status !== "success") {
          showAlert("danger", result.mensaje || "No se pudo guardar la migración.");
          updateResumen();
          return;
        }

        showAlert("success", result.mensaje + " Cuentas afectadas: " + result.data.cuentas_afectadas + ".");
        $("#btn_buscar_migracion").trigger("click");
      },
      error: function () {
        showAlert("danger", "Error guardando la migración.");
        updateResumen();
      },
      complete: function () {
        $("#btn_guardar_migracion").html('<i class="icon-checkmark4"></i> Confirmar migración');
      },
    });
  });

  $("#btn_refrescar_historial").on("click", function () {
    loadHistorial();
  });

  $(document).on("click", ".btn-revertir-migracion", function () {
    $("#revertir_id_migracion").val($(this).data("id"));
    $("#revertir_observacion").val("");
    $("#modal_revertir_migracion").modal("show");
  });

  $("#btn_confirmar_reversion").on("click", function () {
    var idMigracion = $("#revertir_id_migracion").val();
    if (!idMigracion) return;

    $.ajax({
      url: baseUrl + "Admin/Indexaciones/revertir_migracion",
      type: "POST",
      dataType: "json",
      data: {
        id_migracion: idMigracion,
        observacion: $("#revertir_observacion").val(),
      },
      beforeSend: function () {
        $("#btn_confirmar_reversion").prop("disabled", true).text("Revirtiendo...");
      },
      success: function (result) {
        if (result.status !== "success") {
          showAlert("danger", result.mensaje || "No se pudo revertir la migracion.");
          return;
        }
        $("#modal_revertir_migracion").modal("hide");
        showAlert("success", result.mensaje + " Cuentas afectadas: " + result.data.cuentas_afectadas + ".");
        $("#btn_buscar_migracion").trigger("click");
      },
      error: function () {
        showAlert("danger", "Error revirtiendo la migracion.");
      },
      complete: function () {
        $("#btn_confirmar_reversion").prop("disabled", false).html('<i class="icon-undo2"></i> Revertir migracion');
      },
    });
  });
});
