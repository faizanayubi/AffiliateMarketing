<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;

class Auth extends Controller {
    
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
                "live" => TRUE
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
        $this->defaultLayout = "layouts/blank";
        $this->setLayout();
        $this->seo(array(
            "title" => "Register",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        if (RequestMethods::post("action") == "register") {
            $exist = User::first(array("email = ?" => RequestMethods::post("email")));
            if (!$exist) {
                $user = new User(array(
                    "username" => RequestMethods::post("username"),
                    "name" => RequestMethods::post("name"),
                    "email" => RequestMethods::post("email"),
                    "password" => sha1(RequestMethods::post("password")),
                    "phone" => RequestMethods::post("phone"),
                    "admin" => 0,
                    "live" => 0
                ));
                $user->save();
                
                $platform = new Platform(array(
                    "user_id" => $user->id,
                    "name" => "FACEBOOK_PAGE",
                    "link" =>  RequestMethods::post("link"),
                    "image" => $this->_upload("fbadmin", "images")
                ));
                $platform->save();
                $view->set("message", "Your account has been created and will be activate within 3 hours after verification.");
            } else {
                $view->set("message", 'Username exists, login from <a href="/admin/login">here</a>');
            }
        }
    }
    
    public function logout() {
        $this->setUser(false);
        self::redirect("/home");
    }
    
    public function noview() {
        $this->willRenderLayoutView = false;
        $this->willRenderActionView = false;
    }

    public function JSONview() {
        $this->willRenderLayoutView = false;
        $this->defaultExtension = "json";
    }
    
    /**
     * The method checks whether a file has been uploaded. If it has, the method attempts to move the file to a permanent location.
     * @param string $name
     * @param string $type files or images
     */
    protected function _upload($name, $type = "files") {
        if (isset($_FILES[$name])) {
            $file = $_FILES[$name];
            $path = APP_PATH . "/public/assets/uploads/{$type}/";
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename = uniqid() . ".{$extension}";
            if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
                return $filename;
            } else {
                return FALSE;
            }
        }
    }
}
