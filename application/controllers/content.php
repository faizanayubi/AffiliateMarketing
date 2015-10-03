<?php

/**
 * Description of content
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Content extends Admin {
    
    /**
     * @before _secure, changeLayout
     */
    public function create() {
        $this->seo(array("title" => "Create Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::post("action") == "content") {
            $item = new Item(array(
                "url" =>  RequestMethods::post("url"),
                "title" => RequestMethods::post("title"),
                "image" => $this->_upload("image", "images"),
                "target" => $this->target(),
                "description" => RequestMethods::post("description"),
                "user_id" => $this->user->id
            ));
            $item->save();
            
            $rpm = new RPM(array(
                "item_id" => $item->id,
                "value" => RequestMethods::post("value"),
                "country" => RequestMethods::post("country")
            ));
            $rpm->save();
            $view->set("success", true);
        }
    }
    
    protected function target() {
        $alias = array("http://bollychitchat.in", "http://filmyhub.website", "http://teamkapil.website", "http://teamfilmy.biz");
        //return $alias[rand(0, 3)];
        return $alias[0];
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function manage() {
        $this->seo(array("title" => "Manage Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        $contents = Item::all(array(), array("id", "title", "created"));
        $view->set("contents", $contents);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function platforms() {
        $this->seo(array("title" => "New User Platforms", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::get("action") == "findUser") {
            $date = RequestMethods::get("date", date('Y-m-d', strtotime("now")));
            $live = RequestMethods::get("live", 0);
            $users = User::all(array("live = ?" => $live, "created LIKE ?" => "%{$date}%"), array("id","name", "created"));
            $view->set("users", $users);
        }
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function edit($id = NULL) {
        $this->seo(array("title" => "Edit Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $item = Item::first(array("id = ?" => $id));
        $rpm = RPM::first(array("item_id = ?" => $item->id));
        
        if (RequestMethods::post("action") == "update") {
            $item->title = RequestMethods::post("title");
            $item->url = RequestMethods::post("url");
            $item->target = RequestMethods::post("target");
            $item->description = RequestMethods::post("description");
            $item->live = RequestMethods::post("live", "0");
            $rpm->value = RequestMethods::post("value");
            $rpm->country = RequestMethods::post("country");
            
            $item->save();
            $rpm->save();
            $view->set("success", true);
            $view->set("errors", $item->getErrors());
        }
        $view->set("item", $item);
        $view->set("rpm", $rpm);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function shortenURL() {
        $this->seo(array("title" => "Shorten URL", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        if (RequestMethods::get("longURL")) {
            $longURL = RequestMethods::get("longURL");
            $googl = Registry::get("googl");
            $object = $googl->shortenURL($longURL);
            
            $view->set("shortURL", $object->id);
            $view->set("googl", $object);
        }
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function delete($id = NULL) {
        $this->seo(array("title" => "Delete Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $item = Item::first(array("id = ?" => $id));
        $item->delete();

        $earning = Earning::first(array("item_id = ?" => $item->id));
        $earning->delete();

        $links = Link::all(array("item_id = ?" => $item->id));
        foreach ($links as $link) {
            $stat = Stat::all(array("link_id = ?" => $link->id));
            $stat->delete();
            $link->delete();
        }

        $rpms = RPM::all(array("item_id = ?" => $item->id));
        foreach ($rpms as $rpm) {
            $rpm->delete();
        }

        $view->set("success", "true");
    }
}
