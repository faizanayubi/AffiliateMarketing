<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;

class Member extends Auth {
    
    /**
     * @before _secure, changeLayout
     */
    public function index() {
        $this->seo(array(
            "title" => "Dashboard",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        $links = Link::all(array("user_id = ?" => $this->user->id), array("item_id", "short", "created"), "created", "desc", 10, 1);
        $view->set("links", $links);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function stats() {
        $this->seo(array(
            "title" => "Stats Charts",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function topearners() {
        $this->seo(array(
            "title" => "Top Earners",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function links() {
        $this->seo(array(
            "title" => "Links",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function earnings() {
        $this->seo(array(
            "title" => "Earnings",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function faqs() {
        $this->seo(array(
            "title" => "FAQs",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function profile() {
        $this->seo(array(
            "title" => "Profile",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function payments() {
        $this->seo(array(
            "title" => "Payments",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
    }
    
    public function changeLayout() {
        $this->defaultLayout = "layouts/member";
        $this->setLayout();
    }
}
