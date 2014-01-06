<?php
if(!class_exists('ArtMapsTemplating')) {
require_once('Smarty/Smarty.class.php');
class ArtMapsTemplating {

    private function initSmarty($template) {
        require_once('ArtMapsUtil.php');
        $templateDir = dirname(ArtMapsUtil::findThemeFile("templates/$template"));
        $smarty = new Smarty();
        $smarty->setTemplateDir($templateDir);
        $smarty->setCompileDir("$templateDir/.compilation");
        $smarty->setCacheDir("$templateDir/.cache");
        $smarty->setConfigDir("$templateDir/.configuration");
        $smarty->registerPlugin('modifier', 'artmapsUri', array($this, 'artmapsUri'));
        $smarty->registerPlugin('modifier', 'wordpressSearch', array($this, 'wordpressSearch'));
        $smarty->registerPlugin('modifier', 'dynImage', array($this, 'dynImage'));
        return $smarty;
    }

    public function renderNetworkAdminPage(
            $updated, $coreServerUrl, $masterKeyIsSet, $mapKey) {
        $tpl = 'network_admin_page.html';
        $smarty = $this->initSmarty($tpl);
        $smarty->setCaching(false);
        $smarty->assign('updated', $updated);
        $smarty->assign('coreServerUrl', $coreServerUrl);
        $smarty->assign('masterKeyIsSet', $masterKeyIsSet);
        $smarty->assign('mapKey', $mapKey);
        return $smarty->fetch($tpl);
    }

    public function renderBlogAdminPage(ArtMapsBlog $blog, $updated) {
        $tpl = 'blog_admin_page.html';
        $smarty = $this->initSmarty($tpl);
        $smarty->setCaching(false);
        $smarty->assign('updated', $updated);
        $smarty->assign('searchSource', $blog->getSearchSource());
        $smarty->assign('users', get_users());
        $smarty->assign('postAuthor', $blog->getPostAuthor());
        $smarty->assign('postDate', $blog->getPostDate());
        $smarty->assign('categories', get_categories(array('hide_empty' => false)));
        $smarty->assign('postCategories', $blog->getPostCategories());
        $smarty->assign('jQueryThemeUri', $blog->getJQueryThemeUri());
        $smarty->assign('locationReasons', $blog->getLocationReasons());
        return $smarty->fetch($tpl);
    }

    public function renderImportAdminPage($imported, $imports) {
        $tpl = 'import_admin_page.html';
        $smarty = $this->initSmarty($tpl);
        $smarty->setCaching(false);
        $smarty->assign('imported', $imported);
        $smarty->assign('imports', $imports);
        return $smarty->fetch($tpl);
    }

    public function renderCommentTemplate(
            ArtMapsBlog $blog, $objectID, $link, $metadata) {
        $tpl = 'comment_template.html';
        $smarty = $this->initSmarty($tpl);
        $smarty->setCaching(true);
        $smarty->assign('objectID', $objectID);
        $smarty->assign('link', $link);
        $smarty->assign('metadata', $metadata);
        return $smarty->fetch($tpl, $objectID);
    }

    public function renderObjectPageTitleTemplate(ArtMapsBlog $blog, $metadata) {
        $tpl = 'object_page_title_template.html';
    	$smarty = $this->initSmarty($tpl);
    	$smarty->setCaching(false);
    	$smarty->assign('metadata', $metadata);
    	return $smarty->fetch($tpl);
    }

    public function renderMainMapTemplate(ArtMapsBlog $blog) {
        $tpl = 'main_map_template.html';
        $smarty = $this->initSmarty($tpl);
        $smarty->setCaching(true);
        return $smarty->fetch($tpl);
    }

    public function renderDigestEmail($objects) {
        $tpl = 'digest_email_template.txt';
        $smarty = $this->initSmarty($tpl);
        $smarty->setCaching(false);
        $smarty->assign('objects', $objects);
        $smarty->assign('siteUrl', site_url());
        return $smarty->fetch($tpl);
    }

    public function artmapsUri($file) {
        require_once('ArtMapsUtil.php');
        return ArtMapsUtil::findThemeUri($file);
    }

    public function wordpressSearch($term) {
        return get_search_link($term);
    }

    public function dynImage($url, $isWidth, $size) {
        if(function_exists("dynimage_get_url")) {
            return dynimage_get_url($url, $isWidth, $size);
        } else {
            return $url;
        }
    }
}}
?>