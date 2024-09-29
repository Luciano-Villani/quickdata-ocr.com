function dt() {
  $(".datatable-ajax_lotes").dataTable().fnDestroy();
  var base_url = $("body").data("base_url");
  var mytable = $(".datatable-ajax").dataTable({
    pageLength: 10,
    language: {
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    order: [[0, "desc"]],
    columnDefs: [
      { className: "dt-center", targets: "_all" },
      { className: "dt-nowrap", targets: [7] },
      // { width: "1%", visible: false, targets: [0] },
      { width: "1%", orderable: false, targets: [3, 4] },
    ],
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
      data: { table: "_lotes" },
      url: "/Admin/Lotes/lotes_dt/",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert('line 24'+jqXHR.status + textStatus + errorThrown);
      },
    },
    initComplete: function (a, v) {

      table = this;
      this.api()
        .columns([0, 1, 2, 6])
        .every(function () {
        });
      this.api().columns([0, 1]).every(function () {

      })

    },
  });
}

function checkFile(file) {
  var formDatas = new FormData();
  formDatas.append("id_proveedor", $("#id_proveedor").val());
  formDatas.append("code_lote", $("#code").val());

  formDatas.append("name", file["name"]);

  $.ajax({
    url: "/Admin/Lotes/checkFile",
    type: "POST",
    contentType: false,
    dataType: "json",
    data: formDatas,
    processData: false,
    cache: false,
    beforeSend: function () {
      $.blockUI({ message: "<h1>procesando...</h1>" });
    },
    success: function (data) {
      $.unblockUI();
      console.log("checkfile");
      if (data.status == "error") {
        console.log("error file");
        console.log(file);
        myDropzone = Dropzone.forElement(".dropzone");
        myDropzone.removeFile(file);
        data.estado = "error";
        data.title = "Carga de archivos";
        data.mensaje = "El archivo ya existe<br>" + file.name;
        alertas(data);
      } else {
        // var archivo = $("body").data('base_url')+"uploader/files/"+$('input#codeproveedor').val()+"/"+file.name;
        // $("#tabla_archivos").prepend( "<tr data-archivo='"+archivo+"'><td data-archivo='"+archivo+"'>"+archivo+"</td><td><span data-archivo='"+archivo+"' class='label bg-warning-400'>Pendiente</span></td></tr>");
        data.estado = "success";
        data.title = "Validación de archivos";
        data.mensaje = "<strong>OK</strong><br>" + file.name;
        $("#procesar_lote").removeAttr("disabled");
        // alertas(data);
      }
    },
    error: function (request, error) {
      alert("Request: " + JSON.stringify(request));
    },
  });
}

