<?php

$c = curl_init();
if($c === false)
    throw new Exception('Error initialising Curl');
$url = "http://devservice.artmaps.org.uk/service/tate2/rest/v1/objectsofinterest/search/?boundingBox.northEast.latitude=9000000000&boundingBox.southWest.latitude=-9000000000&boundingBox.northEast.longitude=18000000000&boundingBox.southWest.longitude=-18000000000";
if(!curl_setopt($c, CURLOPT_URL, $url))
    throw new Exception(curl_error($c));
if(!curl_setopt($c, CURLOPT_RETURNTRANSFER, 1))
    throw new Exception(curl_error($c));
$data = curl_exec($c);
if($data === false)
    throw new Exception(curl_error($c));
curl_close($c);
unset($c);
$objects = array_filter(json_decode($data), function($object) {
    return count($object->locations) > 1;
});
echo count($objects);
echo "\n";

?>