function dt() {
  
  $(".datatable-ajax_lotes").dataTable().fnDestroy();
  var base_url = $("body").data("base_url");
  var mytable = $(".datatable-ajax").dataTable({
    pageLength: 10,
    language: {
      select: {
        rows: " %d Registros seleccionados",
      },
      url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
    },
    order: [[0, "desc"]],
    columnDefs: [
      { className: "dt-center", targets: "_all" },
      { className: "dt-nowrap", targets: [7] },
      { orderable: false, targets: [5] },
      {
        targets: 0,
        checkboxes: {
          selectRow: true,
        },
        // render:function(data, type, row, meta){
        //   console.log(data);

        // },
      },
      // { width: "1%", visible: false, targets: [0] },
      { width: "1%", orderable: false, targets: [0, 3, 4] },
    ],
    processing: true,
    serverSide: true,
    select: {
      style: "multi",
      selector: "td:first-child",
    },

    createdRow: function (row, data, dataIndex) {
      // agrego el atributo id al td 0

      $(row).attr("id", data[0]);
      $(row).find("td:eq(0)").attr("id", data[0]);
    },
    ajax: {
      data: { table: "_lotes_canon" },
      url: "/Electromecanica/Lecturas/lotes_dt/",
      type: "POST",
      error: function (jqXHR, textStatus, errorThrown) {
        alert("line 24" + jqXHR.status + textStatus + errorThrown);
      },
    },
    initComplete: function (a, v) {
      table = this;
      this.api()
        .columns([0, 1, 2, 6])
        .every(function () {});
      this.api()
        .columns([0, 1])
        .every(function () {});
    },
  });
}

