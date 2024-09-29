$(document).ready(function () {


	$("i.icon-pencil3").on('click', function (e) {

		alert();
	});


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






	$('#cars').select2({
		placeholder: 'Selecsasasaasasion'
	  });

});

/* ------------------------------------------------------------------------------
 *
 *  # Login pages
 *
 *  Demo JS code for a set of login and registration pages
 *
 * ---------------------------------------------------------------------------- */


// Setup module
// ------------------------------

var LoginRegistration = function () {


	//
	// Setup module components
	//

	// Uniform
	var _componentUniform = function () {
		if (!$().uniform) {
			console.warn('Warning - uniform.min.js is not loaded.');
			return;
		}

		// Initialize
		$('.form-input-styled').uniform();
	};


	//
	// Return objects assigned to module
	//

	return {
		initComponents: function () {
			_componentUniform();
		}
	}
}();


// Initialize module
// ------------------------------

document.addEventListener('DOMContentLoaded', function () {
	LoginRegistration.initComponents();
});
