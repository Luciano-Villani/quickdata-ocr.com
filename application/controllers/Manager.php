<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Manager extends backend_controller {

	function __construct() {
		parent::__construct();
		if (!$this->ion_auth->logged_in())
		{
		  redirect('Login');
		}
		
	}	

	function dashboard(){
	
// 		$map = directory_map('./uploads');
		
// // cURL install check
// if (!function_exists('curl_init')) {
//     exit("cURL isn't installed for " . phpversion());
// }
// $API_KEY = '1431f3da31a5e793de9ff8a8f4be7939';
// $FILE_PATH = base_url('uploads/'.$map[0]);
// $FILE_PATH = base_url('uploads/27238775960_015_00002_00000015.pdf');
// $MIME_TYPE = 'application/pdf'; // change according to the file type
// $ACCOUNT = 'carlostutino';
// $VERSION = '1.1';
// $ENDPOINT = 'facturas';

// // Open a cURL session to send the document
// $ch = curl_init();

// // Setup headers
// $headers = array(
//   "Authorization: Token $API_KEY"
// );

// // Add our file to the request
// $data = array(
//   "document" => new CURLFile(
//       $FILE_PATH,
//       $MIME_TYPE,
//       substr($FILE_PATH, strrpos($FILE_PATH, "/") + 1)
//   )
// );

// // URL for a prediction
// $url = "https://api.mindee.net/v1/products/$ACCOUNT/$ENDPOINT/v$VERSION/predict";

// $options = array(
//   CURLOPT_URL => $url,
//   CURLOPT_HTTPHEADER => $headers,
//   CURLOPT_POSTFIELDS => $data,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_RETURNTRANSFER => true
// );

// // Set all options for the cURL request
// curl_setopt_array(
//     $ch,
//     $options
// );

// // Execute the request & extract the query content into a variable
// $json = curl_exec($ch);

// // Close the cURL session
// curl_close($ch);

// // Store the response as an array to allow for easier manipulations
// $result = json_decode($json, true);

// // Print the content of the document as raw json
// // echo json_encode($result, JSON_PRETTY_PRINT);

// echo '<pre><br>'; var_dump( $result); echo '<br></pre>';die(); 

/*
// */	
// 		$map = directory_map('./uploads');

// 		echo '<pre><br>'; var_dump( $map); echo '<br></pre>';die(); 
		$this->data['css_common']= $this->css_common;

		$this->data['script_common']= $this->script_common;
		
		$this->data['content']= $this->load->view('manager/secciones/dash',$this->data,TRUE);

		$this->load->view('manager/head', $this->data);
		$this->load->view('manager/index',$this->data);
		$this->load->view('manager/footer',$this->data);
	}	
}

