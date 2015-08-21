# ShoppingList Backend

### Install
Create in your MySQL Database with the SQL_DUMP_Shoppinglist.sql file a table for the ShoppingList.
Then edit the file api.php

Change (set a Key like a Password)

```php
$authKey = "";
```
then change:

```php
$db = NEW sql('host','db','table','user','password');
```
replaced it wit your Information.

Copy the files to your Server, go to the App in Settings add the Path and the Authkey.
Done.
