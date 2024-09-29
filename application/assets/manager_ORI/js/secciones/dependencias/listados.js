
$(document).ready(function () {
	alert();
	new PNotify({
		title: 'Info notice',
		text: 'Check me out! I\'m a notice.',
		addclass: 'alert-styled-left alert-arrow-left text-sky-royal',
		type: 'info'
	});

	var base_url = $("body").data('base_url');
	
	if($("body").data('data_action') == "Editar"){
		$('.collapse').collapse('toggle');
	}
	$("form#form-validate-jquery").validate({
		rules:{
			id_secretaria:{
				required: true,
				min:1
			},
			dependencia:"required",
		},
		messages:{
			id_secretaria: "Seleccione una opción",
			dependencia: "El campo Dependencia es requerido"
		}
	});

	$('#usuarios_dt').DataTable({
		dom: 'frtip',
		 columnDefs: [
    {
			targets: -1,
//			className: 'dt-body-right',
			bSortable: false,
    },
	{ visible: false, targets: [0,3] }
  ],
		language: {
			url: base_url + 'assets/manager/js/plugins/tables/translate/spanish.json'
		},
		serverSide: true,
		"type": "POST",
		dataSrc: '',
		ajax: {
			url: base_url + 'Dependencias/list_dt',
			type: 'POST',
			error: function (jqXHR, textStatus, errorThrown) {
				alert(jqXHR.status + textStatus + errorThrown);
			}
		}
	});
});
