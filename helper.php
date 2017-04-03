<?php
/** The helper (base) class */
class helper {

    public static $pageExtension, $preparePassed = array();
    private static $config=null, $prepared = array();
    const HELPERS_DIR = __DIR__;
    const CONFIG_DIR = "config";
    const DS = DIRECTORY_SEPARATOR;

    /**Genereates a list of helpers to be used on the spl_autoload_register
     * @see ../boot/autoload.php
     * @return array  - active helpers lits to be registered */
    static function AutoLoadRegister(){
        $composer = json_decode(file_get_contents(($file=self::HELPERS_DIR .self::DS.($fn="composer.json"))));
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (function_exists('json_last_error_msg')) {
                $error_message = "error message:".json_last_error_msg().", ";
            }
            exit("Syntax error in ".$fn.". (".$error_message."file: ".$file);
        } 
        unset($composer->autoload->{"psr-0"}->helper); //composer autload list begins with the current class definition
        
        $alr = array();
        foreach(array_keys((array)$composer->autoload->{"psr-0"}) as $h){
            $alr[$h] = $h.self::DS.$h;
        }
        return $alr;
    }

    /**Returns the self::$config[$helper] values by a helper request.
     * On call withaut argument executes the initial config load (@see /config/config.json) */
    final static function config($helper=null){
        if(isset($helper)){
            return  (   array_key_exists($helper, self::$config) ?
                        self::$config[$helper] :
                        exit("Missing ".$helper." config data in the helpers/config/config.json")
                    );
        } else {
            $path = self::HELPERS_DIR .self::DS. self::CONFIG_DIR .DS;
            if(is_readable(($file=$path.($fn="config.json")))){
                $config = json_decode(file_get_contents($file), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    if (function_exists('json_last_error_msg')) {
                        $error_message = "error message:".json_last_error_msg().", ";
                    }
                    exit("Syntax error in ".$fn.". (".$error_message."file: ".$file);
                }
            }
            self::$config = isset($config) ? $config : exit("Missing config file ".$file.". (at ".$path);
        }
    }
    
    /**Register (pre-load) all files needed for the current helper
     * @param string|array $classes - the class names which the current helper extends (@note ClassName===FileName) 
     * @param string $helper - the current helper where current method was called , i.e. <code>basename(__FILE__,"php")</code>
     * @param null|string $ext - <code>.php</code> */
    static function uses($classes, $helper, $ext=".php"){
        foreach((is_array($classes)?$classes:array($classes)) as $c){
            require self::HELPERS_DIR.self::DS . $helper . self::DS . $c . $ext;
        }
    }

    static function prepare($setOrCheck = false, $classMethod){
        self::$pageExtension = cf::$rq["ext"];
        $continuePreparation = $setOrCheck === false ? true : false ;
        if($setOrCheck === true){
            if(!in_array($classMethod, self::$preparePassed) && empty(self::$pageExtension)){
                die("ERROR (in a ".__CLASS__." call): Please call the ".$classMethod."(); method before the page headers sent."); 
            }
        } else if(!empty($setOrCheck)) {
            self::$pageExtension = $setOrCheck;
        } 
        if(!in_array($classMethod, self::$preparePassed)){
            self::$preparePassed[] = $classMethod;
        }
        return $continuePreparation;
    }
    
    /** Adds the invoking class <code>__METHOD__</code> value to self::prepared array, to be able later 
     * via helper::prepared(__METHOD__), to confirm the invoking class prepare method was passed.
     * 
     * @todo: Rename self::NEW_prepare()  -> self::prepare();
     *               self::$preparePassed -> self::$prepared 
     *        and Remove self::$pageExtension its obvious to use cf::$rq["ext"]
     * 
     * @param string $__METHOD__ <code>__METHOD__</code> value (...::prepare of the invoking class)*/
    static function NEW_prepare($__METHOD__){
        if(!in_array($__METHOD__, self::$prepared)){
            self::$prepared[] = $__METHOD__;
        }
    }
    
    /** Confirms the prepare function is called for the current class method 
     * @param string $__METHOD__ Invoking <code>__METHOD__</code> value
     * @return (bool)true | die(...) */
    static function prepared($__METHOD__){
        if(!in_array($__METHOD__, self::$prepared) && empty(cf::$rq["ext"])){
            die("ERROR (in a ".__CLASS__." call): Please call the ".$__METHOD__."(); before the page headers sent."); 
        }
    }

}

helper::config();
