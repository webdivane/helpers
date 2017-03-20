# The cf  helper ``cf::... `` #
The **cf** (common functions) helper is dedicated to collect various properties and functions, which not fit in the request or mvc architecture.

### What is this class for? ###
Instead usage of the usual `<?="<a href=\"linkUrl\">Link</a>"?>` with the current class you can call `form::link("linkUrl","Link");`

### Files structure? ###
* [cf.php](/cf.php) is the main helper class, dedicated to collect the app custom logic `cf::defaultCkSettings();`
* [common.php](/common.php) is the container of the common functions helper class, dedicated to collect app-independent functions `cf::ifNull($a, "\$a is empty.");`
* [add/](add) folder contain list of stylesheets and JavaScript files needed for the specific methods calls @see cf::prepare(). 