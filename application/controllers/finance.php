<?php

/**
 * Description of analytics
 *
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Finance extends Admin {
    
    /**
     * All earnings records of persons
     * 
     * @before _secure, changeLayout
     */
    public function records() {
        $this->seo(array("title" => "Records Finance", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $earnings = Earning::all();
    }

    /**
     * Finds the earning from a website
     * @before _secure, changeLayout
     */
    public function earnings() {
        $this->seo(array("title" => "Earnings Finance", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
    }

    /**
     * Earning on a Content
     * @before _secure, changeLayout
     */
    public function content($id='') {
        $this->seo(array("title" => "Content Finance", "view" => $this->getLayoutView()));
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
