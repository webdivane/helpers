<?php
/** The helper (base) class */
class helper {

    public static $pageExtension, $preparePassed = array();

    /**Genereates a list of helpers to be registered on the spl_autoload_register
     * @see ../boot/autoload.php
     * @return array  - active helpers lits to be registered */
    static function AutoLoadRegisteredList(){
        $alr = array();
        $alr["cf"]                   =   array("fn" => array("cf/cfCore","cf/cf"));
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
        return $alr;
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

}

