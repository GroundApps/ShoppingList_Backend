#How to contribute

* Active development only takes place in the ```devel``` branch, so please only fork the ```devel``` branch.
* Check if the bugs you found are still existing in the current ```devel``` version.
* If so, make your changes.
* Open a Pull Request and also explain your changes and why you think they are necessary.
* Done.
* Enjoy that we are eternally grateful for your help :)

###Error Codes
* If whatever you changed or implemented is something that is directly accessed by the Android app,
use the following way of displaying errors:

```php die (json_encode(array('type' => DEFINED_IN_CONSTANTS.php, 'content' => 'Put your message here.'))); ```
Add the constant you used for as type to CONSTANTS.php.

* All codes 1000-1999 will be displayed as a success, if implemented in the app
* All codes > 5000 will be displayed as an error in the app
  * The message provided by the backend will be displayed
