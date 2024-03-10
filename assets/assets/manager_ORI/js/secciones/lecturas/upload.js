$(document).ready(function () {
  $('#views_dt').dataTable().fnClearTable();
  $('#views_dt').dataTable().fnDestroy();
	dt();

});


function dt(){

  $('#views_dt').dataTable().fnDestroy();
    var base_url = $("body").data('base_url');
	var id_proveedor = $("#id_proveedor").val();

    var dato = new FormData();
    dato.append("id_proveedor", id_proveedor);

   var table =  $("#views_dt").DataTable({
        dom: "frtip",
        columnDefs: [
          {
            targets: -1,
            //			className: 'dt-body-right',
            bSortable: false,
          },
          { visible: false, targets: [0,2,3,4,5,6] }
        ],
        
        language: {
          url: base_url + "assets/manager/js/plugins/tables/translate/spanish.json",
        },
        serverSide: true,
          
        ajax: {
   
          url: base_url + "Admin/Lecturas/list_dt/"+id_proveedor,
          type: "POST",
          error: function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR.status + textStatus + errorThrown);
          },
        },
      });
}

Dropzone.options.fileMultiple = { // camelized version of the `id`
    maxFiles: 20,
	autoProcessQueue:true ,
    paramName: "file", // The name that will be used to transfer the file
    maxFilesize:3, // MB
	url:'upload',
	acceptedFiles:"application/pdf",
	// dragstart:function(e) {
    //     alert(e
    //         )},
	// addedfile:function(file){
	// 	console.log('file');
	// 	console.log(file);
	// 	$("#total").html(this.files.length);
	// },
	init: function () {
       
		this.on("addedfile",function(file,responseText){
      blockui();
			$("#Accepteda").html(this.getAcceptedFiles().length);
			$("#Uploadinga").html(this.getUploadingFiles().length);
			$("#Queueda").html(this.getQueuedFiles().length);
			$("#Rejecteda").html(this.getRejectedFiles().length);
			$("#totala").html(this.files.length);
	
		$("#total").html(this.files.length);
		}),
		this.on("success", function (file, responseText) {
			$("#Accepteda").html(this.getAcceptedFiles().length);
			$("#Uploadinga").html(this.getUploadingFiles().length);
			$("#Queueda").html(this.getQueuedFiles().length);
			$("#Rejecteda").html(this.getRejectedFiles().length);
			$("#totala").html(this.files.length);
		  console.log(responseText);
		});
	  },
	  uploadprogress: function(file, progress, bytesSent) {
       
		console.log('uploadprogress');
		// console.log(file);
		console.log(progress);
	  },
	queuecomplete : function(file, response){
		
    },
    complete: function(){
      $.unblockUI();
     dt();  
        // var tabla = $('#views_dt').DataTable()
        // $('#views_dt').DataTable().ajax.reload();
        	},
    sending: function(file, xhr, formData){
        formData.append('id_proveedor', $("#id_proveedor").val());
    }
    // accept: function(file, done) {
    // //   if (file.name == "justinbieber.jpg") {
    // //     done("Naha, you don't.");
    // //   }
    // //   else { done('asdsaasa'); }
    // }
  };
// Dropzone.options.fileMultiple = {
// 	dictDefaultMessage: 'Drop fileAAAAAA to upload <span>or CLICK</span>',
// 	complete: function(){
// 		alert();
// 	},
// 	init: function() {
// 	  this.on("addedfile", file => {
// 		console.log("A file has been added");
// 	  });
// 	}
//   };