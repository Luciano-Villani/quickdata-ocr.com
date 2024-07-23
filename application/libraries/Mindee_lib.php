<?php
defined('BASEPATH') OR exit('No direct script access allowed'); 
require_once APPPATH . 'libraries/Mindee/src/Client.php';

require_once APPPATH . 'libraries/Mindee/src/Error/MindeeException.php';
require_once APPPATH . 'libraries/Mindee/src/Error/MindeeApiException.php';

require_once APPPATH . 'libraries/Mindee/src/Input/Base64Input.php';
require_once APPPATH . 'libraries/Mindee/src/Input/Bytes4Input.php';
require_once APPPATH . 'libraries/Mindee/src/Input/EnqueueAndParseMethodOptions.php';
require_once APPPATH . 'libraries/Mindee/src/Input/PageOptions.php';
require_once APPPATH . 'libraries/Mindee/src/Input/PredictOptions.php';
require_once APPPATH . 'libraries/Mindee/src/Input/PredictMethodOptions.php';
require_once APPPATH . 'libraries/Mindee/src/Input/InputSource.php';
require_once APPPATH . 'libraries/Mindee/src/Input/LocalInputSource.php';
require_once APPPATH . 'libraries/Mindee/src/Input/PathInput.php';
require_once APPPATH . 'libraries/Mindee/src/Input/FileInput.php';
require_once APPPATH . 'libraries/Mindee/src/Input/URLInputSource.php';



require_once APPPATH . 'libraries/Mindee/src/Http/MindeeApi.php';
require_once APPPATH . 'libraries/Mindee/src/Http/ResponseValidation.php';
require_once APPPATH . 'libraries/Mindee/src/Http/BaseEndpoint.php';
require_once APPPATH . 'libraries/Mindee/src/Http/EndPoint.php';

require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/Inference.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/ApiResponse.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/AsyncPredictResponse.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/ApiRequest.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/Document.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/Job.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/Product.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/Prediction.php';
require_once APPPATH . 'libraries/Mindee/src/Parsing/Common/Page.php';

require_once APPPATH . 'libraries/Mindee/src/Product/InvoiceSplitter/InvoiceSplitterV1.php';
require_once APPPATH . 'libraries/Mindee/src/Product/InvoiceSplitter/InvoiceSplitterV1Document.php';
require_once APPPATH . 'libraries/Mindee/src/Product/InvoiceSplitter/InvoiceSplitterV1PageGroup.php';
use Mindee\Client;
use Mindee\Input;
use CURLFile as c;
use Mindee\Error\MindeeMimeTypeException;
use Mindee\Error\MindeeSourceException;
use Mindee\Error\MindeeClientException;
use Mindee\Error\MindeeHttpException;
use Mindee\Http\ResponseValidation;
use Mindee\Input\EnqueueAndParseMethodOptions;
use Mindee\Input\InputSource;
use Mindee\Input\PathInput;
use Mindee\Input\LocalInputSource;
use Mindee\Input\PredictMethodOptions;
use Mindee\Error\MindeeApiException;
use Mindee\Http\Endpoint;
use Mindee\Http\MindeeApi;
use Mindee\Input\Base64Input;
use Mindee\Input\BytesInput;
use Mindee\Input\FileInput;
use Mindee\Input\PageOptions;
use Mindee\Input\URLInputSource;
use Mindee\Parsing\Common\AsyncPredictResponse;
use Mindee\Parsing\Common\PredictResponse;

use ReflectionClass as Rr;
use ReflectionException as xr;


class Mindfdee_lib
{
   function test(){
  
    $mindeeClient = new Client("f4b6ebe406cdb615674ae37aabc48929");

    $inputSource = $mindeeClient->sourceFromPath(base_url()."uploader/files/4399/0278693-23-12.pdf");

    // Enqueue and parse the file asynchronously
    $apiResponse = $mindeeClient->enqueueAndParse(InvoiceSplitterV1::class, $inputSource);
    echo '<pre>aca';
    var_dump($apiResponse ); 
    echo '</pre>';
    die();

// echo strval($apiResponse->document);


   }
   
}