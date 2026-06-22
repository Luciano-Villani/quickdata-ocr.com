$(document).ready(function () {

  var base_url = $("body").data("base_url");
  var currentFilter = new URLSearchParams(window.location.search).get("filtro") || "";

  $(".batch-filter-buttons [data-filter]").removeClass("btn-primary active").addClass("btn-light");
  $(".batch-filter-buttons [data-filter='" + currentFilter + "']")
    .removeClass("btn-light").addClass("btn-primary active");

  var mytable = $(".datatable-ajax").DataTable({
     select: {
      'style': 'multi',
      'selector': 'tr' // Ajusta el selector si es necesario
    },
    dom: "<'row mx-0 align-items-center'<'col-md-4'B><'col-md-3'l><'col-md-5'f>>rtip",
    buttons: [
      'colvis'
    ],
    pageLength: 10,
    language: {
      select: {
        rows: " %d Registros seleccionados",
      },
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    order: [[1, 'desc']],
    columnDefs: [
      { visible: false, targets: [13] },
      {
        'targets': [9, 11, 12, 13],
        'orderable': false,
      }
    ],
    fixedHeader: {
      header: true,
    },
    autoWidth: false,
    paging: true,
    scrollCollapse: true,
    scrollX: true,
    scrollY: "62vh",
    processing: true,
    serverSide: true,
    responsive: false,
    ajax: {
      data: function (data) {
        data.table = "_datos_api_canon";
        data.id_lote = $("body").data("data_lote");
        data.filtro = currentFilter;
      },
      url: "/Electromecanica/Lecturas/viewBatch/" + $("body").data("data_lote"),
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
    createdRow: function (row, data, dataIndex) {
      // agrego el atributo id al td 0
      console.log('createrow');
      // console.log(data);      // console.log(row);
      // return;
      $(row).attr("id", data[13]);
      $(row).find("td:eq(0)").attr("id", data[13]);
    },
    initComplete: function () {
      $(".dataTables_scrollBody").css("max-height", "none");
      var scrollBody = $(".dataTables_scrollBody");
      scrollBody.off("wheel.dtHorizontal").on("wheel.dtHorizontal", function (e) {
        if (Math.abs(e.originalEvent.deltaY) > Math.abs(e.originalEvent.deltaX)) {
          this.scrollLeft += e.originalEvent.deltaY;
          e.preventDefault();
        }
      });
      this.api()
        .columns()
        .every(function () {
          
        });
    },
  });

  $("body").on("click", ".batch-filter-buttons [data-filter]", function () {
    currentFilter = $(this).data("filter") || "";
    $(".batch-filter-buttons [data-filter]").removeClass("btn-primary active").addClass("btn-light");
    $(this).removeClass("btn-light").addClass("btn-primary active");
    mytable.ajax.reload();
  });

 

  
  $("body").on("click", "span.mergefile", function (e) {
    var file = $(this).data("file");
    var importeCero = parseInt($(this).data("importe-cero"), 10) === 1;
    e.preventDefault();

    if (parseInt($(this).data("error-bloqueante"), 10) > 0) {
      $.confirm({
        title: "CONSOLIDAR ARCHIVO",
        content: "La lectura posee datos criticos faltantes o esta duplicada. Corregila antes de consolidar.",
        buttons: {
          cancel: { text: "Cerrar", btnClass: "btn-red" }
        }
      });
    } else if ($(this).data("indexador") == '0') {
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
      if (importeCero) {
        dato.append("permitir_importe_cero", "1");
      }
      $.confirm({
        autoClose: "cancel|10000",
        title: importeCero ? "CONFIRMAR IMPORTE 0.00" : "CONSOLIDAR ARCHIVO",
        content: importeCero
          ? "Esta intentando consolidar una factura con importe 0.00. ¿Desea continuar?"
          : "¿Confirma la consolidación?",
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
                url: $("body").data("base_url") + "Electromecanica/Lecturas/Consolidar",
                success: function (result) {
                  alertas(result);
                  $( ".datatable-ajax").DataTable().ajax.reload()
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

  $("body").on("click", "span.borrar-file", function (e) {
    e.preventDefault();
    var dato = new FormData();
    var id = $(this).data("id_file");
    var tabla = $(this).data("tabla");
    dato.append("id", id);
    dato.append("tabla", tabla);
    dato.append("campo", "id");
    dato.append("deletefile", true);
    $.confirm({
      autoClose: "cancel|10000",
      title: "Eliminar Datos",
      content: "Confirma eliminar el archivo y sus datos ?",
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
              url: $("body").data("base_url") + "Electromecanica/Lecturas/deletefile",
              success: function (result) {
                alertas(result);
               console.log('Borrado');
               $( ".datatable-ajax").DataTable().ajax.reload()
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
  

});



var Elemento_Module = {
	attrs: {
		publicaciones: [],

	},
	methods: function () {



		function checkAvailableListPublicaciones(id) {
			return Elemento_Module.attrs.publicaciones.includes(id) ? false : true;
		}

		function getAll() {
			return Elemento_Module.attrs.publicaciones;
		}

		function countList() {
			return Elemento_Module.attrs.publicaciones.length;
		}

		function display() {
			$('.publicaciones_seleccionadas').text(this.countList());
			console.log('[array publicaciones] - Lista de ids', this.getAll());
		}

		function onChangeNumberTextSelect() {
			$('.publicaciones_seleccionadas').text(this.countList());
		}

		function actionsButtons() {

			$('.remove-all-Publicaciones').click(function (e) {
				e.preventDefault();

				swal({
					title: 'Eliminar Publicaciones',
					text: "¿Esta seguro desea eliminar la(s) persona(s) seleccionada(s)?",
					type: 'error',
					showCancelButton: true,
					confirmButtonText: 'Si, eliminar',
					cancelButtonText: 'No, cancelar!',
					confirmButtonClass: 'btn btn-success',
					cancelButtonClass: 'btn btn-danger m-l-10',
					buttonsStyling: false
				}).then(function () {

					Elemento_Module.methods().multiDeletes();
				}, function () {
					swal("Cancelado!", "Operación cancelada por el usuario!", "error");
				});
			});
		}



		function insert(id) {

			if (this.checkAvailableListPublicaciones(id)) {
				Elemento_Module.attrs.publicaciones.push(id);
			}

			this.display();
			this.refresh();
		}

		function remove(id) {

			Elemento_Module.attrs.publicaciones.splice(Elemento_Module.attrs.publicaciones.indexOf(id), 1);

			this.display();
			this.refresh();
		}

		function refresh() {

			if (Elemento_Module.attrs.publicaciones.length) {

				this.onChangeNumberTextSelect();
			} else {

			}

			$('.publicaciones_seleccionadas').html(this.countList());
		}

		function clearAll() {
			Elemento_Module.attrs.publicaciones = [];
			this.display();
		}

		function clearAndRefresh() {
			Elemento_Module.methods().clearAll();

			Elemento_Module.methods().refresh();
		}

		function onAllSelectionPublicaciones() {
			this.clearAll();

		}

		function fetchIds(ids) {
			this.clearAndRefresh();

			$('.publicaciones_seleccionadas').html("");

			ids.map(r => Elemento_Module.methods().insert(r));
		}

		return {
			fetchIds: fetchIds,
			insert: insert,
			remove: remove,
			getAll: getAll,
			display: display,
			refresh: refresh,
			countList: countList,
			clearAll: clearAll,
			clearAndRefresh: clearAndRefresh,
			checkAvailableListPublicaciones: checkAvailableListPublicaciones,
			onChangeNumberTextSelect: onChangeNumberTextSelect,
			onAllSelectionPublicaciones: onAllSelectionPublicaciones
		};
	},
	init: function () {
		console.log('Module Publicaciones Loaded');

	}
};

$(document).ready(function () {

  $( ".datatable-ajax").DataTable().on('select', function (e, dt, type, indexes) {

		if (type === 'row') {

			var data =$(".datatable-ajax").DataTable().rows(indexes).data('id');
			console.log('sdata');
			console.table(data);
			Elemento_Module.methods().insert(parseInt(data[0][13]));

		}
	});

	$( ".datatable-ajax").DataTable().on('deselect', function (e, dt, type, indexes) {
		if (type === 'row') {
			var data = $( ".datatable-ajax").DataTable().rows(indexes).data('id');
			Elemento_Module.methods().remove(parseInt(data[0][13]));

		}
	});

	$("#selectAllPost").on("click", function (e) {

		if ($(this).is(":checked")) {
			$( ".datatable-ajax").DataTable().rows().select();
			$( ".datatable-ajax").DataTable().rows().iterator('row', function (context, index) {

				$(this.row(index).node()).hasClass('selected');
				var id = $(this.row(index).node()).attr('id');
				console.log('->' + $(this.row(index).node()).attr('id'));

				Elemento_Module.methods().insert(parseInt(id));

			});
		} else {

			$( ".datatable-ajax").DataTable().rows().deselect();
			Elemento_Module.methods().clearAndRefresh();
			Elemento_Module.methods().countList();
		}
	});
}); 
