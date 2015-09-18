<?php

/**
 * Description of content
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;

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
            $view->set("success", true);
        }
    }
    
    protected function target() {
        $alias = array("http://bollychitchat.in", "http://filyhub.website", "http://teamkapil.website", "http://teamfilmy.biz");
        return $alias[rand(0, 3)];
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
        
        if (RequestMethods::post("action") == "update") {
            $item->title = RequestMethods::post("title");
            $item->url = RequestMethods::post("url");
            $item->target = RequestMethods::post("target");
            $item->description = RequestMethods::post("description");
            $item->live = RequestMethods::post("live", "0");
            
            $item->save();
            $view->set("success", true);
            $view->set("errors", $item->getErrors());
        }
        $view->set("item", $item);
    }
    
    /**
     * @before _secure, changeLayout
     */
    public function delete($id = NULL) {
        $this->seo(array("title" => "Delete Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
    }
}
