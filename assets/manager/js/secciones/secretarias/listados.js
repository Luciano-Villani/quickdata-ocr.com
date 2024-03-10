function initDatatable(search = false, type = 0) {
	var base_url = $("body").data("base_url");
	table = $("#secretarias_dt").DataTable({
	  fixedHeader: {
		header: true,
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
	  order: false,
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
$(document).ready(function () {
  initDatatable();

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
				url: $("body").data("base_url") + "Secretarias/delete",
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
		title: "Borrar Secretaría",
		content: "La secretaría posee indexación",
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
      url: $("body").data("base_url") + "Secretarias/edit",
      success: function (result) {
        if (result.status == "success") {
          $("div#collapseExample").addClass("show");
          $("input[name='id']").val(result.data.id);
          $("input[name='major']").val(result.data.major);
          $("input[name='secretaria']").val(result.data.secretaria);
		  $('html, body').animate({ scrollTop: 0 }, 'fast');
          //   setTimeout( function() {

          // }, 1000);
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
  $("form#form-validate-jquery").validate({
    rules: {
      major: "required",
      secretaria: "required",
    },
    messages: {
      major: "El campo es requerido",
      secretaria: "El campo Secretaría es requerido",
    },
  });
});


$("body").on("click", "a.bxsorrar_dato", function (e) {
  alert();
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
              url: $("body").data("base_url") + "Secretarías/delete",
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
      title: "Borrar Secretaría",
      content: "La secretaría posee indexación",
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

