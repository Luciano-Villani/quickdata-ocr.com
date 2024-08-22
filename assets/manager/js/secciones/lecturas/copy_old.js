$(document).ready(function () {
    $("body").on("click", "span.reset-file", function(e) {
    
        e.preventDefault();
        var dato = new FormData();
        var id = $(this).data("id_file");
        var tabla = $(this).data("tabla");
        dato.append("id", id);
        dato.append("tabla", tabla);
        dato.append("campo", "id");
      
        $.confirm({
            autoClose: "cancel|10000",
            title: "Resetar Factura",
            content: "Confirma?",
            buttons: {
                confirm: {
                    text: "Resetear",
                    btnClass: "btn-blue",
                    action: function() {
                        $.ajax({
                            type: "POST",
                            contentType: false,
                            dataType: "json",
                            data: dato,
                            processData: false,
                            cache: false,
                            beforeSend: function() {},
                            url: $("body").data("base_url") + "lecturas/resetfile",
                            success: function(result) {
                                
                                console.log('mytablemytable');
                                console.log(id);
                                $("body").find("[data-file='"+id+"']").html('');
                                location.reload();
                            

                                // $(".datatable-ajax").DataTable().ajax.reload()
                            },
                            error: function(xhr, errmsg, err) {
                                console.log(xhr.status + ": " + xhr.responseText);
                            },
                        });
                    },
                },
                cancel: {
                    text: "Cancelar",
                    btnClass: "btn-red",
                    action: function() {},
                },
            },
        });
    });
});