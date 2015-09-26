#!/bin/bash

echo "Press Enter to continue with update for ShoppingList database"
read
HOST=`cat /var/www/sholi_test/config.php | grep host | cut -d\" -f2`
USER=`cat /var/www/sholi_test/config.php | grep user | cut -d\" -f2`
PASS=`cat /var/www/sholi_test/config.php | grep password | cut -d\" -f2`
DB=`cat /var/www/sholi_test/config.php | grep db | cut -d\" -f2`
echo "Updating Database.  Please wait..."
echo
SQLSTATUS=`mysql -u $USER -p$PASS < update.sql`

if [ $? -eq 0 ]
then
  echo "Successfully updated database!"
else
  echo "Could not update database. See error above."
fi
