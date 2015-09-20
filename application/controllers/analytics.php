<?php

/**
 * Description of analytics
 *
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Analytics extends Admin {
    
    /**
     * @before _secure, changeLayout
     */
    public function full() {
        $this->seo(array("title" => "shortURL Analytics", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        if (RequestMethods::get("shortURL")) {
            $shortURL = RequestMethods::get("shortURL");
            $googl = Registry::get("googl");
            $object = $googl->analyticsFull($shortURL);
            //echo '<pre>', print_r($object), '</pre>';
            
            $view->set("shortURL", $shortURL);
            $view->set("googl", $object);
        }
    }
    
}
