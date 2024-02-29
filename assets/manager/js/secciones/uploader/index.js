$(document).ready(function () {

	var base_url = $("body").data('base_url');
	// $("#file_multiple").dropzone({
	// 	paramName: "file", // The name that will be used to transfer the file
	// 	dictDefaultMessage: 'Drop fileAAAAAA to upload <span>or CLICK</span>',
	// 	maxFilesize: 10 // MB
	// });
	//let myDropzone = Dropzone("#file_multiple", { url:'rods.php'});
// myDropzone.on("addedfile", file => {
//   console.log("A file has been added");
// });
});

Dropzone.options.fileMultiple = { // camelized version of the `id`
	
	autoProcessQueue:true,
    paramName: "file", // The name that will be used to transfer the file
    maxFilesize:3, // MB
	url:'upload',
	acceptedFiles:"application/pdf",
	dragstart(e) {alert(e)},
	// addedfile:function(file){
	// 	console.log('file');
	// 	console.log(file);
	// 	$("#total").html(this.files.length);
	// },
	init: function () {
		this.on("addedfile",function(file,responseText){
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
		console.log(file);
		console.log(bytesSent);
	  },
	queuecomplete : function(file, response){
		
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