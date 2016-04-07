# ShoppingList Backend

##Docker Deployment
For a fast deployment, use Docker!
For now the container is only compatible with an sqlite database.

###Build from source
You can build the container from sources:

> git clone https://github.com/GroundApps/ShoppingList_Backend/tree/devel
> docker build -t shoppinglist .

###Pull an already built container
Or, if you are feeling lazy, just pull an already built container from the
[Docker hub](https://hub.docker.com/r/lertsenem/shoppinglist):

> docker pull lertsenem/shoppinglist

###Run the container
Finally, run the container with a command like this:

> $ docker run \
>       -d \
>       --name shoppinglist \
>       -p 8000:80 \
>       -e "API_KEY=mysecretpassword" \
>       -v /tmp/sl_data:/shoppinglist/data \
>       lertsenem/shoppinglist

* use the env variable 'API_KEY' to set the app password ;
* mount the volume '/shoppinglist/data' to persist the sqlite database.

Note that in regard to this last point you can (and should) use a volume-only
container for portability reasons.

##Installation

###Requirements
* PHP >= 5.3.7
* php-gd
* Apache Websever (we recommend a TLS Connection to the Server)
* MySQL or SQLite (you can select in the Install Script)

###Database
You can either use MySQL or SQLite. SQLite is easier to set up.

####Installation
If you use Ubuntu you can use the [PPA](https://launchpad.net/~jklmnn/+archive/ubuntu/groundapps) by executing `add-apt-repository ppa:jklmnn/groundapps`.  
To install the backend manually , go to http://your.path/.
Fill up the form, click on create!
=======
To install the backend manually, go to http://your.path/  
Fill up the form, click on create!  
That's all.

https is currently not supported for self-signed certificates.  
See the [wiki](https://github.com/GroundApps/ShoppingList/wiki) for more informations about installation and roadmap.

## Feedback
Please do never hesitate to open an issue!<br>
I know there a some bugs, most likely because I had no idea how to do it otherwise and therefore had to use a workaround.
