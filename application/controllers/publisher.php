<?php
/**
 * Description of publisher
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;
use MongoDB\MongoQB as MongoQB;

class Publisher extends Admin {
	
	/**
     * @before _secure, changeLayout, _admin
     */
	public function settings() {
		$this->seo(array("title" => "Settings", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $login = Meta::first(array("property = ?" => "login"), array("id", "value"));
        $commision = Meta::first(array("property = ?" => "commision"));

        if (RequestMethods::post("commision")) {
        	$commision->value = RequestMethods::post("commision");
        	$commision->save();
        }

        $view->set("login", $login);
        $view->set("commision", $commision);
	}

	/**
     * @before _secure, changeLayout, _admin
     */
    public function fraud() {
        $this->seo(array("title" => "Fraud Links", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
    }
}