$(document).ready(function () {

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




	var base_url = $("body").data('base_url');
	

	$('#usuarios_dt').DataTable({
		dom: 'frtip',
		 columnDefs: [
    {
			targets: -1,
//			className: 'dt-body-right',
			bSortable: false,
    },
	{ visible: false, targets: [0,1,4] }
  ],
		language: {
			url: base_url + 'assets/manager/js/plugins/tables/translate/spanish.json'
		},
		serverSide: true,
		"type": "POST",
		dataSrc: '',
		ajax: {
			url: base_url + 'Secretarias/list_dt',
			type: 'POST',
			error: function (jqXHR, textStatus, errorThrown) {
				alert(jqXHR.status + textStatus + errorThrown);
			}
		}
	});
});
