<?php
/** The form helper (input elements methods) 
 * @uses val, img */
class form extends formCore {

    static $inpFieldContainer = true;
    static $error;
    private static $nonInputFlds = array("textarea","label","span");

    /** Description: Process code before inpMethod  - adding style, label... */
    private static function processInpBefore($label = "", $name = "", &$value = "", &$args = array()) {
        if (self::$inpFieldContainer === true) {
            self::$codeLineBreakAfterInpSKIP = true;
            $fldArgs = array("class" => "fld");
            if(isset($args["fld-class"])){
                $fldArgs["class"] .= " ".$args["fld-class"];
                unset($args["fld-class"]);
            }
            if(isset($args["fld-style"])){
                $fldArgs["style"] = $args["fld-style"];
                unset($args["fld-style"]);
            }
            if(isset($args["fld-id"])){
                $fldArgs["id"] = $args["fld-id"];
                unset($args["fld-id"]);
            }
            self::openTag("p", $fldArgs);
            self::codeStyleSkip(true);
        }
        
        if (!empty($label)) {
            $lblArgs = array();
            if(isset($args["id"])){
                $lblArgs["for"]=$args["id"];
            }
            if(isset($args["lbl-class"])){
                $lblArgs["class"] .= " ".$args["lbl-class"];
                unset($args["lbl-class"]);
            }
            if(isset($args["lbl-style"])){
                $lblArgs["style"] = $args["lbl-style"];
                unset($args["lbl-style"]);
            }
            if (self::$inpFieldContainer != true) {
                self::$codeLineBreakAfterInpSKIP = true;
            }
            if (isset(form::$jsval["validations"][$name]) && array_key_exists("required", form::$jsval["validations"][$name])) {
                $label.= ASTERISK;
            }
            self::tag($label, "label", $lblArgs);
            if (self::$inpFieldContainer != true) {
                self::codeStyleSkip(true);
            }
        }

        if(isset(form::$jsval["validations"][$name])) {
            foreach(form::$jsval["validations"][$name] as $k => $v){
                $args[$k] = $v;
            }
        }
        
        if (!empty($name)) {
            $args["name"] = $name;
        }
        
        
        if(isset($args["type"]) && isset($args["value"]) && $args["type"]==="checkbox"){
            if(!empty($value) && $value===$args["value"]){
                $args["checked"]="checked";
            }
            $value = null;
        }
        
        
        if (!empty($value) || filter_input(INPUT_POST, $name)!==null) {
            $args["value"] = self::prepareDataValue($name, $value);
        }
        
        if (is_array(self::$error) && array_key_exists($name, self::$error)) {
            if (array_key_exists("class", $args)) {
                $args["class"] .= " error";
            } else {
                $args["class"] = "error";
            }
        }
    }

    /** Description: Process code after the inpMethod  - error and text afer... */
    private static function processInpAfter($name = "", $requiredErrorTag = false) {
        if(isset(self::$jsval["validations"][$name]) || !empty(self::$error[$name]) || $requiredErrorTag==true){
            form::tag((empty(self::$error[$name]) ? "" : self::$error[$name][0]),"span",array("class"=>"err"));
        }
        if (self::$inpFieldContainer === true) {
            self::$codeLineBreakAfterInpSKIP = false;
            self::closeTag("p");
            self::codeStyleSkip(false);
        } else {
            self::codeStyleSkip(false);
            self::processAfter();
        }
    }

    private static function prepareDataValue($name, &$value) {
        if (filter_has_var(INPUT_POST, $name)) {
            if (!is_array($_POST[$name])) {
                $value = htmlentities(filter_input(INPUT_POST, $name), ENT_QUOTES, "UTF-8");
            } else {
				$value = array();
				foreach($_POST[$name] as $k=>$v){
					$value[$k] = htmlentities($v, ENT_QUOTES, "UTF-8");
				}
            }
        }
        return $value;
    }

    /** Description: Writes <label>$label: </label><input type="text" name="$name" value="value" {$args}/>
     *  @param str   $label
     *  @param str   $name
     *  @param str   $value
     *  @param arr   $args - tag attributes: (default) "type"=>"text" */
    static function inpField($label="", $name="", $value="", $args=array()) {
        $args["type"] = isset($args["type"]) ? $args["type"] : "text";
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }
        
        self::processInpBefore($label, $name, $value, $args);
        
