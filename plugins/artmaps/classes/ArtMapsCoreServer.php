<?php
if(!class_exists('ArtMapsCoreServerException')) {
class ArtMapsCoreServerException
extends Exception {
    public function __construct($message = '', $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}}

if(!class_exists('ArtMapsCoreServer')) {
class ArtMapsCoreServer {

    const Version = 'v1';

    public static function registerBlog($name) {
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        require_once('ArtMapsCrypto.php');
        $cro = new ArtMapsCrypto();
        $sig = $cro->signString($name, $nw->getMasterKey());
        $input = "{\"name\":\"$name\",\"signature\":\"$sig\"}";
        $c = curl_init();
        if($c === false)
            throw new ArtMapsCoreServerException('Error initialising Curl');
        $url = $nw->getCoreServerUrl()
                . '/admin/rest/'
                . ArtMapsCoreServer::Version
                . '/context';
        if(!curl_setopt($c, CURLOPT_URL, $url))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_RETURNTRANSFER, 1))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'POST'))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_POSTFIELDS, $input))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($input))
                ))
            throw new ArtMapsCoreServerException(curl_error($c));
        $data = curl_exec($c);
        if($data === false)
            throw new ArtMapsCoreServerException(curl_error($c));
        curl_close($c);
        unset($c);
        $jd = json_decode($data);
        if($jd === null)
            throw new ArtMapsCoreServerException(
                    'Error decoding JSON data: ' . json_last_error());
        return $jd;
    }

    private $blog, $prefix;

    public function __construct(ArtMapsBlog $blog) {
        $this->blog = $blog;
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $this->prefix = $nw->getCoreServerUrl() . '/service/'
                . $blog->getName() . '/rest/' . self::Version . '/';
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function fetchObjectMetadata($objectID) {
        $c = curl_init();
        if($c === false)
            throw new ArtMapsCoreServerException('Error initialising Curl');
        $url = $this->prefix . "objectsofinterest/$objectID/metadata";
        if(!curl_setopt($c, CURLOPT_URL, $url))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_RETURNTRANSFER, 1))
            throw new ArtMapsCoreServerException(curl_error($c));
        $data = curl_exec($c);
        if($data === false)
            throw new ArtMapsCoreServerException(curl_error($c));
        curl_close($c);
        unset($c);
        $jd = json_decode($data);
        if($jd === null)
            throw new ArtMapsCoreServerException(
                    'Error decoding JSON data: ' . json_last_error());
        return $jd;
    }

    public function fetchCoreUserID(ArtMapsUser $user) {
        $c = curl_init();
        if($c === false)
            throw new ArtMapsCoreServerException('Error initialising Curl');
        $url = $this->prefix . 'users/search?URI=' . $this->blog->getName()
                . '://' . $user->getLogin();
        if(!curl_setopt($c, CURLOPT_URL, $url))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_RETURNTRANSFER, 1))
            throw new ArtMapsCoreServerException(curl_error($c));
        $data = curl_exec($c);
        if($data === false)
            throw new ArtMapsCoreServerException(curl_error($c));
        curl_close($c);
        unset($c);
        $jd = json_decode($data);
        if($jd === null || $jd == -1)
            throw new ArtMapsCoreServerException(
                    'Error decoding JSON data: ' . json_last_error());
        return $jd->ID;
    }

    public function doImport($file, $signature, $ID) {
        $c = curl_init();
        if($c === false)
            throw new ArtMapsCoreServerException('Error initialising Curl');
        require_once('ArtMapsNetwork.php');
        $nw = new ArtMapsNetwork();
        $url = $nw->getCoreServerUrl() . '/service/'
                . $this->blog->getName() . '/import/' . self::Version . '/csv/import';
        if(!curl_setopt($c, CURLOPT_URL, $url))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_RETURNTRANSFER, 1))
            throw new ArtMapsCoreServerException(curl_error($c));
        if(!curl_setopt($c, CURLOPT_POST, true))
            throw new ArtMapsCoreServerException(curl_error($c));
        $post = array(
                'signature' => $signature,
        		'callback' => "http://somethingorother", 
                'file' => "@$file"
        );
        if(!curl_setopt($c, CURLOPT_POSTFIELDS, $post))
            throw new ArtMapsCoreServerException(curl_error($c));
        $data = curl_exec($c);
        if($data === false)
            throw new ArtMapsCoreServerException(curl_error($c));
        $info = curl_getinfo($c);
        curl_close($c);
        unset($c);
        if($info['http_code'] != 200)
            throw new ArtMapsCoreServerException('Import failed');
    }
}}
?>