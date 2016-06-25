# The message helper ``msg::... `` #
Serves the user messages.

### Setup ###
The helper can work with bootstrap existing message classes. To activate this just set the dedicated class constant `const USE_BOOTSTRAP = false;` to `true`.

* Regular messages:

	![Regular messages](../repo-files/msg-types-bootstrap.jpg "Regular messages examples")
 
* Bootstrap messages: 

	![Bootstrap messages](../repo-files/msg-types-bootstrap.jpg "Bootstrap messages examples")

### The helper methods###
* **Set:** `msg::set("Site message come here!", "success")`. Stores the message in the session variable. The next method shows it on the next page load. Method is created to serve save/delete actions, where the action completion will trigger page reloading.
    
* **Get:** `msg::get()`. Shows any stored in the session variable messages.

* **Put:** `msg::get("Site message come here!", "plain")`. Shows a single message, without session connection.