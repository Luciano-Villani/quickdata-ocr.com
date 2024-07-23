<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "vendor/autoload.php";

// require_once APPPATH . 'libraries/Mindee/src/Input/Base64Input.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/BytesInput.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/EnqueueAndParseMethodOptions.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/PageOptions.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/PredictOptions.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/PredictMethodOptions.php';
// // require_once APPPATH . 'libraries/Mindee/src/Input/InputSource.php';
// // require_once APPPATH . 'libraries/Mindee/src/Input/LocalInputSource.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/URLInputSource.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/PathInput.php';
// require_once APPPATH . 'libraries/Mindee/src/Input/FileInput.php';
use Mindee\Client;
use Mindee\Product\InvoiceSplitter\InvoiceSplitterV1;
// // use Mindee\Input;
// use CURLFile as c;
// use Mindee\Error\MindeeMimeTypeException;
// use Mindee\Error\MindeeSourceException;
// use Mindee\Error\MindeeClientException;
// use Mindee\Error\MindeeHttpException;
// use Mindee\Http\ResponseValidation;
// use Mindee\Input\EnqueueAndParseMethodOptions;
// // use Mindee\Input\InputSource;
// use Mindee\Input\PathInput;
// // use Mindee\Input\LocalInputSource;
// use Mindee\Input\PredictMethodOptions;
// use Mindee\Error\MindeeApiException;
// use Mindee\Http\Endpoint;
// use Mindee\Http\MindeeApi;
// use Mindee\Input\Base64Input;
// use Mindee\Input\BytesInput;
// use Mindee\Input\FileInput;
// use Mindee\Input\PageOptions;
// use Mindee\Input\URLInputSource;
// use Mindee\Parsing\Common\AsyncPredictResponse;
// use Mindee\Parsing\Common\PredictResponse;

class Mindee2_lib
{
    public function __construct(){
    //    die('DebugPHPMailer class is loaded.');
    }
    function test(){

        $mindeeClient = new Client("f4b6ebe406cdb615674ae37aabc48929");
    
        $inputSource = $mindeeClient->sourceFromPath("uploader/files/4399/0278693-23-12.pdf");
    
        // Enqueue and parse the file asynchronously
        $apiResponse = $mindeeClient->enqueueAndParse(InvoiceSplitterV1::class, $inputSource);
        
        echo '<pre>----';
        var_dump( $apiResponse->document); 
        echo '</pre>';
        die();
        echo strval($apiResponse->document);
    
    
       }
}