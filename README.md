#Helpers#

A short list of ***[php](http://php.net/ "PHP.net")*** helper classes
## 


* [GitHub URL](https://github.com/webdivane/php-helpers)
* Version 1.1 (beta)
* Public since: 2016-06-25
 
**Note:** The *library is under constant development*. Many of its parts ***will*** be updated in the future.

##

#### Contents ####
The root includes a base [helper.php](helper.php) class and a list of separte helpers folders ([forms/](/forms/)).
```
helpers/
├── cf/							- Common functions
├── ...							
├── form/						- Form (input & html) output
├── ...							
├── msg/						- Message (session & inline)
├── log/						- Log write  
├── ...							
└── helper.php

```

**Separate helpers may (or may not) extend the base [helper.php](helper.php) class.** (it cary for separete helpers autoload and use preparations).

More details about the base [helper.php](helper.php) class can be fonud below.  
 
##The base [helper.php](helper.php) class##
Contain following methods:

* **Auto load registered list** `helper::AutoLoadRegisteredList();`

	Returns the regitered helpers as array, ready for in `spl_autoload_register()` use:

		

		spl_autoload_register(function ($class) {
		    
		    global $helperAlrMap;
		    
		    if(isset($helperAlrMap[$class])){
		        RegisterClass($helperAlrMap[$class]["fn"]);
		    } else if (...) {   									
				//any other classes load logic
				...
		    }
		});
		
		function RegisterClass($classFlineNames, $path = APP_HELPERS, $fileExtension = ".php"){
		    foreach (((is_array($classFlineNames)) ? $classFlineNames : array($classFlineNames)) AS $class ){
		        if(is_readable($filename = $path . $class . (!empty($fileExtension) ? $fileExtension : ""))){
		            require_once ($filename); 
		        } else {cf::end("<strong>Class call error:</strong> the calss \"{$class}\" cannot be found. ", 4);}
		    }
		}
		
		RegisterClass("helper");
		$helperAlrMap=(array)helper::AutoLoadRegisteredList();
	|


* **Prepare** `helper::prepare();`
	
	This is required mainly for helpers which rely of additional files load (before self helper usage). 
	
	In case the helper require a files to be loaded before its call, the base class ensures the `hlpr::prepare();` method is trigerred on the page load. If not, it stops the further execution with an error message. 
		
	**Example:** The [msg/](msg/) helper may requrire dedicated CSS files to can work, so is mandatory the `msg::prepare();` to be called before to try to show/set/get any message. In case the `msg::put("I'm happy today!", "success");` is called without a prior `msg::prepare();` call, the script execution will be stopped with the message: 
	
	***ERROR (in a helper call):** Please call the msg::prepare(); method before the page headers sent.*
	
	Discover of the separate helpers further will show you *why* the preparation has to be executed before the page headers sent.



---
[
![php]("repo-files/php-logo.svg" "Powered by")
](http://php.net/)

