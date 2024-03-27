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

      columnDefs: [
        {
          targets: [6],
          //			className: 'dt-body-right',
          bSortable: false,
        },
        {
          // render: function (data, type, row) {
          //   // console.log(row);
          //   return "PROG " + data;
          // },
          // targets: 5,
        },
        {
          // render: function (data, type, row) {
          //   return data + "." + row[5];
          // },
          // targets: 6,
        },
      ],
      // columnDefs: [
      //   {
      //     targets: [0, 1, 2],
      //     visible: false,
      //   },

      //   {
      //     targets: [14, 15, 16, 17, 18],
      //     //			className: 'dt-body-right',
      //     bSortable: false,
      //   },
      //   // { className: "dt-center dt-nowrap", targets: [] },
      //   { targets: ["_all"], className: "dt-left dt-nowrap" },

      //   {
      //     targets: [0, 1, 2],
      //     visible: false,
      //   },
      //   {targets: ["_all"], visible: true} ,
      //   {
      //     targets: ['_aññ'],
      //     render: function (data, type, full, meta) {
      //       console.log('prog');
      //       console.log('render 4')
      //       console.log(data)
      //       return data + " a(" + full[1] + ")";
      //     },
      //     targets: [8],
      //     render: function (data, type, full, meta) {
      //       // console.log('full');
      //       // console.log(full);
      //       punto = ".";
      //       if (full[2] == "") {

      //         punto = "";
      //       }else{
      //         punto = + full[2]
      //       }
      //       return "PROG "+data+full[2];
      //     },
      //     targets: [9],
      //     render: function (data, type, full, meta) {
      //       console.log('progs');

      //       punto = "";
      //       if (full[8] != "") {

      //         punto = "."+full[8];
      //       }else{

      //         punto = '';
      //       }
      //       return +data+punto;
      //     },
      //   },

      //   {
      //     targets: [14, 15, 16, 17, 18],
      //     //			className: 'dt-body-right',
      //     bSortable: false,
      //   },
      //   { className: "dt-center dt-nowrap", targets: [] },
      //   { targets: ["_all"], className: "dt-left dt-nowrap" },
      // ],
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
          table: "_proveedores",
          data_search: search,
          id_proveedor: prove,
        },
        url: "/Proveedores/list_proveedores_dt",
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
  var base_url = $("body").data("base_url");
  initDatatable();

  $("form#form-validate-jquery").validate({
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
