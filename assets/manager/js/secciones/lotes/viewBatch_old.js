

$(document).ready(function () {

  var base_url = $("body").data("base_url");

  var mytable =   $(".datatable-ajax").dataTable({
    select: true,
    select: {
      'style': 'multi',
      'selector': 'td:first-child'
    },
    dom: 'Blfrtip',
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
      {visible:false,targets:[13]},
      {
        'targets': 0,
        'visible':false,
        'checkboxes': {
          'selectRow': true,
        },
        orderable: false,
        // render:function(data, type, row, meta){
        //   console.log(data);

        // },
        }
      
      // {className: "d-none", targets:[8]},
      // { className: 'dt-nowrap', targets: [ 8 ] },
      // { width: '1%',visible:false, targets: [ 0 ] },
    
    ],
 
    fixedHeader: {
      header: true,
    },
    autoWidth: false,
    paging: true,
    scrollCollapse: true,
    scrollX: true,
    scrollY: 600,
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
    createdRow: function (row, data, dataIndex) {
      // agrego el atributo id al td 0
      console.log('createrow');
      // console.log(data);      // console.log(row);
      // return;
      $(row).attr("id", data[13]);
      $(row).find("td:eq(0)").attr("id", data[13]);
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
  // $(".datatable-ajax").DataTable().columns.adjust().draw();
  // $(".datatable-ajax").DataTable().responsive.recalc();

  mytable.on( 'select', function ( e, dt, type, indexes ) {
  console.log(dt);
    if ( type === 'row' ) {
        var x = table.rows().data().pluck('Stek');
        document.getElementById('selectedStek').value = x; 
        document.getElementById("selectedStek").style.color = "blue";
    }
  });



  
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
              url: $("body").data("base_url") + "Lotes/deletefile",
              success: function (result) {
                alertas(result);
               console.log('mytablemytable');
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
  
 
  $("body").on("click", "span.reload-lote", function (e) {

    e.preventDefault();
    var dato = new FormData();

    dato.append("file", $(this).data("file"));
    dato.append("id_proveedor", $(this).data("id_proveedor"));

    $.confirm({
      autoClose: "cancel|10000",
      title: "Recargar Datos",
      content: "Confirma ?",
      buttons: {
        confirm: {
          text: "Recargar datos API",
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
              url: $("body").data("base_url") + "Lotes/getDato",
              success: function (result) {
                alertas(result);
        
              //  $( ".datatable-ajax").DataTable().ajax.reload()
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