        $tag = !isset($args["type"]) || !in_array($args["type"],self::$nonInputFlds) ? "input" : $args["type"];
        $innerHtml = in_array($tag, self::$nonInputFlds) && isset($args["value"]) ? $args["value"] : "";
        if(in_array($tag, self::$nonInputFlds)) {
            unset($args["type"], $args["value"]);
        } 
        if($tag==="textarea"){$codeOffset = self::$ofsetIndentsCount; self::$ofsetIndentsCount=0;}
        self::tag($innerHtml, $tag, $args);
        if($tag==="textarea"){ self::$ofsetIndentsCount=$codeOffset; }
        self::processInpAfter($name, $requiredErrorTag);
    }
    
    /** Description: Writes <label>$label: </label><select name="$name" {$args}><option value="$value"/>$valueTitle</option></select>
     *  @param string   $label
     *  @param string   $name
     *  @param array    $options
     *  @param strring  $value
     *  @param array    $args - tag attributes */
    static function inpSelect($label = "", $name = "", $options = array(), $value = "", $args = array()) {
        self::processInpBefore($label, $name, $value, $args);

        if (is_array($options)) {
            if(!array_key_exists("{!--Empty--!}", $options)){
				$options=(array("{!--Empty--!}" => "Please select"))+$options;
			}
            /*
            if (array_key_exists("{!--Empty--!}", $options)) {
                array_unshift($options, "Please select");
            } else if (empty($value)) {
                $option_with_pre = array("{!--Empty--!}" => "Please select");
                foreach($options as $k=>$v){
                    $option_with_pre[$k] = $v;
                }
                $options = $option_with_pre;
            }*/
        } else {
            die("ERROR (".__METHOD__."): options provided for $name are not valid array.");
        }
        self::openTag("select", $args);
        foreach ($options as $k => $v) {
            $arr_opt = array("value" => $k);
            if ($k == $value) {
                $arr_opt["selected"] = "";
            }
            self::tag($v, "option", $arr_opt);
        }
        self::closeTag("select");
        self::processInpAfter($name);
    }

    /** Description: Writes <button type="submit" name="save-btn" value="$textSave" {$args}>$txtSave</button><button type="button" onclick="location.assign('$cancelUrl')" {$args}>$txtSave</button>
     *  @param str   $cancelUrl
     *  @param str   $args
     *  @param str   $txtSave
     *  @param arr   $txtCancel - tag attributes */
    static function inpSaveCancel($cancelUrl = "", $args = array(), $txtSave = "Save", $txtCancel = "Cancel") {
        self::processInpBefore("", "", "", $args);
        self::tag($txtSave, "button", array_merge(array("type" => "submit", "name" => "save-btn", "value" => $txtSave), $args));
        if(!is_null($cancelUrl)) {
            self::tag($txtCancel, "button", array_merge(array("type" => "button", "onclick" => "location.assign('$cancelUrl')"), $args));
        }
        self::processInpAfter("");
    }
    
    /** Description: Writes <label>$label: </label><textarea name="$name" {$args}/>$value</textarea>
     *  @param str   $label
     *  @param str   $name
     *  @param str   $value
     *  @param arr   $args - tag attributes: (default) "type"=>"text" */
    static function inpCkEditor($label = "", $name = "", $value = "", $args = array(), $ckSettings = null) {
        $args["type"] = !isset($args["type"]) || $args["type"]==="textarea" ? "textarea" : die(__METHOD__.": Invalid \$args[\"type\"] call.");
        $args["data-ckeditor"] = is_null($ckSettings) || empty($ckSettings) ? "true" : str_replace("\"","'",json_encode($ckSettings));
        ckeditor::set();
        self::inpField($label, $name, $value, $args);
    }
    
    /** Description: cretes file upload input for an image
	 * @param type $label
	 * @param type $name
	 * @param type $value
	 * @param type $args
	 * @throws Exception */
	static public function inpImg($label, $name, $value, $args = array()) {	
        img::prepare(true);
        
        img::validSetupData($name);
        
        //Checking active validations
        $isRequired = false;
        if(isset(self::$jsval["validations"][$name]) && !empty(self::$jsval["validations"][$name])){
            foreach(self::$jsval["validations"][$name] as $validation=>$d){
                if($validation === "data-callback" && $d === "requireImg"){
                    $isRequired = true; 
                }
            }
        }
        
        if($isRequired===true && isset(img::$setup["disable-delete"]) && img::$setup["disable-delete"] === false){
            val::setError($name, "Error: img::\$setup[\"disable-delete\"] is set to false while image is required on the ".__METHOD__." call");
        } else if ($isRequired === true){
            img::$setup["disable-delete"] = true;
        }
                
        $imageUnderMsg = "";
        // isset checks are just to solve the php warnings...
        if(isset(img::$setup["allowed-extensions"])) {
            $ext = array_map('strtoupper', img::$setup["allowed-extensions"]);
            $s = count(img::$setup["allowed-extensions"]) === 2 ? " &amp; " : ", ";
            $imageUnderMsg = "System allows only " . strtoupper(implode($s,$ext)). " files. ";
        }
        if(isset(img::$setup["crop"]) && img::$setup["crop"]!==false && isset(img::$setup["width"]) && isset(img::$setup["height"])){
            $imageUnderMsg .= "Recommended size: ".img::$setup["width"]." x ".img::$setup["height"]."px.";
            if(img::$setup["crop"] === "inline"){
                self::tag("","script",array("src"=>"//ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js"));
                self::tag("","script",array("src"=>"//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js"));
                self::tag("","script",array("src"=>ADD."helpers/img/cropper/cropper.js"));
                self::tag('
                    var fileref=document.createElement("link"); fileref.setAttribute("rel", "stylesheet"); fileref.setAttribute("type", "text/css");
                    fileref.setAttribute("href", "'.ADD.'helpers/img/cropper/cropper.css");
                    document.getElementsByTagName("head")[0].appendChild(fileref);
                    ',"script");
            }
        } else {
            img::$setup["crop"] = false;
        }
        
        self::processInpBefore($label, $name, $value, $args);
        
        //Writing <input type="file" .../>
        $args["type"]="file";
        $args["id"]="inut-file-".$name;
        $args["data-process-url"] = ADD . 'helpers/img/process.php';
        $args["data-settings"] = urlencode(json_encode(img::$setup));
        $args["onchange"]="prepareImageBeforeCorp(this,1,'".img::$setup["crop"]."');";
        self::tag($value, "input", $args);
        
        self::tag((empty(self::$error[$name]) ? "" : self::$error[$name][0]),"span",array("class"=>"err"));
        
        self::input("",array("type"=>"hidden","id"=>("image-name-". $name),"name"=>$name."-filename"));
        
        self::tag($imageUnderMsg,"span",array("class"=>"inp-img-after-message"));
		
        self::tag('', 'span', array('id' => 'iframe-messages-' . $name, "class"=>"inp-img-iframe-message"));		
        
     
		if(!empty($value)) {
			$original_img =  WP . img::$setup["path"] . $value;
            form::openA(array("onclick"=>"showUploadedImagePop(this)","class"=>"inp-img-preview-lnk"));
                form::tag("","img",array("src"=>$original_img,"id"=>"gallery-img-".$name, "style"=>"max-height:".img::$setup["inp-thumb-max-height"]."px"));
            form::closeA();

            if(!isset(img::$setup["disable-delete"]) || img::$setup["disable-delete"]===false){
                form::openSpan(array("class"=>"img-delete"));
                    self::$inpFieldContainer = false;
                    self::inpField("Remove file", "removeImage-".$name, "yes", array("type"=>"checkbox"));
                    self::$inpFieldContainer = true;
                form::closeSpan();
            }
            /*
			if(!empty($args['original_img'])) {
				$original_img = $args['original_img'];				
			} else if($big_wp_dir) {
				$original_img = $big_wp_dir . $value;
			} 
				
			?>
			<a class="inp-img-preview-lnk" href="javascript:void(0);" onclick="showUploadedImagePop(this)"><img id="gallery-img-<?=$name?>" alt="" src="<?=$original_img?>" style="max-height:<?=img::$setup["inp-thumb-max-height"]?>px;"/></a>
			<?php			
			if(empty($args['disable_delete'])) {
			?>
			<label for="id-removeImage-<?=$name?>" >Remove file</label>
			<input type="checkbox" id="id-removeImage-<?=$name?>" name="removeImage-<?=$name?>" value="yes" />
			<?php
			}
		
			$v_r = explode('/', $value);
			if(!empty($v_r) && is_array($v_r)) {
				$image = array_pop($v_r);
				if(strrpos($image, '.') !== false) {
					self::inpHidden("",'old_image_name_' . $name, $image);
				}
			}*/
			
		} else {
            form::openA(array("onclick"=>"showUploadedImagePop(this)","class"=>"inp-img-preview-lnk"));
                form::tag("","img",array("id"=>"gallery-img-".$name, "style"=>"display:none, max-height:".img::$setup["inp-thumb-max-height"]."px"));
            form::closeA();
			/*?><a class="inp-img-preview-lnk" href="javascript:void(0);" onclick="showUploadedImagePop(this)"><img id="gallery-img-<?=$name?>" style="display:none; max-height:<?=img::$setup["inp-thumb-max-height"]?>px;"/></a><?php*/
		}
        self::closeTag("p");
	} 
}