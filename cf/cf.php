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
        self::backtrace();
    }

    /** Regular php `die(var_dump())` replacement - dies dump(ing) passed args, showing self call position. */
    final static public function vdd(){
        if(!headers_sent()){ header('Content-Type: text/html; charset=utf-8'); }
        echo "<pre contenteditable>";
        foreach(func_get_args() as $value){
            var_dump($value);
        }
        echo "</pre>";
        self::backtrace(); 
        exit();
    }

    /** Dies, showing a string message.
     *  @param string $msg
     *  @param integer $callIndex  - the index from the debug_backtrace() to be shown below the message */
    final static function end($msg, $callIndex = 0){
        if(!headers_sent()){ header('Content-Type: text/html; charset=utf-8'); }
        echo "<pre contenteditable>".$msg."</pre>";
        self::backtrace(($callIndex+2)); //Call index is expanded with 2 -> skips the cf::end() & cf::callTrace() calls.
        exit();
    }
    
    /** Call backtrace informer.
     *  Prints the call func label and if allowed filename and line of the call position
     *  @param integer $index */
    final static function backtrace($index=2){
        $callers=debug_backtrace();
        if(array_key_exists($index, $callers)){
            
            $labels = array("end"=>"End", "vdd"=>"End dump", "vd"=>"Dump");
            
            $caller = function($row, $labels) use ($labels) {
                $c = (isset($row) && array_key_exists("class",$row) ? $row["class"] : ""); // ClassName -or- ""
                $c.= empty($c) ? "" : $row["type"]; // "" -or- (:: / ->)
                $c.= ((in_array(($func=$row["function"]), array_keys($labels))) ? $labels[$func] : ($func."()")); //F unc -or- $labels[$func]
                return $c;
            };
            
            $call = ($caller($callers[$index]) . ", triggered from: " . $caller($callers[($index+1)])); // current(), .. parent()
            $path = self::$config["backtrace omit path"]===true  ? basename($callers[$index]["file"]) : $callers[$index]["file"];
            $info = ("<nowrap>".$call . ",</nowrap> <nowrap>" . $path . " (line: " . $callers[$index]["line"] . ")</nowrap>");

        } else {
            $info = "<em>debug_backtrace() not have data under requested index.</em>";
        }
        echo "<p style=\"font-family:Tahoma,'sans-serif'; padding-top:5px; font-size:12px; color:#888; border-top:1px dashed #888; margin:0px;\">".$info."</p>";
    }
}
cf::$config = helper::config("cf");