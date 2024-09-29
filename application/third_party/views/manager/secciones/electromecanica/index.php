<script>
// Get the template HTML and remove it from the doument
var previewNode = document.querySelector("#template");
previewNode.id = "";
var previewTemplate = previewNode.parentNode.innerHTML;
previewNode.parentNode.removeChild(previewNode);

Dropzone.createElement = function(string) {
var el = $(string);
return el[0];
};
var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
url: "/Lotes/upload", // Set the url
thumbnailWidth: 80,
// paramName: "warehouse_image",
thumbnailHeight: 80,
parallelUploads: 20,
previewTemplate: previewTemplate,
autoQueue: false, // Make sure the files aren't queued until manually added
previewsContainer: "#previews", // Define the container to display the previews
clickable: ".fileinput-button", // Define the element that should be used as click trigger to select files.
renameFile: function(file) {
	var dt = new Date();
	var time = dt.getTime();
	return time+file.name;
},
headers: {
	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
},
});

myDropzone.on("addedfile", function (file) {
// Hookup the start button
file.previewElement.querySelector(".start").onclick = function () {
	myDropzone.enqueueFile(file);
};
});

// Update the total progress bar
myDropzone.on("totaluploadprogress", function (progress) {
document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
});

myDropzone.on("sending", function (file) {
// Show the total progress bar when upload starts
document.querySelector("#total-progress").style.opacity = "1";
// And disable the start button
file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
});

// Hide the total progress bar when nothing's uploading anymore
myDropzone.on("queuecomplete", function (progress) {
document.querySelector("#total-progress").style.opacity = "0";
});
myDropzone.on("sending", function(file, xhr, formData){
formData.append("camera_id", "loremipsum");
console.log(file);
console.log(file.upload.filename);
console.log(xhr);

});

// Setup the buttons for all transfers
// The "add files" button doesn't need to be setup because the config
// `clickable` has already been specified.
document.querySelector("#actions .start").onclick = function () {
myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
};
document.querySelector("#actions .cancel").onclick = function () {
myDropzone.removeAllFiles(true);
};

</script>