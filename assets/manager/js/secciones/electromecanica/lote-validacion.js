$(document).ready(function () {
  var loteProcesando = false;
  var loteValidado = false;

  function resetLoteModal() {
    $("#tabla_archivos tbody").html("");
    $("button#enviar_archivos").attr("disabled", "disabled");
    $(".progress").hide();
    $(".progress-bar").css("width", "0%").html("0%");
    $("#lote_total_archivos").text("0");
    $("#lote_procesados").text("0");
    $("#lote_errores_api").text("0");
    $("#lote_porcentaje").text("0%");
    $("#lote_validation_result").hide().removeClass("ok warning danger");
    $("#lote_validation_title, #lote_validation_detail").html("");
    $("#lote_action_errores, #lote_action_sin_index, #lote_action_completo")
      .hide()
      .attr("href", "#");
    loteProcesando = false;
    loteValidado = false;
  }

  function actualizarKpisProceso(total, procesados, erroresApi) {
    var porcentaje = total > 0
      ? Math.round(((procesados + erroresApi) / total) * 100)
      : 0;

    $("#lote_total_archivos").text(total);
    $("#lote_procesados").text(procesados);
    $("#lote_errores_api").text(erroresApi);
    $("#lote_porcentaje").text(porcentaje + "%");
    $(".progress-bar").css("width", porcentaje + "%").html(porcentaje + "%");
  }

  function marcarArchivo(nombre, clase, texto) {
    $("#tabla_archivos tbody")
      .find("span[data-archivo='" + nombre + "']")
      .removeClass("bg-warning-400 bg-info-400 bg-success-400 bg-danger-400")
      .addClass(clase)
      .text(texto);
  }

  function renderValidacionLote(result) {
    var clase = "ok";

    if (result.estado === "incompleto" || result.estado === "vacio") {
      clase = "danger";
    } else if (result.estado === "observaciones") {
      clase = "warning";
    }

    $("#lote_validation_title").html("Resultado del lote");
    $("#lote_validation_detail").html(
      "<div class='lote-validation-grid'>" +
      "<div><strong>" + result.total_archivos + "</strong> archivos del lote</div>" +
      "<div><strong>" + result.archivos_procesados_api + "</strong> con respuesta API</div>" +
      "<div><strong>" + result.archivos_sin_respuesta_api + "</strong> sin respuesta API</div>" +
      "<div><strong>" + result.archivos_error_lectura + "</strong> con errores de lectura</div>" +
      "<div><strong>" + result.archivos_sin_indexar + "</strong> sin indexar</div>" +
      "</div>"
    );

    $("#lote_action_completo").attr("href", result.url_lote).show();

    if (parseInt(result.archivos_error_lectura, 10) > 0) {
      $("#lote_action_errores").attr("href", result.url_errores).show();
    } else {
      $("#lote_action_errores").hide();
    }

    if (parseInt(result.archivos_sin_indexar, 10) > 0) {
      $("#lote_action_sin_index").attr("href", result.url_sin_index).show();
    } else {
      $("#lote_action_sin_index").hide();
    }

    $("#lote_validation_result")
      .removeClass("ok warning danger")
      .addClass(clase)
      .show();
  }

  function validarLoteProcesado(closeIfOk) {
    var codeLote = $("#code").val();
    if (!codeLote) {
      return;
    }

    $("#lote_validation_result")
      .removeClass("ok warning danger")
      .addClass("warning")
      .show();
    $("#lote_validation_title").html("Validando lote...");
    $("#lote_validation_detail").html(
      "Recalculando errores, sin index y estado del lote."
    );
    $("#lote_action_errores, #lote_action_sin_index, #lote_action_completo").hide();

    $.ajax({
      type: "POST",
      dataType: "json",
      data: { code_lote: codeLote },
      url: $("body").data("base_url") + "Electromecanica/Lecturas/validar_lote",
      success: function (result) {
        loteValidado = true;
        loteProcesando = false;
        renderValidacionLote(result);
        $(".datatable-ajax").DataTable().ajax.reload();

        if (closeIfOk && result.estado === "ok") {
          $("#modal_backdrop").modal("hide");
          resetLoteModal();
        }
      },
      error: function (xhr) {
        loteProcesando = false;
        $("#lote_validation_result")
          .removeClass("ok warning")
          .addClass("danger")
          .show();
        $("#lote_validation_title").html("No se pudo validar el lote");
        $("#lote_validation_detail").html(
          "El servidor respondio " + xhr.status + ". Revisar el lote manualmente."
        );
      },
    });
  }

  window.prepararModalProcesamientoLoteCanon = function () {
    actualizarKpisProceso($("#tabla_archivos tbody tr").length, 0, 0);
    $("#lote_validation_result").hide().removeClass("ok warning danger");
    loteProcesando = false;
    loteValidado = false;
  };

  $("#modal_backdrop")
    .off("shown.bs.modal.loteCanon")
    .on("shown.bs.modal.loteCanon", function () {
      window.prepararModalProcesamientoLoteCanon();
    });

  $("body")
    .off("click", "button#cerrar_modal")
    .on("click", "button#cerrar_modal", function (e) {
      e.preventDefault();

      if (loteProcesando) {
        $.confirm({
          title: "Procesamiento en curso",
          content: "Todavia hay archivos procesandose. Si cerras ahora, se validara el lote con lo disponible.",
          buttons: {
            confirm: {
              text: "Validar y cerrar",
              btnClass: "btn-primary",
              action: function () {
                validarLoteProcesado(false);
              },
            },
            cancel: {
              text: "Seguir esperando",
            },
          },
        });
        return;
      }

      if (!loteValidado && $("#tabla_archivos tbody tr").length) {
        validarLoteProcesado(true);
        return;
      }

      $("#modal_backdrop").modal("hide");
      resetLoteModal();
    });

  $("body")
    .off("click", "button#enviar_archivos")
    .on("click", "button#enviar_archivos", function (e) {
      e.preventDefault();
      $("button#enviar_archivos").attr("disabled", "disabled");
      $(".progress").show();
      $("#lote_validation_result").hide().removeClass("ok warning danger");

      var filas = $("#tabla_archivos tbody tr");
      var count = filas.length;
      var processedCount = 0;
      var errorApiCount = 0;
      loteProcesando = true;
      loteValidado = false;
      actualizarKpisProceso(count, processedCount, errorApiCount);

      function cerrarProcesamientoSiTermino() {
        if (processedCount + errorApiCount >= count) {
          loteProcesando = false;
          validarLoteProcesado(false);
        }
      }

      filas.each(function () {
        var nombre = $(this).data("archivo");
        var postdata = new FormData();
        postdata.append("file", nombre);
        postdata.append("id_proveedor", $("#id_proveedor").val());

        $.ajax({
          type: "POST",
          contentType: false,
          cache: false,
          processData: false,
          dataType: "json",
          data: postdata,
          beforeSend: function () {
            marcarArchivo(nombre, "bg-info-400", "Procesando");
          },
          url: $("body").data("base_url") + "Electromecanica/Lecturas/leerApi",
          success: function (result) {
            processedCount++;
            marcarArchivo(result.mensaje, "bg-success-400", "Procesado");
            actualizarKpisProceso(count, processedCount, errorApiCount);
            cerrarProcesamientoSiTermino();
          },
          error: function (xhr) {
            console.log(xhr.status + ": " + xhr.responseText);
            errorApiCount++;
            marcarArchivo(nombre, "bg-danger-400", "Error API");
            actualizarKpisProceso(count, processedCount, errorApiCount);
            cerrarProcesamientoSiTermino();
          },
        });
      });
    });
});

