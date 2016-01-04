<?php
/**
 * Description of auth
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Auth extends Controller {
    
    /**
     * @before _session
     */
    public function login() {
        $this->defaultLayout = "layouts/blank";
        $this->setLayout();
        $this->seo(array(
            "title" => "Login",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        if (RequestMethods::get("action") == "reset") {
            $exist = User::first(array("email = ?" => RequestMethods::get("email")), array("id", "email", "name"));
            if ($exist) {
                $this->notify(array(
                    "template" => "forgotPassword",
                    "subject" => "New Password Requested",
                    "user" => $exist
                ));

                $view->set("message", "Password Reset Email Sent Check Your Email. Check in Spam too.");
            }
        }
        
        if (RequestMethods::post("action") == "login") {
            $email = RequestMethods::post("email");
            $exist = User::first(array("email = ?" => $email), array("id", "email"));
            if($exist) {
                $user = User::first(array(
                    "email = ?" => RequestMethods::post("email"),
                    "password = ?" => sha1(RequestMethods::post("password"))
                ));
                if($user) {
                    $login = Meta::first(array("property = ?" => "login"));
                    if($login->value == "yes") {
                        if ($user->live) {
                            $this->setUser($user);
                            $this->session();
                        } else {
                            $view->set("message", "User account not verified");
                        }
                    } else {
                        if ($user->admin) {
                            $this->setUser($user);
                            $this->session();
                        }
                        $view->set("message", "We are Updating Login after sometime");
                    }
                } else{
                    $view->set("message", 'Wrong Password, Try again or <a href="/auth/login?action=reset&email='.$email.'">Reset Password</a>');
                }
                
            } else {
                $view->set("message", 'User doesnot exist. Please signup <a href="/auth/register">here</a>');
            }
        }
    }
    
    /**
     * @before _session
     */
    public function register() {
        $this->defaultLayout = "layouts/blank";
        $this->setLayout();
        $this->seo(array(
            "title" => "Register",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();
        
        if (RequestMethods::post("action") == "register" && $this->reCaptcha()) {
            $exist = User::first(array("email = ?" => RequestMethods::post("email")));
            if (!$exist) {
                $user = new User(array(
                    "username" => RequestMethods::post("username"),
                    "name" => RequestMethods::post("name"),
                    "email" => RequestMethods::post("email"),
                    "password" => sha1(RequestMethods::post("password")),
                    "phone" => RequestMethods::post("phone"),
                    "admin" => 0,
                    "domain" => "",
                    "fblink" => RequestMethods::post("fblink"),
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
                $this->notify(array(
                    "template" => "publisherRegister",
                    "subject" => "Welcome to EarnBugs.in",
                    "user" => $user
                ));
                $view->set("message", "Your account has been created and will be activate within 3 hours after verification.");
            } else {
                $view->set("message", 'Username exists, login from <a href="/admin/login">here</a>');
            }
        }
    }

    public function forgotpassword() {
        $this->defaultLayout = "layouts/blank";
        $this->setLayout();
        $this->seo(array(
            "title" => "Forgot Password",
            "view" => $this->getLayoutView()
        ));
        $view = $this->getActionView();

        if (RequestMethods::post("action") == "change") {
            $token = RequestMethods::post("token");
            $id = base64_decode($token);
            $user = User::first(array("id = ?" => $id));
            if(RequestMethods::post("password") == RequestMethods::post("cpassword")) {
                $user->password = sha1(RequestMethods::post("password"));
                $user->save();
                $this->setUser($user);
                $this->session();
            } else{
                $view->set("message", 'Password Does not match');
            }
        }

        if (RequestMethods::get("action") == "reset") {
            $token = RequestMethods::get("token");
            $id = base64_decode($token);
            $exist = User::first(array("id = ?" => $id), array("id"));
            if($exist) {
                $view->set("token", $token);
            } else{
                $view->set("message", 'Something Went Wrong please contact admin');
            }
        }
    }

    protected function session() {
        $session = Registry::get("session");
        $where = array(
            "property = ?" => "domain",
            "live = ?" => true
        );
        $domains = Meta::all($where);
        $session->set("domains", $domains);
        self::redirect("/publisher");
    }

    protected function reCaptcha() {
        $g_recaptcha_response = RequestMethods::post("g-recaptcha-response", '03AHJ_Vut4zQ1SeLzhjSRqza49bNphfWsmpzejAkFADRA5hUN9hcBZdDq6fr8iA8aUhmJPlWxrG_7ImqQ3mheofTBVoT8MhUJRWNLTGpUofNVeVPREHmVqBPOQC80S70Gpt5UzT4E390NOhrOxc93gR5-h998bllqaWF180_yyMX2JqsfsgBx8R1gED5sJ8Zu8M7aRVRrAMBvfx8BBmUOG5QPnvPhcdIaq9pkCYiNC2Smr_JDLoL9jeB2uFGFsNaZaNuAIqffi9d_aF0HypLlo3TOCyhrPSeIz8lLmMpamOGjJqq_62SufW4vJ4ZYBSG4dBW0J_g7saqWljmULRJqW9veba-3AIErK7cDoDmgZCP9HtKDgx2ZOQXkvS_8Rnqwj-iAXFmHsLmOJ8CBMQ4j0dYwEHelkwlL6q5hUOqswrWrCab8eZ8xXEhme-qsjoHeTSlbwGSXHW5i-gPV_-qKOkWwxGDKkFzk6QkaJ6vRXRoGygaTcYQXO4ClDaUZOgKPJEhK0LbS-Hs4excNlCk-Ff23wiwGcyuqOOZ1oRH-L9X1eNwqHd-MFFZoPG99s45gURcHA4UMEPkAy60WT8BHGD1Z3_HbsVTX7Ana9d0vyuhr0ou7E0kWfwdHCrEQ5jOpp2J4z4Kn6aeMnpExO028-4VhAL-pGy_gcErEohBaQO7BZPtR4jB7iC4iytQrl6u5KDaSkWHO9KunFgO3UCHzoA3C08z6PjkU4Eq5fYSy5bwTMH_R6WHsH3RON0VQwJCuJu8XlwnDwKqC5siYVJ5EVE50r0xNEAGQgPUjwZIHFzoZO0g7NejN_1p3hgzpoAYFaW6mSzXxK08aaLD0nbxRWxWO9fbIIPQfRVLnkSMHikTZyqwPBJyVQJlEe65CFV1KdDzYUsAOZH3wUKFRC1L4EgkRLlvFnq1wlRA');
        $curl = new Curl();
        $curl->post('https://www.google.com/recaptcha/api/siteverify', array(
            'secret' => '6LfRZRQTAAAAABxnjW_9e6x_BgzVc_b2ghnxmE8D',
            'response' => $g_recaptcha_response
        ));
        return $curl->response->success;
    }

    /**
     * @before _secure, _admin
     */
    public function loginas($user_id) {
        $this->setUser(false);
        $user = User::first(array("id = ?" => $user_id));
        $this->setUser($user);
        $this->session();
    }
    
    /**
     * The Main Method to return SendGrid Instance
     * 
     * @return \SendGrid\SendGrid Instance of Sendgrid
     */
    protected function sendgrid() {
        $configuration = Registry::get("configuration");
        $parsed = $configuration->parse("configuration/mail");

        if (!empty($parsed->mail->sendgrid) && !empty($parsed->mail->sendgrid->username)) {
            $sendgrid = new \SendGrid\SendGrid($parsed->mail->sendgrid->username, $parsed->mail->sendgrid->password);
            return $sendgrid;
        }
    }
    
    protected function getBody($options) {
        $template = $options["template"];
        $view = new Framework\View(array(
            "file" => APP_PATH . "/application/views/layouts/email/{$template}.html"
        ));
        foreach ($options as $key => $value) {
            $view->set($key, $value);
            $$key = $value;
        }

        return $view->render();
    }
    
    protected function notify($options) {
        $body = $this->getBody($options);
        $emails = isset($options["emails"]) ? $options["emails"] : array($options["user"]->email);

        switch ($options["delivery"]) {
            default:
                $sendgrid = $this->sendgrid();
                $email = new \SendGrid\Email();
                $email->setSmtpapiTos($emails)
                        ->setFrom('info@earnings.in')
                        ->setFromName("EarnBugs Team")
                        ->setSubject($options["subject"])
                        ->setHtml($body);
                $sendgrid->send($email);
                break;
        }
        $this->log(implode(",", $emails));
    }
    
    protected function log($message = "") {
        $logfile = APP_PATH . "/logs/" . date("Y-m-d") . ".txt";
        $new = file_exists($logfile) ? false : true;
        if ($handle = fopen($logfile, 'a')) {
            $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
            $content = "[{$timestamp}]{$message}\n";
            fwrite($handle, $content);
            fclose($handle);
            if ($new) {
                chmod($logfile, 0755);
            }
        } else {
            echo "Could not open log file for writing";
        }
    }

    protected function changeDate($date, $day) {
        return date_format(date_add(date_create($date),date_interval_create_from_date_string("{$day} day")), 'Y-m-d');;
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
    protected function _upload($name, $type = "images") {
        $img_type = array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        );
        if (isset($_FILES[$name])) {
            $file = $_FILES[$name];
            $path = APP_PATH . "/public/assets/uploads/{$type}/";
            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
            $filename = uniqid() . ".{$extension}";

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $ext = array_search($finfo->file($file['tmp_name']), $img_type, true);
            if ($ext !== false) {
                if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
                    return $filename;
                }
            }
        }
        return FALSE;
    }
}