$(document).ready(function () {

  $("body").on("click", "button#cerrar_modal", function (e) {
    $('#tabla_archivos tbody').html('');
    $('#button#enviar_archivos').attr('disabled','disabled');

  });
  $("body").on("click", "button#enviar_archivos", function (e) {

    var filas = $('#tabla_archivos tbody tr');
    $('#tabla_archivos tbody tr').each(function(index){
      console.log($(this).data('archivo'));
      var nombre = $(this).data('archivo');
      
      var datos = {
        nombre: nombre,
        // apellido: apellido
      };
    
      var postdata = new FormData();
                postdata.append("file",nombre);
                postdata.append("id_proveedor",$("#id_proveedor").val());
      
                $.ajax({
                  type: "POST",
                  contentType: false,
                  dataType: "json",
                  data: postdata,
                  processData: false,
                  cache: false,
                  beforeSend: function () {

                    $("#tabla_archivos tbody").find("span[data-archivo='"+nombre+"']").removeClass('bg-warning-400').addClass('bg-info-400').text('procesando');
                   },
                  url: $("body").data("base_url") + "Lotes/leerApi",
                  success: function (result) {
                    console.log("RESULT");
                    console.log(result);
                    $("#tabla_archivos tbody").find("span[data-archivo='"+result.mensaje+"']").removeClass(['bg-info-400,bg-warning-400']).addClass('bg-success-400').text('Procesado');
                    var data = [];
                    data.status = result.status;
                    data.title = result.title;
                    data.mensaje = result.mensaje;
                    // alertas(result);
                    $(".datatable-ajax").DataTable().ajax.reload();
                  },
                  error: function (xhr, errmsg, err) {
                    console.log(xhr.status + ": " + xhr.responseText);
                  },
                });

      

      })


  })

//   $.confirm({
        
//     title: 'Archivos subidos',
//     content: 'Obtener datos de OCR del lote '+ $("#code").val()+'?',
//     buttons: {
//         confirm: function () {
           
//           // busqueda de datos API
//           var postdata = new FormData();
//           postdata.append("id_proveedor", $("#id_proveedor").val());
//           postdata.append("code_lote", $("#code").val());

//           $.ajax({
//             type: "POST",
//             contentType: false,
//             dataType: "json",
//             data: postdata,
//             processData: false,
//             cache: false,
//             beforeSend: function () { },
//             url: $("body").data("base_url") + "Lotes/leerApi/"+ $("#code").val()+'/'+$("#id_proveedor").val(),
//             success: function (result) {
//               console.log("RESULT");
//               console.log(result);
 
//             },
//             error: function (xhr, errmsg, err) {
//               console.log(xhr.status + ": " + xhr.responseText);
//             },
//           });


//         },
//         cancel: function () {
//             $.alert('Cancelado !');
//         }
//         ,
//     somethingElse: {
//         text: 'Something else',
//         btnClass: 'btn-blue',
//         disable:true,
//         keys: ['enter', 'shift'],
//         action: function(){
//             $.alert('Something else?');
//         }
//     }
//     }
// });

  function DestroyDropzones() {
    $(".dropzone").each(function () {
      let dropzoneControl = $(this)[0].dropzone;
      if (dropzoneControl) {
        dropzoneControl.destroy();
      }
    });
  }
  var uploader = document.querySelector(".dropzone");
  Dropzone.autoDiscover = false;
  DestroyDropzones();
  dt();
  var mytable = $("#lwecturas_dt").DataTable({
    responsive:true,
    serverSide: true,
    ajax: {
      url: $("body").data("base_url") + "lecturas/list_dt",
      type: "POST",
    },
  });
  var mytable = $("#lecturas_dt").DataTable({
    dom: "frtip",
    responsive:true,
    serverSide: true,
    columnDefs: [
      {
        targets: 0,
        			className: 'dt-body-left',
        bSortable: false,
      },
      { visible: false, targets: [] },
    ],
    language: {
      url:
        $("body").data("base_url") +
        "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    // dataType: 'json',
    serverSide: true,
    ajax: {
      data: { time: "time" },
      url: $("body").data("base_url") + "lecturas/list_dt",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR.status + textStatus + errorThrown);
      },
    },
  });

  $("body").on("click", "span.mergelote", function (e) {
    var code = $(this).data("code");
    var consolidado = $(this).data("consolidado");
    e.preventDefault();

    if ($(this).data("errores") != 0) {
      $.confirm({
        title: "CONSOLIDAR LOTE",
        content:
          "El lote: <strong> " +
          code +
          " </strong> posee errores de indexación",
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

      if (consolidado != '0') {
        $.confirm({
          title: "CONSOLIDAR LOTE",
          content:
            "El lote: <strong> " + code + " </strong> ya fue consolidado",
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
        $.confirm({
          autoClose: "cancel|10000",
          title: "CONSOLIDAR LOTE",
          content: "Confirma la consolidación del lote : <strong>" + $(this).data("code") + "</strong> ???",
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
                  beforeSend: function () {
                    $.blockUI();
                  },
                  url: $("body").data("base_url") + "Lecturas/Consolidar",
                  success: function (result) {
                    $.unblockUI();
                    $(".datatable-ajax").DataTable().ajax.reload();

                    console.log("resultaaaaa");
                    console.log(result);
                    var data = [];
                    data.estado = result.estado;
                    data.title = result.title;
                    data.mensaje = result.mensaje;
                    alertas(data);
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
              action: function () { },
            },
          },
        });
      }
    }
  });
  $("body").on("click", "span.borrar_lote", function (e) {
    e.preventDefault();

    var dato = new FormData();

    dato.append("code", $(this).data("code"));
    dato.append("id_lote", $(this).data("id_lote"));
    $.confirm({
      autoClose: "cancel|10000",
      title: "Eliminar lotes",
      content:
        "Confirma eliminar el lote: " +
        $(this).data("code") +
        " y sus archivos PDF ?",
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
              beforeSend: function () { },
              url: $("body").data("base_url") + "Lotes/delete",
              success: function (result) {
                console.log("result");
                console.log(result);
                $(".datatable-ajax").DataTable().ajax.reload();
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
          action: function () { },
        },
      },
    });
  });
  $("body").on("click", "a#consolidar", function (e) {
    e.preventDefault();

    var dato = new FormData();
    var action = $(this).data("accion");
    dato.append("id_lectura_api", $(this).data("id_lectura_api"));
    dato.append("id_indexador", $(this).data("id_indexador"));
    $.confirm({
      autoClose: "cancel|10000",
      title: $(this).data("text"),
      content: $(this).data("data_cons"),
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
              beforeSend: function () { },
              url: $("body").data("base_url") + "Lecturas/" + action,
              success: function (result) {
                console.log("result");
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
          action: function () { },
        },
      },
    });
  });
  function generarRandom(num) {
    const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    const charactersLength = characters.length;
    let result = "";
      for (let i = 0; i < num; i++) {
          result += characters.charAt(Math.floor(Math.random() * charactersLength));
      }
  
    return result;
  }
  var base_url = $("body").data("base_url");

  $("body").on("change", "select#id_proveedor", function () {

    
    if ($(this).val() > 0) {
      var dato = new FormData();
      dato.append("id_proveedor", $(this).val());
     var code =  generarRandom(5);
     $("input#code").val(code);
      $.ajax({
        type: "POST",
        contentType: false,
        dataType: "json",
        data: dato,
        processData: false,
        cache: false,
        beforeSend: function () { },
        url: $("body").data("base_url") + "Proveedores/checkApiUrl",
        success: function (result) {
          console.log("RESULT");
          console.log(result.proveedor);
          if (result.status == "false") {
            $.confirm({
              title: "PROVEEDORES",
              content: "El provedor no tiene url api configurada",
              buttons: {
                cancel: {
                  text: "Cancelar",
                  btnClass: "btn-red",
                  action: function () { },
                },
              },
            });
            $("#mydropzone").addClass("d-none");
            $("input#proveedor").val("");
            $("selct#id_proveedor").val(0);
            $("input#codeproveedor").val('');
          } else {
            $("#mydropzone").removeClass("d-none");
            $("input#proveedor").val(result.proveedor.id);
            $("span#modal_proveedor").html(result.proveedor.nombre);
            $("input#codeproveedor").val(result.proveedor.codigo);
          }
        },
        error: function (xhr, errmsg, err) {
          console.log(xhr.status + ": " + xhr.responseText);
        },
      });
    } else {
      $("#mydropzone").addClass("d-none");
      $("input#proveedor").val("");
    }
  });

  document.querySelectorAll("a.toggle-vis").forEach((el) => {
    el.addEventListener("click", function (e) {
      e.preventDefault();

      let columnIdx = e.target.getAttribute("data-column");
      let column = mytable.column(columnIdx);

      // Toggle the visibility
      column.visible(!column.visible());
    });
  });
});
$(function () {
  var myFileUploadDropZone = new Dropzone(".dropzone", {
    url: "/Lotes/upload",
    parallelUploads:10,
    autoProcessQueue: false,
    maxFiles: 10,
    maxFilesize: 2054,
    timeout: 1000000,
    acceptedFiles: ".pdf",
    addRemoveLinks: true,
    dictDefaultMessage: "Arrastra los archivos para procesar",
    dictFallbackMessage: "Tu navegador no soporta drag & drop.",
    dictInvalidFileType: "Archivo no permitido.",
    dictFileTooBig:
      "Archivo demasido pesado ({{filesize}} MB). Tamaño máximo permitido: {{maxFilesize}} MB.",
    dictResponseError: "Server responded with {{statusCode}} code.",
    dictCancelUpload: "Cancelar upload",
    dictRemoveFile: "Quitar",

    init: function () {
      var myDropzone = this;
      $("#procesar_lote").click(function (e) {


        e.preventDefault();
        $.blockUI();
        myDropzone.processQueue();
      }), this.on("error", function (file, response) {
        var data = [];
        data.status = 'error';
        data.title = 'Carga de archivos';
        data.mensaje = response;
        alertas(data);
        return;

      }),
       
        this.on("success", function (file, response) {

          var data = [];
          // aa = JSON.stringify(response);
          json = JSON.parse(JSON.stringify(response));
          mensaje = JSON.parse(json);
        
          data.status = mensaje.status;
          data.title = 'Carga de archivos';
          data.mensaje = mensaje.mensaje;
          var archivo = mensaje.file;
          $("#tabla_archivos").prepend( "<tr data-archivo='"+archivo+"'><td data-archivo='"+archivo+"'>"+archivo+"</td><td><span data-archivo='"+archivo+"' class='label bg-warning-400'>Pendiente</span></td></tr>");
  
          alertas(data);
        
       
        }),
        this.on("addedfile", (file) => {
          this.options.autoProcessQueue = false;
          checkFile(file);
          if (this.files.length) {
            $("body #procesar_lote").removeAttr('disabled');
            var _i, _len;
            for (
              _i = 0, _len = this.files.length;
              _i < _len - 1;
              _i++ // -1 to exclude current file
            ) {
              if (
                this.files[_i].name === file.name &&
                this.files[_i].size === file.size &&
                this.files[_i].lastModifiedDate.toString() ===
                file.lastModifiedDate.toString()
              ) {
                this.removeFile(file);
              }
              // console.log("datos");
              // console.log(this.files[_i]["name"]);
            }
          } else {
            $("body #procesar_lote").attr('disabled', 'disabled');
          }
        });

      this.on("complete", function (file) {
        console.log('file');
        console.log(file);
              

        if (
          this.getUploadingFiles().length === 0 &&
          this.getQueuedFiles().length === 0
        ) {
        }
        // $(".datatable-ajax").DataTable().ajax.reload();
        this.removeFile(file);
      });
      this.on("processing", function () {
        
        this.options.autoProcessQueue = true;
      });
    },
    removedfile: function (file) {
      file.previewElement.remove();
      if (!this.files.length) {
        $("body #procesar_lote").attr('disabled', 'disabled');
      }
    },


    sending: function (file, xhr, formData) {
      $.blockUI();
      myDropzone = Dropzone.forElement(".dropzone");
      // alert( myDropzone.getAcceptedFiles().length);
      console.log("sending");
      formData.append("id_proveedor", $("#id_proveedor").val());
      formData.append("code_lote", $("#code").val());
      // myDropzone.getQueuedFiles().length
      formData.append("cant", myDropzone.files.length);
    },
    queuecomplete: function (file, xhr, formData) {
      $.unblockUI();
      console.log("queuecomplete function");
      $("body #procesar_lote").attr('disabled', 'disabled');
      this.removeAllFiles(true);
      $('#modal_backdrop').modal('show');
      $('button#enviar_archivos').removeAttr('disabled');
      
     
      $(".datatable-ajax").DataTable().ajax.reload();
   
    //   $.confirm({
        
    //     title: 'Archivos subidos',
    //     content: 'Obtener datos de OCR del lote '+ $("#code").val()+'?',
    //     buttons: {
    //         confirm: function () {
               
    //           // busqueda de datos API
    //           var postdata = new FormData();
    //           postdata.append("id_proveedor", $("#id_proveedor").val());
    //           postdata.append("code_lote", $("#code").val());

    //           $.ajax({
    //             type: "POST",
    //             contentType: false,
    //             dataType: "json",
    //             data: postdata,
    //             processData: false,
    //             cache: false,
    //             beforeSend: function () { },
    //             url: $("body").data("base_url") + "Lotes/leerApi/"+ $("#code").val()+'/'+$("#id_proveedor").val(),
    //             success: function (result) {
    //               // console.log("RESULT");
    //               // console.log(result);
     
    //             },
    //             error: function (xhr, errmsg, err) {
    //               console.log(xhr.status + ": " + xhr.responseText);
    //             },
    //           });


    //         },
    //         cancel: function () {
    //             $.alert('Cancelado !');
    //         }
    //         ,
    //     somethingElse: {
    //         text: 'Something else',
    //         btnClass: 'btn-blue',
    //         disable:true,
    //         keys: ['enter', 'shift'],
    //         action: function(){
    //             $.alert('Something else?');
    //         }
    //     }
    //     }
    // });
 





    },
  });
});
