<?php
/** The helper (base) class */
class helper {

    public static $pageExtension, $config, $preparePassed = array();
    private static $prepared = array();
    const HELPERS_DIR = __DIR__;
    const CONFIG_DIR = "config";
    const DS = DIRECTORY_SEPARATOR;

    /**Genereates a list of helpers to be registered on the spl_autoload_register
     * @see ../boot/autoload.php
     * @return array  - active helpers lits to be registered */
    static function AutoLoadRegisteredList(){
        self::config();
        $alr = array();
        $alr[($h="cf")]           =   array("fn" => $h.DS.$h);
        /**
        $alr["ckeditor"]             =   array("fn" => "ckeditor/ckeditor");
        $alr["cssmodal"]             =   array("fn" => "cssmodal/cssmodal");
        $alr["db"]                   =   array("fn" => array("db/dbCore","db/db"));
        $alr["fallback"]             =   array("fn" => "fallback/fallback");
        $alr["form"]                 =   array("fn" => array("form/formCore","form/form"));
        $alr["img"]                  =   array("fn" => "img/img"); //Amended version of the previous
        $alr["log"]                  =   array("fn" => "log/log");
        $alr["lang"]                 =   array("fn" => "lang/lang");
        $alr["msg"]                  =   array("fn" => "msg/msg");
        $alr["signup"]               =   array("fn" => "signup/signup");
        $alr["table"]                =   array("fn" => array("table/tableCore","table/table"));
        $alr["val"]                  =   array("fn" => array("val/valCore","val/val"));
        $alr["token"]                =   array("fn" => "token/token");
        */
        return $alr;
    }

    private static function config(){
        $path = self::HELPERS_DIR .self::DS. self::CONFIG_DIR .DS;
        foreach(($files = array("config.json", "config.sample.json")) as $fn){
            if(is_readable(($file=$path.$fn))){
                $config = json_decode(file_get_contents($file), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    if (function_exists('json_last_error_msg')) {
                        $error_message = "error message:".json_last_error_msg().", ";
                    }
                    cf::end("Syntax error in ".$fn.". (".$error_message."file: ".$file);
                }
            }
        }
        self::$config = isset($config) ? $config : cf::end("Missing any config file ".implode(", ", $files).". (at ".$path);
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
    
    /** Adds the invoker's <code>__METHOD__</code> value to self::prepared array
     * to be able on helper::prepared(__METHOD__) to confirm the invoker's 
     * prepare method was passed 
     * 
     * @todo: Rename self::NEW_prepare()  -> self::prepare();
     *               self::$preparePassed -> self::$prepared 
     *        and Remove self::$pageExtension its obevious to use cf::$rq["ext"]
     * 
     * @param string $__METHOD__ Invoker's <code>__METHOD__</code> value */
    static function NEW_prepare($__METHOD__){
        if(!in_array($__METHOD__, self::$prepared)){
            self::$prepared[] = $__METHOD__;
        }
    }
    
    /** Confirms the prepare function is called for the current class method 
     * @param string $__METHOD__ Invoker's <code>__METHOD__</code> value
     * @return (bool)true | die(...) */
    static function prepared($__METHOD__){
        if(!in_array($__METHOD__, self::$prepared) && empty(cf::$rq["ext"])){
            die("ERROR (in a ".__CLASS__." call): Please call the ".$__METHOD__."(); before the page headers sent."); 
        }
    }

}

if(!defined("DS")){ define("DS",DIRECTORY_SEPARATOR);}

