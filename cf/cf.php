<?php 
helper::uses(["common"], basename(__FILE__,".php"));

/** The cf (Common Functions) helper class */
class cf extends common {

    static $config = null;
    
    /**Regular php `var_dump()` replacement - writes variables dump (in a pre tag), showing the own call position. */
    final static public function vd(){
        $args = func_get_args();

        echo "<pre>";
        foreach($args as $value){
            var_dump($value);
        }
        
        $callers=debug_backtrace();
        
        $index = in_array($callers[1]["function"],array("forward_static_call", "forward_static_call_array")) ? 2 : 1;
        $class = isset($callers[$index], $callers[$index]["class"]) ? $callers[$index]["class"]."::" : null;
        
        $callInfo = "Dump triggered from: "  .  $class . $callers[$index]["function"] . "()";
        $callInfo.= self::$protectFileinfoOnDumps===false ? " | " . $callers[($index-1)]["file"] . " (line: " . $callers[($index-1)]["line"] . ")" : null;

        echo "</pre><p style=\"font-family:Tahoma,sans-serif; padding-top:5px; font-size:12px; color:#888; border-top:1px dashed #888; margin:0px;\">".$callInfo."</p>";
    }

    /** Regular php `die(var_dump())` replacement - dies writing variables dump (in a pre tag), showing the own call position. */
    final static public function vdd(){
        $args = func_get_args();
        if(!headers_sent()){ echo "<meta charset=\"UTF-8\">\n";}
        echo "<pre contenteditable>";
        foreach($args as $value){
            var_dump($value);
        }
        $callers=debug_backtrace();
                $from= isset($callers[1], $callers[1]["class"], $callers[0]) ? 
                "Dump triggered from: "  .  $callers[1]["class"] . "::" . $callers[1]["function"] . " | " . $callers[0]["file"] . " (line: " . $callers[0]["line"] . ")" :
                "Dump triggered from: SomeClass::Method | some-class.php (line: ?)";
        die("</pre><p style=\"font-family:Tahome,sans-serif; padding-top:5px; font-size:12px; color:#ccc; border-top:1px dotted #ccc\">".$from."</p>");
    }

    /** Dies, showing and end message
     *  @param string $msg
     *  @param integer $callIndex  - the index from the debug_backtrace() to be shown below the message */
    final static function end($msg, $callIndex = 1){
        $callers=debug_backtrace();
        $from="The end triggered from: "  .  (isset($callers[$callIndex]["class"]) ? $callers[$callIndex]["class"] . "::" . $callers[$callIndex]["function"] . " | " : "") . $callers[$callIndex]["file"] . " (line: " . $callers[$callIndex]["line"] . ")";
        die("<pre style=\"font-family:Tahoma,sans-serif; color:#888;\">".  $msg."</pre><p style=\"font-family:Tahoma,sans-serif; padding-top:5px; font-size:12px; color:#ccc; border-top:1px dotted #ccc\">".$from."</p>");        
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