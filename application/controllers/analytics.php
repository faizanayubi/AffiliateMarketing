<?php

/**
 * Description of analytics
 *
 * @author Faizan Ayubi
 */
class Analytics extends Admin {
    
    /**
     * @before _secure, changeLayout
     */
    public function content($id = NULL) {
        $this->seo(array("title" => "Delete Content", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
    }
    
}
