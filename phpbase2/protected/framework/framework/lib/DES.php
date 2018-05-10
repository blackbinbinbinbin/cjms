<?php

class DES {
    var $key;
    var $iv; // 偏移量

    function __construct($key, $iv = 0) {
        $this->key = $key;
        if ($iv == 0) {
            $this->iv = $key;
        } else {
            $this->iv = $iv;
        }
    }

    // 加密
    function encrypt_old($str) {
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad($str, $size);

        $data = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
        // $data=strtoupper(bin2hex($data)); //返回大写十六进制字符串
        return base64_encode($data);
    }

    // 解密
    function decrypt_old($str) {
        $str = base64_decode($str);
        // $strBin = $this->hex2bin( strtolower($str));
        $str = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_DECRYPT, $this->iv);
        $str = $this->pkcs5Unpad($str);
        return $str;
    }

    // 加密
    function encrypt($str) {
        $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $str = $this->pkcs5Pad($str, $size);

        $cipher = mcrypt_module_open(MCRYPT_DES,'','cbc','');
        mcrypt_generic_init($cipher, $this->key, $this->iv);
        $encrypted = mcrypt_generic($cipher, $str);
        mcrypt_generic_deinit($cipher);

        return base64_encode($encrypted);
    }

    // 解密
    function decrypt($str) {
        if (strpos($str, '-')) {
            return $str;
        }

        $str = base64_decode($str);

        $cipher = mcrypt_module_open(MCRYPT_DES,'','cbc','');
        mcrypt_generic_init($cipher, $this->key, $this->iv);
        $decrypted = mdecrypt_generic($cipher, $str);
        mcrypt_generic_deinit($cipher);

        $decrypted = $this->pkcs5Unpad($decrypted);

        return $decrypted;
    }

    function hex2bin($hexData) {
        $binData = "";
        for ($i = 0; $i < strlen($hexData); $i += 2) {
            $binData .= chr(hexdec(substr($hexData, $i, 2)));
        }
        return $binData;
    }

    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5Unpad($text) {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
        return substr($text, 0, -1 * $pad);
    }

}

 
