<?php
if(!class_exists('ArtMapsCryptoException')){
class ArtMapsCryptoException
extends Exception {
    public function __construct($message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}}

if(!class_exists('ArtMapsCrypto')) {
class ArtMapsCrypto {

    const SignatureAlgorithm = 'SHA256';

    public function signString($data, $key) {
        if(!is_string($data))
            throw new ArtMapsCryptoException('Data to sign must be a string');
        $k = openssl_pkey_get_private($key);
        if($k === false)
            throw new ArtMapsCryptoException(openssl_error_string());
        if(openssl_sign($data, $signature, $k, self::SignatureAlgorithm) === false)
            throw new ArtMapsCryptoException(openssl_error_string());
        openssl_free_key($k);
        $sig = base64_encode($signature);
        return $sig;
    }

    public function signData($data, $key, ArtMapsUser $user) {
        if(!is_array($data))
            throw new ArtMapsCryptoException('Data to sign must be an array');
        $data['username'] = $user->getLogin();
        $data['userLevel'] = implode(',', $user->getRoles());
        $data['timestamp'] = intval(time() * 1000);
        ksort($data);
        $k = openssl_pkey_get_private($key);
        if($k === false)
            throw new ArtMapsCryptoException(openssl_error_string());
        if(openssl_sign(implode($data), $signature, $k, self::SignatureAlgorithm) === false)
            throw new ArtMapsCryptoException(openssl_error_string());
        openssl_free_key($k);
        $data['signature'] = base64_encode($signature);
        if($data['signature'] === false)
            throw new ArtMapsCryptoException(
                    'There was an error base64 encoding the signature');
        return $data;
    }

    public function signFile($file, $key) {
        if(!file_exists($file) || ! is_file($file))
            throw new ArtMapsCryptoException('File to be signed does not exist');
        $k = openssl_pkey_get_private($key);
        if($k === false)
            throw new ArtMapsCryptoException(openssl_error_string());
        $data = file_get_contents($file);
        if(openssl_sign($data, $signature, $k, self::SignatureAlgorithm) === false)
            throw new ArtMapsCryptoException(openssl_error_string());
        openssl_free_key($k);
        $sig = base64_encode($signature);
        return $sig;
    }
}}
?>