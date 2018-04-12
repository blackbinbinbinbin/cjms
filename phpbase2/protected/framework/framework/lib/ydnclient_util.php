<?php
//    require_once "Crypt/Blowfish.php";
    function hex2bin_t($data) {
        $len = strlen($data);
        return pack("H" . $len, $data);
    } 

    //url base64编码
    function urlsafe_b64encode2($string) {
        $data = base64_encode($string);
        //$data = str_replace(array('+','/','='),array('-','_',''),$data);
        $data = str_replace(array('+','/'),array('-','_'),$data);
        return $data;
    }
    //url base64解码
    function urlsafe_b64decode2($string) {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        //$mod4 = strlen($data) % 4;
        //if ($mod4) {
        //    $data .= substr('====', $mod4);
        //}
        return base64_decode($data);
    }

    function generate_random_str($len) {
        $randstr = "";
        for($i = 0; $i < $len; $i++) {
            $randstr .= chr(mt_rand(1, 254));
        }
        return $randstr;

    }
    
//     function verify_cookie($custom_cookie)
//     {
        
//         $custom_cookie = str_replace(' ', '+', $custom_cookie);
//         $msg = base64_decode($custom_cookie);
        
//         $bf =& Crypt_Blowfish::factory('cbc');
//         if (PEAR::isError($bf))
//         {
//             echo $bf->getMessage();
//             exit;
//         }
//         $iv = "fedcba9876543210";
//         $iv = hex2bin_t($iv);
//         $key = '123456)(*&^%$#@!';
//         $bf->setKey($key, $iv);
//         $plaintext = $bf->decrypt($msg);
//         if (PEAR::isError($plaintext))
//         {
//             echo $plaintext->getMessage();
//             exit;
//         }
//         //$plaintext = " 1:1386326134:3200090246:3836820663:app.12:8610000060009:860173011146044";
//         $cookie_list = explode(":", $plaintext);
//         //var_dump($cookie_list);
//         if(substr($cookie_list[4], 0, 3) == "app")
//         {
//             error_log("cookie verify success " . $cookie_list[4] );
//             return 1;
//         }
//         else
//         {
//             return 11111111111;
//         }


//         //echo "\n";
//         // Encrypted text is padded prior to encryption
//         // so you may need to trim the decrypted result.
//         error_log("plain text:  $plaintext");
//         return 1;
//     }
    function generate_token($accesskey, $access_secret, $method, $bucket, $filename, $expires)
    {

        $content = $method . "\n" . $bucket . "\n" . $filename . "\n" . $expires . "\n";
       
        $token = $accesskey . ":" . urlsafe_b64encode2(hash_hmac("sha1", $content, $access_secret, true)) . ":" . $expires;
        return $token;
    }
    
    function file_type($info) 
    {
        $strInfo = @unpack("C2chars", $info); 
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']); 
        $fileType = ''; 
        switch ($typeCode) 
        { 
            case 7790: 
                $fileType = 'exe'; 
                break; 
            case 7784: 
                $fileType = 'midi'; 
                break; 
            case 8297: 
                $fileType = 'rar'; 
                break; 
            case 255216: 
                $fileType = 'jpg'; 
                break; 
            case 7173: 
                $fileType = 'gif'; 
                break; 
            case 6677: 
                $fileType = 'bmp'; 
                break; 
            case 13780: 
                $fileType = 'png'; 
                break; 
            default: 
                $fileType = 'unknown'; 
        }
        error_log("filetype: $fileType");
        return $fileType;
    } 
?>