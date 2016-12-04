<?php
/** The common helper class */
class cf extends cfCore {

    static function processPageUrl(&$pageId, &$pagesData, $pgUrl){
        $allPages = $pagesData;
        $pagesData = array();
        if($allPages) {
            $pagesUrls = array();
            $pagesData = array();
            foreach($allPages as $v){
                $id = $v["pgID"];
                unset($v["pgID"]);
                $pagesData[$id] = $v;
                $pagesUrls[$id] = $v["pgUrl"];
            }
            $pageId = in_array($pgUrl, $pagesUrls) ? array_search($pgUrl, $pagesUrls) : false;
            if($pageId) {
                $requestUrl = strtok(ltrim(filter_input(INPUT_SERVER, "REQUEST_URI"),"/"),"?");
                if(!empty($requestUrl) && $requestUrl != $pagesUrls[$pageId]){
                    cf::Redirect(WP.$pagesUrls[$pageId]);
                    die;
                } else if($pageId == 1 && !empty($requestUrl)){
                    cf::Redirect(WP);
                    die;
                }
            }
        }
    }


    /** Sets the image crop settings 
     * can be called in the page constuctor - when self::$rq === "details"
     * or in the validation method as the img errors are returned as inpImg validation messages */
    static function userExampleImgCropSetup(){
        img::$setup = array(); //Its better to reset the values
        img::$setup["path"] = constant('USR_EXAMPLE_IMAGES');
        img::$setup["crop"] = "inline";
        img::$setup["width"] = "400";
        img::$setup["height"] = "400";
        img::$setup["disable-delete"] = false;
        //img::$setup["path-thumb-copy"] = img::$setup["path"]."lal/";
        //img::$setup["width-thumb-copy"] = "100";
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


    static function setIfNull(&$var, $useVal){
        return $var = ( is_null($var)) ? $useVal : $var;
    }


    /** Converts sample array to a key-Value pairs
     * @param array $arr
     * @param boolean $titlecaseValues
     * @return array(key1=>Key1,...) */
    static function arrToKeyValueArr($arr, $titlecaseValues=true){
        $arrNice=array();
        foreach($arr as $v){ $arrNice[$v] = ($titlecaseValues===true ? ucwords(str_replace("-", " " , $v)) : $v);}
        return $arrNice;
    }


    static function arrayTitleCased($arr){
        foreach($arr as $k=>&$v){ $v=ucwords(str_replace("-", " " , $v)); }
        return $arr;
    }


    /** Output tag multyselect
     * @param array $list
     * @param array|string $active array to check value exists is | the list field name where to look for status
     * @param string $listFieldId - the list field name for the item id
     * @param string $listFieldName - the list field name for the text 
     * @param string $selectId The container div id */
    static function tagMultySelect($list, $active, $listFieldId, $listFieldName, $selectId = "multy-select", $allowOrder = false){
        self::prepare(__METHOD__,true);
        if(!is_array($list)){ die(__METHOD__.": Incorrect parameters request call."); }
        form::openDiv(array("id"=>$selectId,"class"=>"multy-select"));
            foreach($list as $el){
                if(!$allowOrder) {
                    $st = in_array(strtolower($el[$active]),array("yes","no")) ? strtolower($el[$active]) : die(__METHOD__.": Incorrect status value \"".$el[$active]."\" request call.");
                } else {
                    $st = abs($el[$active]) > 0 ? "yes" : "no";
                }
                form::openSpan(array("class"=>$st, "data-init"=>$st, "data-value"=>$el[$listFieldId], "onclick"=>"multySelect.setNode(this,'change')"));
                    if($allowOrder){
                        form::span("reorder",array("class"=>"material-icons right reorder"));    
                    }
                    //form::span("",array("class"=>"fa fa-square-o fa-lg"));
                    form::span("radio_button_unchecked",array("class"=>"material-icons unchecked"));
                    form::span("check_circle",array("class"=>"material-icons checked"));
                    //form::span("",array("class"=>"fa fa-check-square-o fa-lg"));
                    form::html($el[$listFieldName]);
                form::closeSpan();
            }
        form::closeDiv();
        if($allowOrder){
            form::tag("","script",array("src"=>ADD."helpers/cf/add/sortable.min.js"));
            form::tag('
                Sortable.create(top.document.getElementById("'.$selectId.'"), {
                    handle: ".reorder",
                    filter: ".no"
                });
                ',"script");
        }
    }


    public static function prepare($methodName, $setOrCheck = false) {
        $method = strpos($methodName, "::") ? str_replace(array("cf::","cfCore::"),"",$methodName) : $methodName;
        if($setOrCheck && !in_array($method, self::$preparedMethods)) {
            die("ERROR (in a cf::prepare() call): Please call the ".__METHOD__."(\"$method\"); method before the page headers sent."); 
        } else  if( helper::prepare($setOrCheck, (__METHOD__)) !==true) {
            return;
        }
        switch($method){
            case "tagMultySelect":      cf::appendToHeadTag(ADD."helpers/cf/add/tagmultyselect.css", "css");
                                        cf::appendToHeadTag(ADD."helpers/cf/add/tagmultyselect.js");
                break;
            case "animate":             cf::appendToHeadTag(ADD."helpers/cf/add/animate-timings.css", "css");
                                        cf::appendToHeadTag(ADD."helpers/cf/add/animate.min.css", "css");
                                        cf::appendToHeadTag(ADD."helpers/cf/add/animate.js","script",array("id"=>"animate.js"));
                break;            
        }
        self::$preparedMethods[]=$method;
    }
}
