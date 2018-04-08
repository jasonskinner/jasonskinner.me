<?php

namespace wpdFormAttr\Login;

class SocialLogin {

    private static $_instance = null;
    private $generalOptions;

    private function __construct() {
        add_action('wpdiscuz_init_options',array(&$this,'initGeneralOption'));
    }
    
    public function initGeneralOption($options){
        $this->generalOptions = $options;
    }
    
    
       
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
