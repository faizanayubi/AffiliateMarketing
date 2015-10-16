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
            
            $rpms = RequestMethods::post("rpm");
            foreach ($rpms as $key => $value) {
                $rpm = new RPM(array(
                    "item_id" => $item->id,
                    "value" => $value,
                    "country" => $key
                ));
                $rpm->save();
            }
            $view->set("success", true);
        }
    }
    
    protected function target() {
        $alias = array("http://bollychitchat.in", "http://filmyhub.website", "http://teamkapil.website", "http://teamfilmy.biz");
        return $alias[rand(0, 3)];
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function manage() {
        $this->seo(array("title" => "Manage Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $page = RequestMethods::get("page", 1);
        $limit = RequestMethods::get("limit", 10);
        
        $contents = Item::all(array(), array("id", "title", "created", "url"), "created", "desc", $limit, $page);
        $count = Item::count();
        $view->set("contents", $contents);
        $view->set("page", $page);
        $view->set("count", $count);
        $view->set("limit", $limit);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function platforms() {
        $this->seo(array("title" => "New User Platforms", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        
        $date = RequestMethods::get("date", date('Y-m-d', strtotime("now")));
        $live = RequestMethods::get("live", 0);
        $users = User::all(array("live = ?" => $live, "created LIKE ?" => "%{$date}%"), array("id","name", "created"));
            
        $view->set("users", $users);
        $view->set("date", $date);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function edit($id = NULL) {
        $this->seo(array("title" => "Edit Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $item = Item::first(array("id = ?" => $id));
        $rpm_in = RPM::first(array("item_id = ?" => $item->id, "country = ?" => "IN"));
        $rpm_us = RPM::first(array("item_id = ?" => $item->id, "country = ?" => "US"));
        $rpm_pk = RPM::first(array("item_id = ?" => $item->id, "country = ?" => "PK"));
        $rpm_au = RPM::first(array("item_id = ?" => $item->id, "country = ?" => "AU"));
        $rpm_nw = RPM::first(array("item_id = ?" => $item->id, "country = ?" => "NW"));
        $rpm_none = RPM::first(array("item_id = ?" => $item->id, "country = ?" => "NONE"));
        
        if (RequestMethods::post("action") == "update") {
            $item->title = RequestMethods::post("title");
            $item->url = RequestMethods::post("url");
            $item->target = RequestMethods::post("target");
            $item->description = RequestMethods::post("description");
            $item->live = RequestMethods::post("live", "0");
            
            $item->save();

            $rpm_in->value = RequestMethods::post("rpm_in");
            $rpm_in->save();
            $rpm_us->value = RequestMethods::post("rpm_us");
            $rpm_us->save();
            $rpm_pk->value = RequestMethods::post("rpm_pk");
            $rpm_pk->save();
            $rpm_au->value = RequestMethods::post("rpm_au");
            $rpm_au->save();
            $rpm_nw->value = RequestMethods::post("rpm_nw");
            $rpm_nw->save();
            $rpm_none->value = RequestMethods::post("rpm_none");
            $rpm_none->save();

            $view->set("success", true);

            $view->set("errors", $item->getErrors());
        }
        $view->set("item", $item);
        $view->set("rpm_in", $rpm_in);
        $view->set("rpm_us", $rpm_us);
        $view->set("rpm_pk", $rpm_pk);
        $view->set("rpm_au", $rpm_au);
        $view->set("rpm_nw", $rpm_nw);
        $view->set("rpm_none", $rpm_none);
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
    public function fraudLinks() {
        $this->seo(array("title" => "Fraud Links", "view" => $this->getLayoutView()));
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
