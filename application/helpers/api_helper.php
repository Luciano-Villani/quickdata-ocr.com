<?php
if (!function_exists('apiRest')) {
  function apiRest($file, $endPoint)
  {

    // echo '<pre>';
    // var_dump( $file ); 
    // echo '</pre>';
    // die();

    $newendPoint = explode('/', $endPoint);

    if (!function_exists('curl_init')) {
      exit("cURL isn't installed for " . phpversion());
    }
    $API_KEY = 'f4b6ebe406cdb615674ae37aabc48929';

    // $FILE_PATH = base_url('uploads/27238775960_015_00002_00000015.pdf');
    $FILE_PATH = $file['full_path'];
    $MIME_TYPE = 'application/pdf'; // change according to the file type
    $ACCOUNT = 'quickdata-mvl';
    $VERSION = '1.1';
    $ENDPOINT = $newendPoint[6];


    // Open a cURL session to send the document
    $ch = curl_init();

    // Setup headers
    $headers = array(
      "Authorization: Token $API_KEY"
    );

    // Add our file to the request
    $data = array(
      "document" => new CURLFile(
        $FILE_PATH,
        $MIME_TYPE,
        substr($FILE_PATH, strrpos($FILE_PATH, "/") + 1)
      )
    );


    // URL for a prediction
    $url = $endPoint;

    $options = array(
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_RETURNTRANSFER => true
    );

    // Set all options for the cURL request
    curl_setopt_array(
      $ch,
      $options
    );

    // Execute the request & extract the query content into a variable
    $json = curl_exec($ch);



    // Close the cURL session
    curl_close($ch);

    // Store the response as an array to allow for easier manipulations
    return json_decode($json, JSON_PRETTY_PRINT);

    // Print the content of the document as raw json
// echo json_encode($result, JSON_PRETTY_PRINT);

  }

}

?>