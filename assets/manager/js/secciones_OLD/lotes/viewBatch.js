

$(document).ready(function () {
  dt();
  // $(".datatable-ajax").DataTable().columns.adjust().draw();
  // $(".datatable-ajax").DataTable().responsive.recalc();
  $("body").on("click", "span.mergefile", function (e) {
    var file = $(this).data("file");
    e.preventDefault();

    if ($(this).data("indexador") == '0') {
      $.confirm({
        title: "CONSOLIDAR ARCHIVO",
        content:
          "El archivo: <strong> " +
          file +
          " </strong> No posee indexación",
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
    }else if ($(this).data("consolidado") != '0') {
      $.confirm({
        title: "CONSOLIDAR ARCHIVO",
        content:
          "El archivo: <strong> " +
          file +
          " </strong> Ya se encuentra Consolidado",
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
    } else {
      var dato = new FormData();
      dato.append("code_lote", $(this).data("code"));
      dato.append("id_file", $(this).data("id_file"));
      $.confirm({
        autoClose: "cancel|10000",
        title: "CONSOLIDAR ARCHIVO",
        content: "Confirma la Consolidación ???",
        buttons: {
          confirm: {
            text: "Confirmar",
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
                url: $("body").data("base_url") + "Lecturas/Consolidar",
                success: function (result) {
                  console.log("result");
                  console.log(result);
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
    }
  });
});
function dt() {
$(".datatable-ajax").dataTable().fnDestroy();
  var base_url = $("body").data("base_url");
  var mytable =   $(".datatable-ajax").dataTable({
    
    dom: 'Blfrtip',
    buttons: [
      'colvis'
  ],
    pageLength: 10,
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },

    order: [[0, 'desc']],
    columnDefs: [
      // {className: "d-none", targets:[8]},
      // { className: 'dt-nowrap', targets: [ 8 ] },
      // { width: '1%',visible:false, targets: [ 0 ] },
    
    ],
    
    processing: true,
    serverSide: true,
    responsive: false,
    ajax: {
      data: { table: "_datos_api", id_lote:$("body").data("data_lote") },
      url: "/Admin/Lotes/viewBatch/"+$("body").data("data_lote"),
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
    initComplete: function () {
      this.api()
        .columns()
        .every(function () {
          // var column = this;
          // var select = $(
          //   '<select class="filter-select" data-placeholder="Filter"><option value=""></option></select>'
          // )
          //   .appendTo($(column.footer()).not(":last-child").empty())
          //   .on("change", function () {
          //     var val = $.fn.dataTable.util.escapeRegex($(this).val());

          //     column.search(val ? "^" + val + "$" : "", true, false).draw();
          //   });

          // column
          //   .data()
          //   .unique()
          //   .sort()
          //   .each(function (d, j) {
          //     select.append('<option value="' + d + '">' + d + "</option>");
          //   });
        });
        
    },
  });
}


