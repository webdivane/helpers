<?php 
helper::uses(["common"], basename(__FILE__,".php"));

/** The cf (Common Functions) helper class */
class cf extends common {

    /**Config data of the helper - allowing override at the runtime.
     * 
     * [    "dumps-protect-fileinfo"    =>  (null)|path-only ] 
     * @var array */
    static $config = null;
    
    /**Regular php `var_dump()` replacement - dump(ing) passed args, showing self call position. */
    final static public function vd(){
        //if(!headers_sent()){ echo "<meta charset=\"UTF-8\">\n";}
        if(!headers_sent()){ header('Content-Type: text/html; charset=utf-8'); }
        echo "<pre contenteditable>";
        foreach(func_get_args() as $value){
            var_dump($value);
        }
        echo "</pre>";        
        self::callTrace();
    }

    /** Regular php `die(var_dump())` replacement - dies dump(ing) passed args, showing self call position. */
    final static public function vdd(){
        if(!headers_sent()){ header('Content-Type: text/html; charset=utf-8'); }
        echo "<pre contenteditable>";
        foreach(func_get_args() as $value){
            var_dump($value);
        }
        echo "</pre>";
        self::callTrace(); 
        exit();
    }

    /** Dies, showing a string message.
     *  @param string $msg
     *  @param integer $callIndex  - the index from the debug_backtrace() to be shown below the message */
    final static function end($msg, $callIndex = 0){
        if(!headers_sent()){ header('Content-Type: text/html; charset=utf-8'); }
        echo "<pre contenteditable>".$msg."</pre>";
        self::callTrace(($callIndex+2)); //Call index is expanded with 2 -> skips the cf::end() & cf::callTrace() calls.
        exit();
    }
    
    /** Prints the call func label and if allowed filename and line of the call position
     *  @param integer $index */
    final static function callTrace($index=2){
        $callers=debug_backtrace();

        $class = isset($callers[$index], $callers[$index]["class"]) ? $callers[$index]["class"]."::" : null;

        $labels = array("end"=>"End", "vdd"=>"End dump", "vd"=>"Dump");
        $info = $labels[($function=$callers[1]["function"])] ." triggered from: " . $class . $function . "()";

        switch (self::$config["dumps-protect-fileinfo"]) {
            case 'path-only': // class-filename.php (line: 123).
                $info.= basename($callers[$index]["file"]) . " (line: " . $callers[$index]["line"] . ").";
                break;
            case null: // .full-pah-to/class-filename.php (line: 123).
                $info.= $callers[$index]["file"] . " (line: " . $callers[$index]["line"] . ").";
                break;
            default:
                $info .= ".";
                break;
        }
        echo "<p style=\"font-family:Tahoma,'sans-serif'; padding-top:5px; font-size:12px; color:#888; border-top:1px dashed #888; margin:0px;\">".$info."</p>";
    }

    static function defaultCkSettings(){
        $ckSettings = array();
        $ckSettings["removePlugins"] = "tabletools,table,image";
        $ckSettings["removeButtons"] = "HorizontalRule,SpecialChar,Anchor";
        //$ckSettings["disallowedContent"] = "img script div input [class] [on*] [style]";
        $ckSettings["height"] = "50";
        $ckSettings["toolbar"] = "Basic";
        $ckSettings["toolbarLocation"] = "bottom";
        return $ckSettings;
    }
}
cf::$config = helper::config("cf");