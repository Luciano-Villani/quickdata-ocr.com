(function ($) {
    "use strict";

    var fechaSeleccionada = "";

    function coincideEstado($row, filtro) {
        var estado = String($row.data("estado") || "");
        if (!filtro) {
            return true;
        }
        if (filtro === "vencidas") {
            return String($row.data("vencida")) === "1";
        }
        if (filtro === "sin_actividad") {
            return String($row.data("sin-actividad")) === "1";
        }
        if (filtro === "pendientes_anteriores") {
            return String($row.data("pendiente-anterior")) === "1";
        }
        if (filtro === "vencen_7") {
            return String($row.data("alerta7")) === "1";
        }
        if (filtro === "por_consolidar") {
            return ["subida_pendiente", "vencida_subida"].indexOf(estado) !== -1;
        }
        if (filtro === "subidas") {
            return ["consolidada", "subida_pendiente", "vencida_subida"].indexOf(estado) !== -1;
        }
        if (filtro === "sin_subir") {
            return ["sin_subir", "vencida_sin_subir"].indexOf(estado) !== -1;
        }
        return estado === filtro;
    }

    function formatoFecha(fecha) {
        if (!fecha) {
            return "-";
        }
        var partes = String(fecha).substring(0, 10).split("-");
        if (partes.length !== 3) {
            return fecha;
        }
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }

    function resetFiltrosTabla() {
        $("#filtro_estado_vencimiento").val("");
        $("#filtro_proveedor_vencimiento").val("");
        $("#filtro_pago_vencimiento").val("");
        $("#ocultar_consolidadas").prop("checked", false);
        $("#ocultar_sin_actividad").prop("checked", false);
    }

    function scrollATabla() {
        var $destino = $("#tabla-obligaciones-card");
        if (!$destino.length) {
            return;
        }
        $("html, body").animate({ scrollTop: Math.max(0, $destino.offset().top - 70) }, 350);
    }

    function renderHistorico(idIndexador) {
        var historico = (window.vencimientosHistoricos || {})[idIndexador] || [];
        var chips = '<div class="historial-meses">';
        var tabla = '<table class="table table-sm table-bordered mb-0"><thead><tr><th>Mes</th><th>Estado</th><th>Vencimiento</th><th>Subida</th><th>Consolidada</th><th>Factura</th><th>Importe</th><th></th></tr></thead><tbody>';

        historico.forEach(function (mes) {
            chips += '<div class="historial-mes ' + mes.estado + '"><strong>' + mes.mes_label + '</strong><br><small>' + mes.estado_label + '</small></div>';
            tabla += '<tr>' +
                '<td>' + mes.mes_label + '</td>' +
                '<td>' + mes.estado_label + '</td>' +
                '<td>' + formatoFecha(mes.fecha_vencimiento) + '</td>' +
                '<td>' + formatoFecha(mes.fecha_subida) + '</td>' +
                '<td>' + formatoFecha(mes.fecha_consolidado) + '</td>' +
                '<td>' + (mes.nro_factura || "-") + '</td>' +
                '<td>' + (mes.importe || "-") + '</td>' +
                '<td>' + (mes.url ? '<a class="btn btn-sm btn-outline-primary" href="' + mes.url + '">Ver</a>' : '') + '</td>' +
                '</tr>';
        });

        chips += '</div>';
        tabla += '</tbody></table>';
        return '<div class="p-2"><h6 class="mb-2">Historico anual de la cuenta</h6>' + chips + tabla + '</div>';
    }

    $(function () {
        var params = new URLSearchParams(window.location.search);
        var estadoInicial = params.get("estado") || "";
        var $tabla = $("#vencimientos_dt");
        var table = $tabla.DataTable({
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Todos"]],
            order: [[1, "asc"]],
            dom: "Blfrtip",
            buttons: [{
                extend: "excelHtml5",
                title: "Calendario de vencimientos",
                filename: "calendario_vencimientos",
                text: "Excel",
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7],
                    rows: { search: "applied" },
                    stripHtml: true
                }
            }],
            language: { url: "/assets/manager/js/plugins/tables/translate/spanish.json" }
        });

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== "vencimientos_dt") {
                return true;
            }

            var $row = $(table.row(dataIndex).node());
            var proveedor = String($row.data("proveedor") || "");
            var pago = String($row.data("pago") || "");
            var estadoFiltro = $("#filtro_estado_vencimiento").val();
            var proveedorFiltro = $("#filtro_proveedor_vencimiento").val();
            var pagoFiltro = $("#filtro_pago_vencimiento").val();
            var ocultarConsolidadas = $("#ocultar_consolidadas").is(":checked");
            var ocultarSinActividad = $("#ocultar_sin_actividad").is(":checked");

            if (fechaSeleccionada && String($row.data("fecha")) !== fechaSeleccionada) {
                return false;
            }
            if (ocultarConsolidadas && String($row.data("estado")) === "consolidada") {
                return false;
            }
            if (ocultarSinActividad && String($row.data("sin-actividad")) === "1") {
                return false;
            }
            if (proveedorFiltro && proveedor !== proveedorFiltro) {
                return false;
            }
            if (pagoFiltro && pago !== pagoFiltro) {
                return false;
            }
            return coincideEstado($row, estadoFiltro);
        });

        $("#filtro_estado_vencimiento, #filtro_proveedor_vencimiento, #filtro_pago_vencimiento, #ocultar_consolidadas, #ocultar_sin_actividad").on("change", function () {
            table.draw();
        });

        $(".filtro-kpi").on("click", function () {
            fechaSeleccionada = "";
            $(".cal-dia").removeClass("seleccionado");
            $("#filtro_fecha_label").text("");
            $("#filtro_estado_vencimiento").val($(this).data("filtro-estado")).trigger("change");
        });

        $(".cal-dia").on("click", function () {
            fechaSeleccionada = String($(this).data("fecha") || "");
            resetFiltrosTabla();
            $(".cal-dia").removeClass("seleccionado");
            $(this).addClass("seleccionado");
            $("#filtro_fecha_label").text("Filtro calendario: " + formatoFecha(fechaSeleccionada));
            table.draw();
            scrollATabla();
        });

        $("#ver_mes_completo").on("click", function () {
            fechaSeleccionada = "";
            $(".cal-dia").removeClass("seleccionado");
            $("#filtro_fecha_label").text("");
            table.draw();
        });

        $("#descargar_vencimientos_excel").on("click", function () {
            table.button(".buttons-excel").trigger();
        });

        $tabla.on("click", ".ver-historico", function () {
            var tr = $(this).closest("tr");
            var row = table.row(tr);
            var idIndexador = String(tr.data("id-indexador"));

            if (row.child.isShown()) {
                row.child.hide();
                $(this).text("+");
            } else {
                row.child(renderHistorico(idIndexador)).show();
                $(this).text("-");
            }
        });

        if (estadoInicial) {
            $("#filtro_estado_vencimiento").val(estadoInicial).trigger("change");
            scrollATabla();
        }
    });
}(jQuery));