function checkFile(file) {
  var formDatas = new FormData();
  formDatas.append("id_proveedor", $("#id_proveedor").val());
  formDatas.append("code_lote", $("#code").val());

  formDatas.append("name", file["name"]);

  $.ajax({
    url: "/Electromecanica/Lecturas/checkFile",
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
  

  // $("#modal_backdrop").modal("show");
  $("body").on("click", "button#cerrar_modal", function (e) {

    $("#tabla_archivos tbody").html("");
    $("button#enviar_archivos").attr("disabled", "disabled");
  });

  $("body").on("click", "button#enviar_archivos", function (e) {


    cantidadarchivos = $("#tabla_archivos tbody tr").length; 
    $("#count").val(count);
    console.log('count');
    console.log(count);

    $("button#enviar_archivos").attr("disabled", "disabled");
    
    var pasada = 0;
    $("#tabla_archivos tbody tr").each(function (index) {
      var nombre = $(this).data("archivo");
      var postdata = new FormData();
      // postdata.append("id_lote", $("#code").val());
      postdata.append("file", nombre);
      postdata.append("id_proveedor", $("#id_proveedor").val());
      setTimeout(function () {
        $.ajax({
          xhr: function () {
            var xhr = new window.XMLHttpRequest();
            //Upload progress
            xhr.upload.addEventListener(
              "progress",
              function (evt) {
                if (evt.lengthComputable) {
                  var percentComplete = evt.loaded / evt.total;
                  //Do something with upload progress
                  console.log("percentComplete");
                  console.log(percentComplete);

                  $(".progress-bar").css("width", percentComplete + "%");
                  $(".progress-bar").html(percentComplete + " %" + count);
                }
              },
              false
            );
            return xhr;
          },
          type: "POST",
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
          data: postdata,
          beforeSend: function () {
            var xhr = new window.XMLHttpRequest();
            //Upload progress
            xhr.upload.addEventListener(
              "progress",
              function (evt) {
                if (evt.lengthComputable) {
                  var percentComplete = evt.loaded / evt.total;
                  //Do something with upload progress
                  console.log("percentComplete");
                  console.log(percentComplete);

                  $(".progress-bar").css("width", percentComplete + "%");
                  $(".progress-bar").html(percentComplete + " %" + count);
                }
              },
              false
            );
            $(".progress").show();
            $(".progress-bar").css("width", "0%");
            $(".progress-bar").html("0%");
            console.log("SALE");
            $("#tabla_archivos tbody")
              .find("span[data-archivo='" + nombre + "']")
              .removeClass("bg-warning-400 ")
              .addClass("bg-info-400")
              .text("procesando");
          },
          url: $("body").data("base_url") + "Electromecanica/Lecturas/leerApi",
          success: function (result) {
            console.log("count");
            console.log(count);
            console.log('pasada++');
            console.log(pasada++);

            $("#tabla_archivos tbody")
              .find("span[data-archivo='" + result.mensaje + "']")
              .removeClass(["bg-info-400"])
              .removeClass(["bg-warning-400"])
              .addClass("bg-success-400")
              .text("Procesado");
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
      }, 0);
    });

    function sleep(milliseconds) {
      var start = new Date().getTime();
      for (var i = 0; i < 1e7; i++) {
        if (new Date().getTime() - start > milliseconds) {
          break;
        }
      }
    }
  });


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

  // var mytable = $("#lecturas_dt").DataTable({
  //   dom: "frtip",
  //   responsive: true,
  //   serverSide: true,
  //   columnDefs: [
  //     {
  //       targets: ["_all"],
  //       className: "dt-body-left",
  //       bSortable: false,
  //     },
  //     { visible: false, targets: [] },
  //   ],
  //   language: {
  //     url:
  //       $("body").data("base_url") +
  //       "assets/manager/js/plugins/tables/translate/spanish.json",
  //   },
  //   // dataType: 'json',
  //   serverSide: true,
  //   ajax: {
  //     data: { time: "time" },
  //     url: $("body").data("base_url") + "Electromecanica/lecturas/list_dt",
  //     type: "POST",
  //     error: function (jqXHR, textStatus, errorThrown) {
  //       alert(jqXHR.status + textStatus + errorThrown);
  //     },
  //   },
  // });

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
      if (consolidado != "0") {
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
          content:
            "Confirma la consolidación del lote : <strong>" +
            $(this).data("code") +
            "</strong> ???",
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
              action: function () {},
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
              beforeSend: function () {},
              url: $("body").data("base_url") + "Electromecanica/Lecturas/delete_lote",
              success: function (result) {
                console.log("result");
                console.log(result);
                alertas(result);
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
          action: function () {},
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
              beforeSend: function () {},
              url: $("body").data("base_url") + "Electromecanica/Lecturas/" + action,
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
          action: function () {},
        },
      },
    });
  });
  function generarRandom(num) {
    const characters =
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
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
      var code = generarRandom(5);
      $("input#code").val(code);
      $.ajax({
        type: "POST",
        contentType: false,
        dataType: "json",
        data: dato,
        processData: false,
        cache: false,
        beforeSend: function () {},
        url: $("body").data("base_url") + "Electromecanica/Proveedores/checkApiUrl",
        success: function (result) {
          console.log("RESULT");
          console.log(result);
          if (result.status == "false") {
            $.confirm({
              title: "PROVEEDORES",
              content: "El provedor no tiene url api configurada",
              buttons: {
                cancel: {
                  text: "Cancelar",
                  btnClass: "btn-red",
                  action: function () {},
                },
              },
            });
            $("#mydropzone").addClass("d-none");
            $("input#proveedor").val("");
            $("selct#id_proveedor").val(0);
            $("input#codeproveedor").val("");
          } else {
            $("#mydropzone").removeClass("d-none");
            
            $("input#proveedor").val(result.proveedor.id);
            $("input#tipoproveedor").val(result.proveedor.tipo_proveedor);
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

  $("body").on("click", "span.reload-lote", function (e) {

    e.preventDefault();
    var dato = new FormData();

    dato.append("id_lote", $(this).data("id_lote"));
    dato.append("id_proveedor", $(this).data("id_proveedor"));

    $.confirm({
      autoClose: "cancel|10000",
      title: "Recargar Datos",
      content: "Confirma ?",
      buttons: {
        confirm: {
          text: "Recargar datos API",
          btnClass: "btn-blue",
          action: function () {
            $.ajax({
              xhr: function () {
                var xhr = $.ajaxSettings.xhr();
                xhr.onprogress = function (e) {
                    // For downloads
                    if (e.lengthComputable) {
                        console.log('download');
                        console.log(e.loaded / e.total);
                      }
                    };
                    xhr.upload.onprogress = function (e) {
                      // For uploads
                      if (e.lengthComputable) {
                      console.log('upload');
                        console.log(e.loaded / e.total);
                    }
                };
                return xhr;
            },
              type: "POST",
              contentType: false,
              dataType: "json",
              data: dato,
              processData: false,
              cache: false,
              beforeSend: function () {
                $.blockUI({
                  message:
                    "<h2>Recargando datos de archivos</h2><p>Procesando</p>"
                });
              },
              url: $("body").data("base_url") + "Electromecanica/Lecturas/leerApi",
              success: function (result) {
                $.unblockUI();
                console.log('result dato leer lotes api');
                console.log(result);
                alertas(result);
        
               $( ".datatable-ajax").DataTable().ajax.reload()
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
    url: "/Electromecanica/Lecturas/upload",

    parallelUploads: 10,
    autoProcessQueue: false,
    maxFiles: null,
    maxFilesize: 10000,
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
      }),
        this.on("error", function (file, response) {
          var data = [];
          data.status = "error";
          data.title = "Carga de archivos";
          data.mensaje = response;
          alertas(data);
          return;
        }),
        this.on("success", function (file, response) {
          var data = [];
          // aa = JSON.stringify(response);
          json = JSON.parse(JSON.stringify(response));
          mensaje = JSON.parse(json);

          console.log("llega dfesde upload");
          console.log(mensaje);
          data.status = mensaje.status;
          data.title = "Carga de archivos";
          data.mensaje = mensaje.mensaje;
          var archivo = mensaje.file;
          $("#tabla_archivos").prepend(
            "<tr data-archivo='" +
              mensaje.pathw +
              "'><td data-archivo='" +
              mensaje.pathw +
              "'>" +
              archivo +
              "</td><td><span data-archivo='" +
              mensaje.pathw +
              "' class='label bg-warning-400'>Pendiente</span></td></tr>"
          );

          // alert(data);
        }),
        this.on("addedfile", (file) => {
          this.options.autoProcessQueue = false;
          checkFile(file);
          if (this.files.length) {
            $("body #procesar_lote").removeAttr("disabled");
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
            $("body #procesar_lote").attr("disabled", "disabled");
          }
        });

      this.on("complete", function (file) {
        console.log("complete ");
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
        $("body #procesar_lote").attr("disabled", "disabled");
      }
    },

    sending: function (file, xhr, formData) {
      $.blockUI();
      myDropzone = Dropzone.forElement(".dropzone");
      // alert( myDropzone.getAcceptedFiles().length);
      console.log("sending");
      console.log(file);
      console.log("xhr");
      console.log(xhr);

      formData.append("id_proveedor", $("#id_proveedor").val());
      formData.append("code_lote", $("#code").val());
      // myDropzone.getQueuedFiles().length
      formData.append("cant", myDropzone.files.length);
    },
    queuecomplete: function (file, xhr, formData) {
      $.unblockUI();
      // var code = generarRandom(5);

      console.log("queuecomplete function");
      $("body #procesar_lote").attr("disabled", "disabled");
      this.removeAllFiles(true);
      $("#modal_backdrop").modal("show");
      $("button#enviar_archivos").removeAttr("disabled");

      $(".datatable-ajax").DataTable().ajax.reload();
    },
  });
});
