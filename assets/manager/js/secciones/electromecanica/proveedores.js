function initDatatable(search = false, type = 0) {
  var prove = false;
  var tipo_pago = false;

  // desde los filtros 4
  if (type == 4) {
    prove = $("#id_proveedor").val();
    tipo_pago = $("#id_tipo_pago").val();
    periodo_contable = $("#periodo_contable").val();

    var $select = $("#id_tipo_pago");
    var value = $select.val();
    var data = [];
    value.forEach(function (valor, indice, array) {
      data[indice] = $select.find("option[value=" + valor + "]").text();
    });
  }

  $("#proveedores_dt").DataTable().destroy();
  var table = $("#proveedores_dt")
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

      language: {
        url: "/assets/manager/js/plugins/tables/translate/spanish.json",
      },
      processing: true,
      serverSide: true,
      // responsive: true,
      type: "POST",
      order: [0, "desc"],
      ordering: true,
      ajax: {
        data: {
          type: type,
          table: "_proveedores_canon",
          data_search: search,
          id_proveedor: prove,
        },
        url: "/Electromecanica/Proveedores/list_dt",
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
              .each(function (d, j) {});
          });
      },
    });
}
$(document).ready(function () {

  	
var validator = $( "form#proveedoresForm" ).validate();
console.log(validator);
validator.reset();

  $("#proveedoresForm").trigger("reset");
  var base_url = $("body").data("base_url");
  initDatatable();

  $("body").on("click", "span.editar_proveedor", function (e) {
    e.preventDefault();
    var dato = new FormData();
    dato.append("id", $(this).data("id_proveedor"));
    dato.append("tabla", $("body").data("tabla"));
    $.ajax({
      type: "POST",
      contentType: false,
      dataType: "json",
      data: dato,
      processData: false,
      cache: false,
      beforeSend: function () {
        $("button.btn-add").addClass("d-none");

        $.blockUI();
      },
      url: $("body").data("base_url") + "Electromecanica/Proveedores/edit",
      success: function (result) {
        $("html, body").animate({ scrollTop: 0 }, "fast");
        if (result.status == "success") {
          $("div#collapseExample").addClass("show");
          $("input[name='id']").val(result.data.id);
          $("input[name='codigo']").val(result.data.codigo);
          $("input[name='detalle_gasto']").val(result.data.detalle_gasto);
          $("input[name='nombre']").val(result.data.nombre);
          $("input[name='objeto_gasto']").val(result.data.objeto_gasto);
          $("input[name='unidad_medida']").val(result.data.unidad_medida);
          $("input[name='urlapi']").val(result.data.urlapi);

          setTimeout(function () {
            $.unblockUI();
          }, 500);
        } else {
          alertas(result);
        }
        // table.ajax.reload(null, false);
      },
      error: function (xhr, errmsg, err) {
        console.log(xhr.status + ": " + xhr.responseText);
      },
    });
  });

  $("body").on("click", "span.borrar_proveedor", function (e) {
    e.preventDefault();
    var dato = new FormData();
    var id = $(this).data("id_proveedor");
    var tabla = $("body").data("tabla");
    dato.append("id", id);
    dato.append("table", tabla);

    $.confirm({
      autoClose: "cancel|10000",
      title: "Eliminar Datos",
      content: "Confirma eliminar el registro??",
      buttons: {
        confirm: {
          text: "Borrar",
          btnClass: "btn-blue",
          action: function () {
            eliminarDatos(dato);
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

  function eliminarDatos(dato) {
    $.ajax({
      type: "POST",
      contentType: false,
      dataType: "json",
      data: dato,
      processData: false,
      cache: false,
      beforeSend: function () {},
      url: $("body").data("base_url") + "Electromecanica/Proveedores/delete",
      success: function (result) {
        alertas(result);
        initDatatable();
        // table.ajax.reload(null, false);
      },
      error: function (xhr, errmsg, err) {
        console.log(xhr.status + ": " + xhr.responseText);
      },
    });
  }
$("form#proveedoresForm").validate({
    submitHandler: function (form) {
      console.log("form");
      console.log(form);
      //  form.resetForm();
      form.submit();
    },
    rules: {
      tipo_pago: {
        required: true,
        min: 1,
      },
      codigo: "required",
      nombre: "required",
      objeto_gasto: "required",
      detalle_gasto: "required",
      unidad_medida: "required",
      urlapi: "required",
    },
    messages: {
      tipo_pago: "Seleccione una opción",
      codigo: "El campo es requerido",
      nombre: "El campo es requerido",
      objeto_gasto: "El campo es requerido",
      detalle_gasto: "El campo es requerido",
      unidad_medida: "El campo es requerido",
      urlapi: "El campo es requerido",
    },
  });

  var mi_tabla = $("#proveedores_dtdd").DataTable({
    dom: "frtip",
    columnDefs: [
      {
        orderable: false,
        width: "100px",
        targets: [0],
      },
      { visible: false, targets: [0] },
    ],
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    serverSide: true,
    type: "POST",

    ajax: {
      url: base_url + "proveedores/list_proveedores_dt",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
    initComplete: function () {
      this.api()
        .columns()
        .every(function () {
          let column = this;
          let title = "titulo";

          // Create input element
          let input = document.createElement("input");
          input.placeholder = title;

          // Event listener for user input
          input.addEventListener("keyup", () => {
            if (column.search() !== this.value) {
              column.search(input.value).draw();
            }
          });
        });
    },
  });
  $("#myInput").on("keyup", function () {
    table.search(this.value).draw();
  });
});
