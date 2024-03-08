$(document).ready(function () {
	initDatatable();
	$("form#form-validate-jquery").validate({
		rules:{
			major:"required",
			secretaria:"required",
		},
		messages:{
			major: "El campo es requerido",
			secretaria: "El campo Secretaría es requerido"
		}
	});
});

$("body").on("click", "a.borrar_dato", function (e) {
	e.preventDefault();

	var dato = new FormData();
	dato.append("id", $(this).data("id"));
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
			  url: $("body").data("base_url") + "Secretarias/delete",
			  success: function (result) {
				alertas(result);
				table.ajax.reload( null, false );
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
function initDatatable(search = false, type = 0) {
	var base_url = $("body").data("base_url");
 table =  $("#secretarias_dt").DataTable({
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
		{ width: "1%", orderable: false, targets: [2] },
    
    ],
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    type: "POST",
    processing: true,
    serverSide: true,
   
    ajax: {
      data: {
        type: type,
        table: "_secretarias",
        data_search: search,
      },
      url: base_url + "Secretarias/list_dt",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
  });
}