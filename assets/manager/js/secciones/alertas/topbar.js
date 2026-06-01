(function ($) {
    "use strict";

    function getModulo() {
        return window.location.pathname.toLowerCase().indexOf("/electromecanica") === 0
            ? "electromecanica"
            : "proveedores";
    }

    function getUrl(modulo) {
        var config = window.MVL_ALERTAS_CONFIG || {};
        return modulo === "electromecanica" ? config.electroUrl : config.proveedoresUrl;
    }

    function storageKey(modulo) {
        return "mvl_alertas_vencimientos_vistas_" + modulo;
    }

    function cacheKey(modulo) {
        return "mvl_alertas_vencimientos_cache_" + modulo;
    }

    function getSessionValue(key) {
        try {
            return window.sessionStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    function setSessionValue(key, value) {
        try {
            window.sessionStorage.setItem(key, value);
        } catch (e) {
            return false;
        }
        return true;
    }

    function getCache(modulo) {
        try {
            return JSON.parse(window.sessionStorage.getItem(cacheKey(modulo)) || "null");
        } catch (e) {
            return null;
        }
    }

    function setCache(modulo, data) {
        try {
            window.sessionStorage.setItem(cacheKey(modulo), JSON.stringify({
                total: parseInt(data.total || 0, 10),
                firma: data.firma || "",
                timestamp: Date.now()
            }));
        } catch (e) {
            return false;
        }
        return true;
    }

    function renderAlertas(data) {
        if (!data || parseInt(data.total, 10) <= 0) {
            return '<div class="p-3 text-center text-muted">No hay vencimientos pendientes.</div>' +
                '<div class="dropdown-divider"></div>' +
                '<a href="' + (data ? data.url_calendario : "#") + '" class="dropdown-item"><i class="icon-calendar mr-2"></i> Abrir calendario</a>';
        }

        return '' +
            '<div class="p-3">' +
                '<div class="alert alert-danger mb-2 py-2">' +
                    '<strong>' + data.vencidas + '</strong> facturas vencidas' +
                '</div>' +
                '<div class="alert alert-warning mb-2 py-2">' +
                    '<strong>' + data.vencen_7 + '</strong> facturas vencen en 7 dias o menos' +
                '</div>' +
                '<small class="text-muted">Ultima revision: ' + data.actualizado + '</small>' +
            '</div>' +
            '<div class="dropdown-divider"></div>' +
            '<a href="' + data.url_vencidas + '" class="dropdown-item"><i class="icon-warning2 text-danger mr-2"></i> Ver vencidas</a>' +
            '<a href="' + data.url_vencen_7 + '" class="dropdown-item"><i class="icon-calendar text-warning mr-2"></i> Ver proximas 7 dias</a>' +
            '<a href="' + data.url_calendario + '" class="dropdown-item"><i class="icon-calendar5 mr-2"></i> Abrir calendario completo</a>';
    }

    function marcarVisto(modulo, firma) {
        if (firma) {
            setSessionValue(storageKey(modulo), firma);
        }
        $("#dropdown_alertas_vencimientos").removeClass("mvl-alert-pulse");
    }

    function aplicarEstadoVisual(modulo, data) {
        var total = parseInt(data.total || 0, 10);
        var $badge = $("#badge-alertas-vencimientos");
        var $dropdown = $("#dropdown_alertas_vencimientos");

        $badge.text(total);
        if (total > 0) {
            $badge.show();
        } else {
            $badge.hide();
        }

        if (total > 0 && getSessionValue(storageKey(modulo)) !== data.firma) {
            $dropdown.addClass("mvl-alert-pulse");
        } else {
            $dropdown.removeClass("mvl-alert-pulse");
        }
    }

    function cargarAlertas() {
        var modulo = getModulo();
        var url = getUrl(modulo);
        if (!url || !$("#dropdown_alertas_vencimientos").length) {
            return;
        }

        var cache = getCache(modulo);
        if (cache && Date.now() - cache.timestamp < 300000) {
            aplicarEstadoVisual(modulo, cache);
        }

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (!response || response.status !== "success") {
                    $("#alertas-vencimientos-content").html('<div class="p-3 text-center text-danger">No se pudieron cargar las alertas.</div>');
                    return;
                }

                var data = response.data || {};
                $("#alertas-vencimientos-content").html(renderAlertas(data));
                setCache(modulo, data);
                aplicarEstadoVisual(modulo, data);

                $("#dropdown_alertas_vencimientos, #marcar_alertas_vistas").off("click.mvlAlertas").on("click.mvlAlertas", function () {
                    marcarVisto(modulo, data.firma);
                });
            },
            error: function () {
                $("#badge-alertas-vencimientos").hide();
                $("#alertas-vencimientos-content").html('<div class="p-3 text-center text-danger">Error de conexion al cargar alertas.</div>');
            }
        });
    }

    $(function () {
        cargarAlertas();
    });
}(jQuery));
