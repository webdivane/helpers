<?php
/** The message helper class */
class msg {
    const MSG_SESS_VAR = "web-app-msgs";

    /**Sets message in session
     * @param str $msg
     * @param ste $type = "plain"|"success"|"error" */
    static function set($msg="", $type="plain"){
        self::prepare(true);
        $msgs = & $_SESSION[self::MSG_SESS_VAR];
        if(!is_array($msgs)){
            $msgs = array();
        }
        if(!in_array($type,array("plain","success","error"))){
            die("Incorrect msg type.");
        }
        $msgs[] = array("value"=>$msg, "type"=>$type);
    }
    
    /**Shows messages from session */
    static function get(){
        self::prepare(true);
        $msgs = isset($_SESSION[self::MSG_SESS_VAR]) ? $_SESSION[self::MSG_SESS_VAR] : array();
        for ($i = 0; $i < count($msgs); $i++){
            form::openP(array("class"=>"app-msg ".$msgs[$i]["type"]));
                form::html($msgs[$i]["value"]);
                form::link("javascript:void(0);","close",array("class"=>"material-icons","onclick"=>"var _this = this; setTimeout(function(){_this.parentNode.style.display = 'none';},400); this.parentNode.setAttribute('class',(this.parentNode.getAttribute('class')+' msg-fadeout'));"));
            form::closeP();
        }
        unset($_SESSION[self::MSG_SESS_VAR]);
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