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

    /**
     * @before _secure, changeLayout
     */
    public function content($id='') {
        $this->seo(array("title" => "Content Analytics", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $item = Item::first(array("id = ?" => $id));

        $earn = 0;
        $earnings = Earning::all(array("item_id = ?" => $item->id), array("amount"));
        foreach ($earnings as $earning) {
            $earn += $earning->amount;
        }

        $links = Link::count(array("item_id = ?" => $item->id));
        $rpm = RPM::count(array("item_id = ?" => $item->id));

        $view->set("item", $item);
        $view->set("earn", $earn);
        $view->set("links", $links);
        $view->set("rpm", $rpm);
    }
    
}
