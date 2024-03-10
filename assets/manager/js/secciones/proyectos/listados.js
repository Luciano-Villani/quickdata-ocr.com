$(document).ready(function () {
  $("body").on("click", "a.edit_dato", function (e) {
    e.preventDefault();

    var dato = new FormData();
    dato.append("id", $(this).data("id"));
    $.ajax({
      type: "POST",
      contentType: false,
      dataType: "json",
      data: dato,
      processData: false,
      cache: false,
      beforeSend: function () {},
      url: $("body").data("base_url") + "Proyectos/edit",
      success: function (result) {
        if (result.status == "success") {
      
          $("div#collapseExample").addClass("show");
          $("input[name='id']").val(result.data.id);
          $("input[name='id_interno']").val(result.data.id_interno);
          $("input[name='descripcion']").val(result.data.descripcion);
          $("select#select_secretaria").val(result.data.id_secretaria).trigger('change');
          $("select#select_programa").removeAttr('disabled');

          setTimeout( function() { 
          $("select#select_programa").val(result.data.id_programa).trigger('change');
        }, 1000);
        $('html, body').animate({ scrollTop: 0 }, 'fast');

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
  $("body").on("click", "a.borrar_dato", function (e) {
    e.preventDefault();

    var dato = new FormData();
    dato.append("id", $(this).data("id"));

    if ($(this).data("estado") != 1) {
      $.confirm({
        autoClose: "cancel|10000",
        title: "Eliminar Datos",
        content: "Confirma eliminar datos ?",
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
                url: $("body").data("base_url") + "Proyectos/delete",
                success: function (result) {
                  alertas(result);
                  table.ajax.reload(null, false);
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
    } else {
      $.confirm({
        title: "Borrar Proyecto",
        content: "El Proyecto posee una indexación",
        buttons: {
          cancel: {
            text: "Cancelar",
            btnClass: "btn-red",
            action: function () {
              return;
            },
          },
        },
      });
    }
  });
  $("#select_programa").attr("disabled", "disabled");
  $("#form-validate-jquery").on(
    "change",
    "select#select_secretaria",
    function () {
      var dato = new FormData();
      dato.append("id", $(this).val());

      $.ajax({
        type: "POST",
        contentType: false,
        //    				dataType: 'json',
        data: dato,
        processData: false,
        cache: false,
        beforeSend: function () {
          $("#select_programa").empty();
        },
        url: $("body").data("base_url") + "Admin/Programas/get_programas",
        success: function (result) {
          var obj = jQuery.parseJSON(result);
          console.log("resultwwwww");
          console.log(Object.keys(obj.data).length);
          console.log(result);

          if (Object.keys(obj.data).length > 0) {
            $("#select_programa").removeAttr("disabled");
            $("#select_programa").append(
              '<option selected value="0">SELECCIONE PROGRAMA</option>'
            );

            $.each(obj.data, function (id, value) {
              $("#select_programa").append(
                '<option value="' +
                  value["id"] +
                  '">' +
                  value["id_interno"] +
                  " - " +
                  value["descripcion"] +
                  "</option>"
              );
            });
          } else {
            $("#select_programa").append(
              '<option selected value="">SIN PROGRAMAS</option>'
            );
            $("#select_programa").attr("disabled", "disabled");
          }

          //    					toastr.success('Registro Editado correctamente!', 'Categorías');
        },
        error: function (xhr, errmsg, err) {
          console.log(xhr.status + ": " + xhr.responseText);
        },
      });
    }
  );

  var base_url = $("body").data("base_url");

  $("form#form-validate-jquery").validate({
    rules: {
      id_secretaria: {
        required: true,
        min: 1,
      },
      id_programa: {
        required: true,
        min: 1,
      },
      id_interno: "required",
      descripcion: "required",
    },
    messages: {
      id_secretaria: "Seleccione una opción",
      id_interno: "El campo es requerido",
      id_programa: "El campo Programa es requerido",
      descripcion: "El campo Proyecto es requerido",
    },
  });
});
function initDatatable(search = false, type = 0) {
  var base_url = $("body").data("base_url");
  table = $("#proyectos_dt").DataTable({
    fixedHeader: {
      header: true,
      // footer: true
    },
    dom: "Blfrtip",
    scrollX: true,
    scrollY: 300,
    scrollCollapse: true,
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "All"],
    ],
    pageLength: 25,
    dom: "Blfrtip",
    columnDefs: [
      { width: "1%", className: "dt-left dt-nowrap", targets: "_all" },
      { width: "1%", orderable: false, targets: [0, 4] },
      // { visible: false, targets: [0] },
    ],
    order: false,
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    type: "POST",
    processing: true,
    serverSide: true,

    ajax: {
      data: {
        type: type,
        table: "_proyectos",
        data_search: search,
      },
      url: base_url + "Proyectos/list_dt",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
  });
}

$(document).ready(function () {
  initDatatable();
  $("#select_dependencia").attr("disabled", "disabled");

  $("#988myProgramForm").on("change", "select#select_secretaria", function () {
    var dato = new FormData();
    dato.append("id", $(this).val());

    $.ajax({
      type: "POST",
      contentType: false,
      //    				dataType: 'json',
      data: dato,
      processData: false,
      cache: false,
      beforeSend: function () {
        $("#select_dependencia ").empty();
      },
      url: $("body").data("base_url") + "Admin/Dependencias/get_dependencias",
      success: function (result) {
        var obj = jQuery.parseJSON(result);
        console.log("resultwwwww");
        console.log(Object.keys(obj.data).length);
        console.log(result);

        if (Object.keys(obj.data).length > 0) {
          $("#select_dependencia").removeAttr("disabled");
          $("#select_dependencia").append(
            '<option selected value="0">SELECCIONE DEPENDENCIA</option>'
          );

          $.each(obj.data, function (id, value) {
            $("#select_dependencia").append(
              '<option value="' +
                value["id"] +
                '">' +
                value["dependencia"] +
                "</option>"
            );
          });
        } else {
          $("#select_dependencia").append(
            '<option selected value="0">SIN DEPENDENCIA</option>'
          );
          $("#select_dependencia").attr("disabled", "disabled");
        }

        //    					toastr.success('Registro Editado correctamente!', 'Categorías');
      },
      error: function (xhr, errmsg, err) {
        console.log(xhr.status + ": " + xhr.responseText);
      },
    });
  });
});
