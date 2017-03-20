<?php
/** The common class */
class common {

    /** @var array. After cf::RequestArray() execution it will look like:  
     * <code><pre>[
     * "complete"=>string,
     * "url-full"=>string, -> above without the query string
     * "filename"=>(null|string), -> "contact.html" file name,
     * "ext"=>string(null|string), -> "html"
     * "url"=>(null|string), -> "contact"
     * "dir-list"=>(null|array())     
     * ]</pre></code>*/
    public static $rq = null;
    public static $pg, $lang, $usr, $pageHeadTagContent=array();
    protected static $preparedMethods = array();
    
    const DECIMAL_POINT = "."; const CURRENCY = "$";


    /** Appends content into the $pageHeadTagContent
     * @param array|string $data
     *  can be called as: 
     * <pre>
     *  <code>cf::appendToHeadTag(["//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.css"=>"css",
     *          "//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.js"=>"script"]);</code>
     *  OR
     * <code>cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js");</code>
     * </pre>
     * @param string $tag_type ("script") [script, inline-script, meta, css]; */
    final static public function appendToHeadTag($data = array(),$tag_type="script", $args = array()){
        $content = array();
        if(is_array($data)){
            foreach($data as $tag_content => $tag_type){
                $content[] = array($tag_type,$tag_content);
            }
        } else {
            $content[] = array($tag_type,$data);
        }
        $headTagAddons = array();
        foreach($content as $v){
            switch($v[0]){
                case "inline-script":   $headTagAddons[] = form::tagOut($v[1], "script", $args);     break;
                case "meta":            $headTagAddons[] = form::tagOut("","meta",$args);  break;
                case "css":             $headTagAddons[] = form::tagOut("","link",array_merge(array("rel"=>"stylesheet","href"=>$v[1]),$args));     break;
                default :               $headTagAddons[] = form::tagOut("","script",array_merge(array("src"=>$v[1]),$args));     break;
            }
        }
        self::$pageHeadTagContent = empty(self::$pageHeadTagContent) ? $headTagAddons : array_merge(self::$pageHeadTagContent, $headTagAddons);
    }


    /** Redirects the visitor to a new url. If headers sent uses <script>location.assign($redirectUrl);</script> */
    final static public function Redirect($redirectUrl, $target=null, $headerReplace= true, $headerCode="301") {
        if ($redirectUrl) {
            if (headers_sent() || !is_null($target)) {   die("<script>".(is_null($target) ? "" : $target.".")."document.location=\"" . $redirectUrl . "\";</script>");
            } else {                die(header('Location: ' . $redirectUrl, $headerReplace, $headerCode)); }
        }
    }

    /**Format a number value for display
     * @param float $val
     * @param int $decimal
     * @param string $dPoint
     * @param string $currency
     * @param string $dTousandsSeprator
     * @return string */
    public static function number($val, $decimal=2, $dPoint=".", $currency = null, $dTousandsSeprator = ","){
        $value = number_format(floatval($val), (int)$decimal, $dPoint, " "); //number format not like &nbsp;
		return ((!is_null($currency) ? $currency : "").str_replace(" ",$dTousandsSeprator,$value));
    }
    
    /**Format a number value as a price
     * uses self::number() default formatting for d point and thousands separator
     * adding self::$currency to the formatted number
     * @param type $val
     * @return string */
    public static function price($val){
        return self::number($val, 2, ".", self::$currency);
    }

    static function numberDecimalCommaToDot($val){
        return (float)(str_replace(",", ".", $val));
    }

    public static function ifNull($val, $defaultVal){
		return is_null($val) ? $defaultVal : $val;
	}

    final static function methodsTree(){
        echo "<pre>";
        var_dump(debug_backtrace());
        die("</pre>");
    }


    /** final function cf::getOutput(); returns generated output of a class method execution */
    final static public function getOutput($class="", $method="", $vars=array()){
        if(empty($class) || empty($class)){die('$class and $method call cannot be empty.');}
        ob_start();
        call_user_func_array(array(__NAMESPACE__ ."\\".$class, $method), $vars);
        return ob_get_clean();
    }


    /** Control Output Buffering: Start or Get current buffer contents and delete current output buffer
     * @param string $getBufferContent - if skipped or passed with "Nooo" starts buferring els return buffer in the passed var */
    static function buffer(&$getBufferContent="Nooo"){
        if($getBufferContent==="Nooo"){ ob_start();
        } else {                        $getBufferContent = ob_get_clean(); }
    }


    /** final function cf::multyArrayKeyExists(); Array nested keys lookup
     *  @param type $key
     *  @param type $arr
     *  @return type */
    final static function multyArrayKeyExists($key, $arr){
        $result = array_key_exists($key, $arr);
        if ($result)
            return $result;
        foreach ($arr as $v) {
            if (is_array($v)) {
                $result = self::multyArrayKeyExists($key, $v);
            }
            if ($result) {
                return $result;
            }
        }
        return $result;
    }


    /** A timing safe equals comparison - prevents leaking length information.
    * It is important that user input is always used as the second parameter.
    * @param string $safe The internal (safe) value to be checked
    * @param string $user The user submitted (unsafe) value
    * @return boolean True if the two strings are identical.     */
    static function timingSafeCompare($safe, $user) {
       // Prevent issues if string length is 0
       $safe .= chr(0);
       $user .= chr(0);

       $safeLen = strlen($safe);
       $userLen = strlen($user);

       // Set the result to the difference between the lengths
       $result = $safeLen - $userLen;

       // Note that we ALWAYS iterate over the user-supplied length
       // This is to prevent leaking length information
       for ($i = 0; $i < $userLen; $i++) {
           // Using % here is a trick to prevent notices
           // It's safe, since if the lengths are different
           // $result is already non-0
           $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
       }

       // They are only identical strings if $result is exactly 0...
       return $result === 0;
    }


    static function visitorBrowserData($url) { 
        $user_data=Array(); 
        $user_data["IP"] = getenv('REMOTE_ADDR');
        if(isset($url)) {$user_data["SignInURL"] = $url;}
        if(filter_has_var(INPUT_POST, 'visitor-sr')) {$user_data["SR"] = str_replace(' ','x',  filter_input(INPUT_POST, 'visitor-sr'));}
        
        $browser = @get_browser(null, true);
        if(isset($browser)) {
            $browserData = array("platform"=>"OS", "parent"=>"Browser", "cssversion"=>"CssVersion", "javascript"=>"JsEnabled");
            foreach($browserData as $dF => $dT){
                if(!empty($browser[$dF])){
                    $user_data[$dT] = $browser[$dF];
                }
            }
        }
        return json_encode($user_data);
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
