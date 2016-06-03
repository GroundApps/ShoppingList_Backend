#!/bin/bash
echo -n "Press Enter to update the ShoppingList database"
read
BASE=`dirname $BASH_SOURCE`
HOST=`cat $BASE/config.php | grep host | cut -d\" -f2`
USER=`cat $BASE/config.php | grep user | cut -d\" -f2`
PASS=`cat $BASE/config.php | grep password | cut -d\" -f2`
DB=`cat $BASE/config.php | grep db | cut -d\" -f2`
echo "Starting the update. Please wait..."
SQLSTATUS=`mysql -h $HOST -u $USER -p$PASS $DB < $BASE/update.sql 2>&1`

if [ $? -eq 0 ]
then
  echo "The database has been updated successfully!"
else
  echo "Error updating the database:"
  echo "$SQLSTATUS"
fi
