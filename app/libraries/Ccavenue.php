<?php
 defined('BASEPATH') OR exit('No direct script access allowed'); 
class Ccavenue {
    const version = '1.1';

    protected $merchant_id = null;
    protected $access_code = null;
    protected $working_key = '';

    /**
    * @param string $merchant_id
    * @param string $access_code is available on the d
    * @param string $working_key can be set if you are working on an alternative server.
    * @return array AuthToken object.
    */
    public function __construct($merchant_id=null, $access_code=null, $working_key=null) 
    {
        $this->merchant_id =   $merchant_id;
        $this->access_code = (string) $access_code;
        $this->working_key = (string) $working_key;   
    }

    public function getPostData($arr){
        $merchant_data = $this->merchant_id;
	$working_key   = $this->working_key;//Shared by CCAVENUES 
	foreach ($arr as $key => $value){
            $merchant_data.=$key.'='.urlencode($value).'&';
	}
	return $this->encrypt($merchant_data,$working_key);
    }
    
    public function getResultData($arr) {
        $working_key   = $this->working_key;//Shared by CCAVENUES  
        $rcvdString=$this->decrypt($arr,$working_key);		
        $decryptValues=@explode('&', $rcvdString);
        $returnArr =  array(); 
        for($i = 0; $i < count($decryptValues); $i++){
		$information=explode('=',$decryptValues[$i]); 
                $returnArr[$information[0]] = urldecode($information[1]);
	}
        return $returnArr;
    }
    
    private function encrypt($plainText,$key){
        $secretKey = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
        return bin2hex($encryptedText);
    } 
    
    private function decrypt($encryptedText,$key){
        $secretKey = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hextobin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }
        
       //*********** Padding Function *********************

    private function pkcs5_pad ($plainText, $blockSize){
        $pad = $blockSize - (strlen($plainText) % $blockSize);
        return $plainText . str_repeat(chr($pad), $pad);
    }

	//********** Hexadecimal to Binary function for php 4.0 version ********

    private function hextobin($hexString){ 
        $length = strlen($hexString); 
        $binString="";   
        $count=0; 
        while($count<$length) 
        {       
            $subString =substr($hexString,$count,2);           
            $packedString = pack("H*",$subString); 
            if ($count==0){
                $binString=$packedString;
            }   
            else{
                $binString.=$packedString;
            } 

            $count+=2; 
        } 
        return $binString; 
    }  
}
?>