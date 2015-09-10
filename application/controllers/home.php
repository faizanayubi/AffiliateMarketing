<?php

/**
 * The Default Example Controller Class
 *
 * @author Faizan Ayubi
 */
use Framework\Controller as Controller;

class Home extends Controller {

    public function index() {
        $this->getLayoutView()->set("seo", Framework\Registry::get("seo"));
    }
    
    public function privacy() {
        $this->seo(array(
            "title" => "Privacy Policy",
            "view" => $this->getLayoutView()
        ));
    }
    
    public function termsofuse() {
        $this->seo(array(
            "title" => "Terms of Use",
            "view" => $this->getLayoutView()
        ));
    }

}
