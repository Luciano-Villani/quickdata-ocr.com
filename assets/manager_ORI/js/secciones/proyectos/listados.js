$(document).ready(function () {
	$('#select_programa').attr('disabled','disabled');
	$("#form-validate-jquery").on("change", "select#select_secretaria", function () {
		
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
				var obj = jQuery.parseJSON( result );
				console.log("resultwwwww");
				console.log(Object.keys(obj.data).length);
				console.log(result);
	  
				if(Object.keys(obj.data).length > 0){
					$('#select_programa').removeAttr('disabled');   
					$("#select_programa").append(
						'<option selected value="0">SELECCIONE PROGRAMA</option>'
					);
					
					$.each(obj.data, function (id, value) {
						$("#select_programa").append(
						'<option value="' +
							value["id"] +
							'">'+value["id_interno"]+ ' - ' + value["descripcion"] +
							"</option>"
						);
					});
				}else{
					$("#select_programa").append(
						'<option selected value="">SIN PROGRAMAS</option>'
					);
					$('#select_programa').attr('disabled','disabled');
				}
			
			  //    					toastr.success('Registro Editado correctamente!', 'Categorías');
			},
			error: function (xhr, errmsg, err) {
			  console.log(xhr.status + ": " + xhr.responseText);
			},
		  })

	});

	var base_url = $("body").data('base_url');

	$("form#form-validate-jquery").validate({
		rules:{
			id_secretaria:{
				required: true,
				min:1
			},
			id_programa:{
				required: true,
				min:1
			},
			id_interno:"required",
			descripcion:"required",
		},
		messages:{
			id_secretaria: "Seleccione una opción",
			id_interno: "El campo es requerido",
			id_programa: "El campo Programa es requerido",
			descripcion: "El campo Proyecto es requerido"
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
	{ visible: false, targets: [0] }
  ],
		language: {
			url: base_url + 'assets/manager/js/plugins/tables/translate/spanish.json'
		},
		serverSide: true,
		"type": "POST",
		dataSrc: '',
		ajax: {
			url: base_url + 'proyectos/list_dt',
			type: 'POST',
			error: function (jqXHR, textStatus, errorThrown) {
				alert(jqXHR.status + textStatus + errorThrown);
			}
		}
	});
});

$(document).ready(function () {
	$('#select_dependencia').attr('disabled','disabled');

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
						'<option selected value="0">SIN DEPENDENCIA</option>'
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
