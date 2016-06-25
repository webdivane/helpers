<?php
/** The message helper class */
class msg {
    
    const MSG_SESS_VAR = "web-app-msgs";

    const USE_BOOTSTRAP = false;
    private static $bootstrapTypeToClass = array(
        "plain"=>"alert-info", "success"=>"alert-success", "error"=>"alert-danger", "warning"=>"alert-warning"
    );

    /**Sets message in session
     * @param string $msg The message content
     * @param string $type - one of  ["plain"(blue),"success"(green),"error"(red), "warning"(yellow)] */
    final static function set($msg="", $type="plain"){
        self::prepare(true);
        $msgs = & $_SESSION[self::MSG_SESS_VAR];
        if(!is_array($msgs)){
            $msgs = array();
        }
        if(!in_array($type, array_keys(self::$bootstrapTypeToClass))){
            die("Incorrect msg type.");
        }
        $msgs[] = array("value"=>$msg, "type"=>$type);
    }
    
    /**Shows session messages*/
    final static function get(){
        self::prepare(true);
        $msgs = isset($_SESSION[self::MSG_SESS_VAR]) ? $_SESSION[self::MSG_SESS_VAR] : array();
        foreach ($msgs as $msg){
            self::Html($msg);
        }
        unset($_SESSION[self::MSG_SESS_VAR]);
    }
    
    /** Just shows a message
     * @param array|string $msg  - is is_array($msg) the $type is ignored as type is expected to be set in the array inside
     * @param string $type = "plain" */
    final static function put($msg, $type="plain"){
        self::prepare(true);
        $msgs = is_array($msg) ? $msg : array(array("value"=>$msg, "type"=>$type));
        foreach ($msgs as $msg){
            self::Html($msg);
        }
    }
	
    private static function Html($msg){
        if(!(bool)self::USE_BOOTSTRAP){
            form::openP(array("class"=>"app-msg ".$msg["type"]));
                form::html($msg["value"]);
                form::link("javascript:void(0);","close",array("class"=>"material-icons","onclick"=>"var _this = this; setTimeout(function(){_this.parentNode.style.display = 'none';},400); this.parentNode.setAttribute('class',(this.parentNode.getAttribute('class')+' msg-fadeout'));"));
            form::closeP();
        } else {
            form::openP(array("class"=>"alert alert-dismissible ".self::$bootstrapTypeToClass[$msg["type"]]));
                form::openButton(array("type"=>"button", "class"=>"close", "data-dismiss"=>"alert", "aria-label"=>"Close"));
                    form::span("&times;",array("aria-hidden"=>"true"));
                form::closeButton();
                form::html($msg["value"]);
                form::link("javascript:void(0);","&nbsp;",array("onclick"=>"var _this = this; setTimeout(function(){_this.parentNode.style.display = 'none';},400); this.parentNode.setAttribute('class',(this.parentNode.getAttribute('class')+' msg-fadeout'));"));
            form::closeP();
        }
    }
    
    static function prepare($setOrCheck = false){
        if( helper::prepare($setOrCheck, (__METHOD__)) !== true ){
            return; //Stops the further execution in case the helper call doesn't return true.
        }
        //Starts the session if its not started yet.
        if (@!session_status() || @session_status() == PHP_SESSION_NONE) {
            @session_start();
        }
        cf::appendToHeadTag(ADD."helpers/msg/add/msg.css", "css");
    }
}