<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;

class Member extends Controller {
    
    public function login() {
        $this->defaultLayout = "layouts/blank";
        $this->setLayout();
        $this->seo(array(
            "title" => "Login",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        if (RequestMethods::post("action") == "login") {
            $user = User::first(array(
                "email = ?" => RequestMethods::post("email"),
                "password = ?" => sha1(RequestMethods::post("password")),
                "validity" => TRUE
            ));
            if ($user) {
                $this->setUser($user);
                self::redirect("/member");
            } else {
                $view->set("message", "User not exist or blocked");
            }
        }
    }
    
    public function register() {
        
    }
}
