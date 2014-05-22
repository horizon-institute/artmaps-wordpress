<?php
/*
Plugin Name: Horizon Dynamic Image Resizer
Plugin URI: http://www.horizon.ac.uk/
Version: v1.1.0
Author: <a href="http://www.horizon.ac.uk/">Horizon Digital Economy Research</a>
Description: Resizes images dynamically.
*/
if(!class_exists('DynImage')) {
class DynImage {

    private $config = array(
            'maxSize' => 2000,
            'quality' => 75,
            'allowedDomains' => array(
            		"beta2.tate.org.uk",
            		"www.tate.org.uk",
            		"dev.artmaps.org.uk",
            		"www.artmaps.org.uk",
            		"localhost",
            		"artmaps.tate.org.uk"),
            'cacheDir' => '/tmp/dynimage',
            'browserCacheTime' => 604800 // One week
    );

    public function __construct() {
        $this->config = (object)$this->config;
        if(!file_exists($this->config->cacheDir))
            mkdir($this->config->cacheDir, 0777, true);
    }

    private function checkSecurity($url, $isWidth, $size) {
        $domain = parse_url($url, PHP_URL_HOST);
        if(!in_array($domain, $this->config->allowedDomains))
            throw new Exception("$domain is not in the list of allowed domains");
    }

    private function cacheFilename($url, $isWidth, $size) {
        return $this->config->cacheDir . '/' .
                md5($url . '.' . ($isWidth ? 'x' : 'y') . '.' . $size);
    }

    public function displayImage($url, $isWidth, $size) {
        dynimage_get_url($url, $isWidth, $size);
        try {
            $this->checkSecurity($url, $isWidth, $size);
            $cacheFile = $this->cacheFilename($url, $isWidth, $size);

            if(!file_exists($cacheFile)) {
                $c = curl_init($url);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                $iData = curl_exec($c);
                if(!$iData)
                    throw new Exception('Curl error: ' . curl_error($c));
                $mimeType = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
                if($mimeType != 'image/jpeg' && $mimeType != 'image/png')
                    throw new Exception("Unable to handle mime-type: '$mimeType'");
                $source = imagecreatefromstring($iData);
                if(!$source)
                    throw new Exception('Unable to create image from image data');

                $oldWidth = imagesx($source);
                $oldHeight = imagesy($source);
                $scale = 1;
                if($size > $this->config->maxSize)
                    $size = $this->config->maxSize;
                if($isWidth) {
                    $scale = $size / $oldWidth;
                } else {
                    $scale = $size / $oldHeight;
                }
                $newWidth = $scale * $oldWidth;
                $newHeight = $scale * $oldHeight;
                $output = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($output, $source, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
                imagejpeg($output, $cacheFile, $this->config->quality);
            }

            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
                    && filemtime($cacheFile) <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                header('HTTP/1.1 304 Not Modified');
            } else {
                header('Expires: ' . gmdate(DATE_RFC2822, time() + $this->config->browserCacheTime));
                header('Last-Modified: '. gmdate(DATE_RFC2822, filemtime($cacheFile)));
                header('Cache-Control: max-age=' . $this->config->browserCacheTime);
                header("Content-type: image/jpeg");
                readfile($cacheFile);
            }

            die();
        }
        catch(Exception $e) {
            header("HTTP/1.1 404 Not Found");
            error_log('Unable to dynamically load and resize image (' . $url . '): ' . $e->getMessage());
            die();
        }
    }
}}

add_action('init', function() {
    global $wp_rewrite;
    $wp_rewrite->add_rewrite_tag('%url%', '(.+)', 'dynimage_url=');
    $wp_rewrite->add_rewrite_tag('%xory%', '(x|y)', 'dynimage_xory=');
    $wp_rewrite->add_rewrite_tag('%size%', '(\d+)', 'dynimage_size=');
    $wp_rewrite->add_permastruct('dynimage', 'dynimage/%xory%/%size%/%url%', false);
});

add_filter('query_vars', function($vars) {
    $vars[] = 'dynimage_url';
    $vars[] = 'dynimage_xory';
    $vars[] = 'dynimage_size';
    return $vars;
});

add_action('parse_request', function($wp) {
    if(array_key_exists('dynimage_url', $wp->query_vars)
            && array_key_exists('dynimage_xory', $wp->query_vars)
            && array_key_exists('dynimage_size', $wp->query_vars)) {

        $dynimage = new DynImage();
        $dynimage->displayImage(
                $wp->query_vars['dynimage_url'],
                $wp->query_vars['dynimage_xory'] === 'x',
                $wp->query_vars['dynimage_size']);
    }
});

function dynimage_get_url($url, $isWidth, $size) {
    return site_url('dynimage/' . ($isWidth ? 'x' : 'y')
            . '/' . $size . '/' . $url);
}

?>
