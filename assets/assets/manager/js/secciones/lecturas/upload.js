
$(function() {


  // Table setup
  // ------------------------------

  // Setting datatable defaults
  $.extend( $.fn.dataTable.defaults, {
      autoWidth: false,
      columnDefs: [{ 
          orderable: false,
          width: '100px',
          targets: [ 5 ]
      }],
      dom: '<"datatable-header"fl><"datatable-scroll"t><"datatable-footer"ip>',
      language: {
          search: '<span>Filter:</span> _INPUT_',
          lengthMenu: '<span>Show:</span> _MENU_',
          paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
      },
      drawCallback: function () {
          $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
      },
      preDrawCallback: function() {
          $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
      }
  });


  // HTML sourced data
  // $('.datatable-html').dataTable();


  // AJAX sourced data
  $('.datatable-ajax').dataTable({
      processing: true,
      serverSide: true, 
      ajax: {
          data:{table:"_lotes",id_proveedor:$("#id_proveedor").val()},
          url: "/Admin/Lecturas/lotes_dt/" + id_proveedor,
          // type: "POST",
            error: function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR.status + textStatus + errorThrown);
          },
        },
        initComplete: function () {
          this.api().columns().every( function() {
              var column = this;
              var select = $('<select class="filter-select" data-placeholder="Filter"><option value=""></option></select>')
                  .appendTo($(column.footer()).not(':last-child').empty())
                  .on('change', function() {
                      var val = $.fn.dataTable.util.escapeRegex(
                          $(this).val()
                      );

                      column
                          .search( val ? '^'+val+'$' : '', true, false )
                          .draw();
                  });

              column.data().unique().sort().each( function (d, j) {
                  select.append('<option value="'+d+'">'+d+'</option>')
              });
          });
      }
  });


  // Javascript sourced data
  var dataSet = [
      ['Trident','Internet Explorer 4.0','Win 95+','4','X'],
      ['Trident','Internet Explorer 5.0','Win 95+','5','C'],
      ['Trident','Internet Explorer 5.5','Win 95+','5.5','A'],
      ['Trident','Internet Explorer 6','Win 98+','6','A'],
      ['Gecko','Firefox 1.0','Win 98+ / OSX.2+','1.7','A'],
      ['Gecko','Firefox 1.5','Win 98+ / OSX.2+','1.8','A'],
      ['Gecko','Firefox 2.0','Win 98+ / OSX.2+','1.8','A'],
      ['Gecko','Firefox 3.0','Win 2k+ / OSX.3+','1.9','A'],
      ['Gecko','Camino 1.0','OSX.2+','1.8','A'],
      ['Gecko','Camino 1.5','OSX.3+','1.8','A'],
      ['Webkit','Safari 1.2','OSX.3','125.5','A'],
      ['Webkit','Safari 1.3','OSX.3','312.8','A'],
      ['Webkit','Safari 2.0','OSX.4+','419.3','A'],
      ['Presto','Opera 7.0','Win 95+ / OSX.1+','-','A'],
      ['Presto','Opera 7.5','Win 95+ / OSX.2+','-','A'],
      ['Misc','NetFront 3.1','Embedded devices','-','C'],
      ['Misc','NetFront 3.4','Embedded devices','-','A'],
      ['Misc','Dillo 0.8','Embedded devices','-','X'],
      ['Misc','Links','Text only','-','X']
  ];

  // $('.datatable-js').dataTable({
  //     data: dataSet,
  //     columnDefs: []
  // });


  // // Nested object data
  // $('.datatable-nested').dataTable({
  //     ajax: 'assets/demo_data/tables/datatable_nested.json',
  //     columns: [
  //         {data: "name[, ]"},
  //         {data: "hr.0" },
  //         {data: "office"},
  //         {data: "extn"},
  //         {data: "hr.2"},
  //         {data: "hr.1"}
  //     ]
  // });


  // Generate content for a column
  // var table = $('.datatable-generated').DataTable({
  //     ajax: 'assets/demo_data/tables/datatable_ajax.json',
  //     columnDefs: [{
  //         targets: 2,
  //         data: null,
  //         defaultContent: "<button class='label label-default'>Show</button>"
  //     },
  //     { 
  //         orderable: false,
  //         targets: [0, 2]
  //     }]
  // });
  
  // $('.datatable-generated tbody').on('click', 'button', function () {
  //     var data = table.row($(this).parents('tr')).data();
  //     alert(data[0] +"'s location is: "+ data[ 2 ]);
  // });



  // External table additions
  // ------------------------------

  // Add placeholder to the datatable filter option
  $('.dataTables_filter input[type=search]').attr('placeholder','Type to filter...');


  // Enable Select2 select for the length option
  $('.dataTables_length select').select2({
      minimumResultsForSearch: "-1"
  });
  
});
$(document).ready(function () {

  
  $("#intoText").html("*");

  // // $("#lotes_dt").dataTable().fnClearTable();
  // // $("#lotes_dt").dataTable().fnDestroy();
  // // // // dt();
});

