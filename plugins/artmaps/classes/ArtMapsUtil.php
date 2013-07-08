<?php
if(!class_exists('ArtMapsUtil')) {
class ArtMapsUtil {

    public static function findThemeFile($file) {
        $files = array(
                get_stylesheet_directory() . '/' . $file,
                get_template_directory() . '/' . $file,
                plugin_dir_path(__FILE__) . '../' . $file
        );
        foreach($files as $f)
            if(file_exists($f))
                return $f;
        throw new Exception("Cannot find file '$file' in theme or plugin directories");
    }

    public static function findThemeUri($file) {
        $files = array(
                get_stylesheet_directory() . '/' . $file => get_stylesheet_directory_uri() . '/' . $file,
                get_template_directory() . '/' . $file => get_template_directory_uri() . '/' . $file,
                plugin_dir_path(__FILE__) . '../' . $file => plugins_url(basename(dirname(dirname(__FILE__)))) . '/' . $file
        );
        foreach($files as $f => $u)
        if(file_exists($f))
            return $u;
        throw new Exception("Cannot find file '$file' in theme or plugin directories");
    }

}}
?>