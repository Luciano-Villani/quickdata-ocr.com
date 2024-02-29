<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Code{
    var $CI;
    var $encryption_key = '';
    var $_hash_type = 'sha1';
    var $_mcrypt_exists = FALSE;
    var $_mcrypt_cipher;
    var $_mcrypt_mode;
    /**
     * Constructor
     *
     * Simply determines whether the mcrypt library exists.
     *
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->_mcrypt_exists = ( ! function_exists('code')) ? FALSE : TRUE;
        log_message('debug', "Encrypt Class Initialized");
    }

    function Letra($L) 
{
 
   $source = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
              if($L>0){
              $rstr = "";
              $source = str_split($source,1);
              for($i=1; $i<=$L; $i++){
              mt_srand((double)microtime() * 1000000);
              $num = mt_rand(1,count($source));
              $rstr .= $source[$num-1];
     }
 
     return  $rstr;
     }
}


function Numero($N) 
{
 
   $source = '0123456789';
              if($N>0){
              $rstr = "";
              $source = str_split($source,1);
              for($i=1; $i<=$N; $i++){
              mt_srand((double)microtime() * 1000000);
              $num = mt_rand(1,count($source));
              $rstr .= $source[$num-1];
     }
 
     return $rstr;
     }
}

}
// END CI_Encrypt class
/* End of file Encrypt.php */