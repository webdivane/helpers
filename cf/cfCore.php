<?php
/** The common helper core class */
class cfCore {

    /** @var type array, after cf::RequestArray() execution it will look like:  array(
     *    "complete"=>string
     *    "url-full"=>string -> above without the query string
     *    "filename"=>(null|string) -> "contact.html" file name,
     *    "ext"=>string(null|string) -> "html"
     *    "url"=>(null|string) -> "contact"
     *    "dir-list"=>(null|array())     ) */
    public static $rq = null;
    public static $pg, $lang, $usr, $pageHeadTagContent=array();
    protected static $preparedMethods = array();


    /** Reads the servers reuqest vars $_SERVER[HTTP_HOST, SERVER_PORT, REQUEST_URI] setting self::$rq propery
     * @see cf::$rq
     * @return array */
    final static public function RequestArray() {
        //@see {@link [http://php.net/manual/es/function.filter-input.php#77307] [FastCGI filter_input(INPUT_SERVER) problem]}
        if(!is_null($host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL))){
            $protocol = filter_input(INPUT_SERVER,'SERVER_PORT', FILTER_SANITIZE_NUMBER_INT) === 443 ? "https://" : "http://";
            $complete = $protocol . $host . ($requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL));
        } else {
            $protocol = (bool)($_SERVER['HTTPS'] === "on") ? "https://" : "http://";
            $requestUri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);            
            $complete = ($protocol . ($host=filter_var($_SERVER["HTTP_HOST"],FILTER_SANITIZE_URL)).$requestUri );
        }
        $request=array(); //set ordering
        $request['complete'] = str_replace("\\","/",$complete);
        $request['url-full'] = strtok($request['complete'],"?");
        $url = explode("/", substr($request['url-full'], strlen($protocol.$host."/")));
        $request["filename"]=array_pop($url);
        $request["ext"] = count($v = explode(".",$request["filename"]))>1 ? array_pop($v) : null;
        $request["url"] = !empty($v) ? implode(".",$v) : $request["filename"];
        $request["dir-list"]= !empty($url) ? $url : null;
//cf::vdd($request);
        return self::$rq = $request;
    }


    /** Appends content into the $pageHeadTagContent
     *  can be called as: 
     *      cf::appendToHeadTag(
     *                          array(
     *                                 "//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.css"=>"css",
     *                                 "//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.js"=>"script"
     *                                )
     *      );
     *  OR
     *      cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js","script");
     *      cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js");
     *  default tag_type = "script"; */
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
                case "css":             $headTagAddons[] = form::tagOut("","link",array("rel"=>"stylesheet","href"=>$v[1], $args));     break;
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


    /** final function cf::vd() - var_dump shortage - writes vars formated dump for submitted arguments.*/
    final static public function vd(){
        $args = func_get_args();
        echo "<pre>";
        foreach($args as $value){
            var_dump($value);
        }
        $callers=debug_backtrace();
        $from="Dump triggered from: "  .  $callers[1]["class"] . "::" . $callers[1]["function"] . " | " . $callers[0]["file"] . " (line: " . $callers[0]["line"] . ")";
        echo "<p style=\"font-family:Tahome,sans-serif; padding-top:5px; font-size:12px; color:#888; border-top:1px dashed #888; margin:0px;\">".$from."</p></pre>";
    }

    /** final function cf::vdd() - var_dump shortage with die(); - writes vars formated dump for arguments. */
    final static public function vdd(){
        $args = func_get_args();
        if(!headers_sent()){ echo "<meta charset=\"UTF-8\">\n";}
        echo "<pre contenteditable>";
        foreach($args as $value){
            var_dump($value);
        }
        $callers=debug_backtrace();
        $from="Dump triggered from: "  .  $callers[1]["class"] . "::" . $callers[1]["function"] . " | " . $callers[0]["file"] . " (line: " . $callers[0]["line"] . ")";
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

}
