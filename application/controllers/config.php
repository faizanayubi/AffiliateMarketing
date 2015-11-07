<?php

/**
 * Description of marketing
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Config extends Admin {

    /**
     * @before _secure, changeLayout
     */
    public function mail() {
        $this->seo(array("title" => "Mail Config", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $configuration = Registry::get("configuration");
        $parsed = $configuration->parse("configuration/mail");
        echo "<pre>", print_r($parsed), "</pre>";

        $view->set("text", $text);
    }

    /**
     * @before _secure, changeLayout
     */
    public function test() {
    	
    }

}