function dt() {
  $("#lotes_dt").dataTable().fnDestroy();
  var base_url = $("body").data("base_url");
  var id_proveedor = $("#id_proveedor").val();

  var dato = new FormData();
  dato.append("id_proveedor", id_proveedor);

  // var table = $("#lotes_dts").DataTable({
   
  //   dom       :"fptip",
  //   pageLenght:"5",
  //   initComplete: function () {
 
  //     this.api()

  //         .columns()
  //         .every(function () {
  //             let column = this;

     
  //             // Create select element
  //             let select = document.createElement('select');
  //             select.add(new Option(''));
  //             column.footer().replaceChildren(select);

  //             // Apply listener for user change in value
  //             select.addEventListener('change', function () {
  //                 var val = DataTable.util.escapeRegex(select.value);

  //                 column
  //                     .search(val ? '^' + val + '$' : '', true, false)
  //                     .draw();
  //             });

  //             // Add list of options
  //             column
  //                 .data()
  //                 .unique()
  //                 .sort()
  //                 .each(function (d, j) {
  //                   console.log('d');
  //                   console.log(d);
  //                   console.log('c');
  //                   console.log(j);
  //                     select.add(new Option(d));
  //                 });
  //         });
  // },
  //   // dom: "frtip",
  //   columnDefs: [
  //     {
  //       targets: -1,
  //       //			className: 'dt-body-right',
  //       // bSortable: false,
  //     },
  //     { visible: false, targets: [ ] },
  //   ],

  //   language: {
  //     url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
  //   },
  //   processing: true,
  //   serverSide: true, 

  // paging: true,
  // pageLength: 10,
  //   ajax: {
  //     data:{table:"_lotes",id_proveedor:$("#id_proveedor").val()},
  //     url: base_url + "Admin/Lecturas/lotes_dt/" + id_proveedor,
  //     // type: "POST",
  //       error: function (jqXHR, textStatus, errorThrown) {
  //       alert(jqXHR.status + textStatus + errorThrown);
  //     },
  //   },
  // });
}

function buscarInfoPanel() {


  console.log('buscarInfoPanel');
  var formData = new FormData();
  formData.append("id_proveedor", $("#id_proveedor").val());
  formData.append("code", $("body > #code").val());
  formData.append("cant", myDropzone.files.length);
  $.ajax({
    url: "getInfoPanel",
    type: "POST",
    contentType: false,
    dataType: 'json',
    data: formData,
    processData: false,
    cache: false,
    success: function (data) {
      alert();
      $("div.header-elements").html(data);
    },
    error: function (request, error) {
      alert("Request: " + JSON.stringify(request));
    },
  });
}

function checkFile(file) {
  var formDatas = new FormData();
  formDatas.append("id_proveedor", $("#id_proveedor").val());

  formDatas.append("name", file['name']);

  $.ajax({
    url: "/Admin/checkFile",
    type: "POST",
    contentType: false,
    dataType: 'json',
    data: formDatas,
    processData: false,
    cache: false,
    beforeSend: function () {
    
       $.blockUI();
    },
    success: function (data) {
      $.unblockUI();
      console.log('checkfile');
      if(data.status == "error"){
        console.log('error file');
        console.log(file);
        myDropzone.removeFile(file);
        data.estado = 'error';
        data.title = 'Carga de archivos';
        data.mensaje = 'El archivo ya existe<br>' + file.name;
        alertas(data);
      }else{

        data.estado = 'success';
        data.title = 'Validación de archivos';
        data.mensaje = '<strong>OK</strong><br>' + file.name;
        alertas(data);
      }
    },
    error: function (request, error) {
      alert("Request: " + JSON.stringify(request));
    },
  });
}
function cerrarLote() {

  var formData = new FormData();
  formData.append("id_proveedor", $("#id_proveedor").val());
  formData.append("id_lote", $("#id_lote").val());
  formData.append("code_lote", $("#code").val());
  formData.append("cant", $("span#intoText").html());

  $.ajax({
    url: "cerrarLote",
    type: "POST",
    contentType: false,
    dataType: 'json',
    data: formData,
    processData: false,
    cache: false,
    success: function (data) {
      console.log('cerrarLote');
      console.log(data);
      $("div.header-elements").html(data);
      myDropzone.removeAllFiles();
  
    },
    error: function (request, error) {
      alert("Request: " + JSON.stringify(request));
    },
  }); // cerrar lote
}

