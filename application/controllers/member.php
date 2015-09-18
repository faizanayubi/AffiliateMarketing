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
        $click = 0;
        
        $links = Link::all(array("user_id = ?" => $this->user->id), array("item_id", "short", "created"), "created", "desc", 10, 1);
        foreach ($links as $link) {
            $stat = Stat::first(array("link_id = ?" => $link->id), array("shortUrlClicks"));
            $click += $stat->shortUrlClicks;
        }
        
        $view->set("links", $links);
        $view->set("earnings", $this->totalEarnings()["total"]);
        $view->set("pending", $this->totalEarnings()["pending"]);
        $view->set("click", $click);
    }
    
    protected function totalEarnings() {
        $total_earnings = 0;
        $pending = 0;
        $earnings = Earning::all(array("user_id = ?" => $this->user->id), array("amount", "live"));
        foreach ($earnings as $earning) {
            $total_earnings += $earning->amount;
            if($earning->live == "1") {
                $pending += $earning->amount;
            }
        }
        $earning = array(
            "total" => $total_earnings,
            "pending" => $pending
        );
        return $earning;
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
        $earners = Earning::all(array("live = ?" => 1), array("user_id", "amount"), "amount", "desc", 10, 1);
        $view->set("earners", $earners);
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
        
        $title = RequestMethods::get("title", "");
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $where = array(
            "title LIKE ?" => "%{$title}%",
            "live = ?" => true,
        );
        $items = Item::all($where, array("title", "image"), "created", "desc", $limit, $page);
        $count = Item::count($where);
        
        $view->set("limit", $limit);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("items", $items);
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
