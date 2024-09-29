$(document).ready(function () {



	var base_url = $("body").data('base_url');
	

	$('#usuarios_dt').DataTable({
		dom: 'frtip',
		 columnDefs: [
    {
			targets: -1,
//			className: 'dt-body-right',
			bSortable: false,
    },
	{ visible: false, targets: [0,1] }
  ],
		language: {
			url: base_url + 'assets/manager/js/plugins/tables/translate/spanish.json'
		},
		serverSide: true,
		"type": "POST",
		dataSrc: '',
		ajax: {
			url: base_url + 'indexaciones/list_dt',
			type: 'POST',
			error: function (jqXHR, textStatus, errorThrown) {
				alert(jqXHR.status + textStatus + errorThrown);
			}
		}
	});
});

$(document).ready(function () {




	$('#select_dependencia').attr('disabled','disabled');

	$("#myProgramForm").on("change", "select#select_secretaria", function () {
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
			  $("#select_dependencia").empty();
			},
			url: $("body").data("base_url") + "Admin/Dependencias/get_dependencias",
			success: function (result) {
				var obj = jQuery.parseJSON( result );
				console.log("resultwwwww");
				console.log(Object.keys(obj.data).length);
				console.log(result);
	  
				if(Object.keys(obj.data).length > 0){
					$('#select_dependencia').removeAttr('disabled');   
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
				}else{
					$("#select_dependencia").append(
						'<option selected value="">SIN DEPENDENCIA</option>'
					);
					$('#select_dependencia').attr('disabled','disabled');
				}
			
			  //    					toastr.success('Registro Editado correctamente!', 'Categorías');
			},
			error: function (xhr, errmsg, err) {
			  console.log(xhr.status + ": " + xhr.responseText);
			},
		  })

	});

});