Dropzone.options.fileMultiple = {
  dictDuplicateFile: "Duplicate Files Cannot Be Uploaded",
  preventDuplicates: true,
  addRemoveLinks: true,
  dictRemoveFile: "Quitar",
  // maxFiles: 20,
  autoProcessQueue: false,
  paramName: "file", // The name that will be used to transfer the file
  maxFilesize: 3, // MB
  url:"/Admin/uploader",
  acceptedFiles: "application/pdf",
  dragstart: function (e) {
    console.log("dragstart");
    console.log(e);
  },
  removedfile: function (file) {
    console.log('removedfile');
    file.previewElement.remove();
    if(this.files.length == 0){
      $("#Myheader-elements").html('');
    }else{
      $("body span#intoText").html(this.files.length);
    }
  },
  success(data) {
    console.log('succes');
    console.log(data.File);
    data.estado = 'success';
    data.title = 'Cargasssss de archivos';
    data.mensaje = 'OK' + data.file.name;
    alertas(data);
    // $.unblockUI();
    // location.reload();
  }, 
   successmultiple() {
    // console.log('successmultiple');
  },
  
  // addedfile:function(file){
  // 	console.log('file');
  // 	console.log(file);
  // 	$("#total").html(this.files.length);
  // },
  init: function () {
    console.log('init');
    var base_url = $("body").data('base_url');
    var submitButton = document.querySelector("div#Myheader-elements");

    myDropzone = this;
    submitButton.addEventListener("click", function () {

      // $.blockUI();
      myDropzone.processQueue();

    });
    this.on("drop", function (event) {
      console.log("drop");

      if ($("#Myheader-elements").html() == "") {
        // alert('1');
        buscarInfoPanel();
        $("body span#intoText").html(this.files.length);
      } else {
        $("body span#intoText").html(this.files.length);
      }
    });

    this.on("dragstart", function (file) {
      console.log("dragstart");
      console.log(file);
    }),
      this.on("addedfile",file=>{
      console.log("addedfile");

      checkFile(file);
      if (this.files.length) {
        var _i, _len;
        for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) // -1 to exclude current file
        {
            if(this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString())
            {
                this.removeFile(file);
            }
            console.log('datos');
            console.log(this.files[_i]['name']);
        }
    }
   
        if ($("#Myheader-elements").html() == "") {
          // alert('1');
          buscarInfoPanel();
          $("body span#intoText").html(this.files.length);
          
        } else {
          $("body span#intoText").html(this.files.length);
         
        }
        $("body  input#cantidad_archivos").val(this.files.length);
        dt();
      }),
      this.on("success", function (file, responseText) {
        $("#Accepteda").html(this.getAcceptedFiles().length);
        $("#Uploadinga").html(this.getUploadingFiles().length);
        $("#Queueda").html(this.getQueuedFiles().length);
        $("#totala").html(this.files.length);
        // console.log(responseText);
      });
  },
  uploadprogress: function (file, progress, bytesSent) {
    console.log("uploadprogress");
    console.log(file);
    console.log(progress);return false;
  },
  queuecomplete: function (file, response) {
    console.log('queuecomplete function');
    // dt();
    // cerrarLote();
  
  },
  complete: function () {

    console.log('complete function');
 
  
  },
  sending: function (file, xhr, formData) {
    console.log('sending');
    formData.append("id_proveedor", $("#id_proveedor").val());
    formData.append("id_lote", $("#id_lote").val());
    formData.append("code_lote", $("#code").val());
    formData.append("cant", this.files.length);
  },
  // accept: function(file, done) {
  // //   if (file.name == "justinbieber.jpg") {
  // //     done("Naha, you don't.");
  // //   }
  // //   else { done('asdsaasa'); }
  // }
};





