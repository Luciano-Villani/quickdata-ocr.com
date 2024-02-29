$(document).ready(function () {

	$("#resetAllPost").on("click", function (e) {


        var dato = new FormData();
		dato.append('id', $(this).data('id'));

		$.ajax({
			type: "POST",
			contentType: false,
			dataType: 'json',
			data: dato,
			processData: false,
			cache: false,
			beforeSend: function () {
				// $(".preloader").fadeIn();
				// $(".preloader").fadeOut();
			},
			url: base_url + "Admin/reset",
			success: function (result) {
				console.log('result');
				console.log(result);

				if (result.estado == true) {

			

				} else {
					toastr.error('Registro no Actualizado!', 'Categorías');
				}

			},
			error: function (xhr, errmsg, err) {
				console.log(xhr.status + ": " + xhr.responseText);
			}
		});

        
    });


});