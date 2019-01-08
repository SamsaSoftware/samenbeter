Samsa Software
=======================

Introduction
------------
This is a highly customisable ERP solution.

Installation
------------

Using Composer (recommended)
----------------------------
The recommended way to get a working copy of this project is to clone the repository
and use `composer` to install dependencies using the `create-project` command:

    curl -s https://getcomposer.org/installer | php --
    php composer.phar create-project -sdev --repository-url="https://packages.zendframework.com" zendframework/skeleton-application path/to/install

Alternately, clone the repository and manually invoke `composer` using the shipped
`composer.phar`:

    cd my/project/dir
    git clone https://<bitbucketUserName>@bitbucket.org/codymihai/mongo.git
    cd myProjectDir
    php composer.phar install


Database
--------

In order to work the project you have to install the `mongo` database.
You can check how to install it here: https://docs.mongodb.org/manual/installation/

Better view of database with : https://robomongo.org/

Web Server Setup
----------------

### PHP CLI Server

The simplest way to get started if you are using PHP 5.4 or above is to start the internal PHP cli-server in the root directory:

    php -S 0.0.0.0:8080 -t public/ public/index.php

This will start the cli-server on port 8080, and bind it to all network
interfaces.

Browser: http://localhost:8081/application/view

**Note: ** The built-in CLI server is *for development only*.

### Apache Setup

To setup apache, setup a virtual host to point to the public/ directory of the
project and you should be ready to go! It should look something like below:

    <VirtualHost *:80>
        ServerName zf2-tutorial.localhost
        DocumentRoot /path/to/zf2-tutorial/public
        SetEnv APPLICATION_ENV "development"
        <Directory /path/to/zf2-tutorial/public>
            DirectoryIndex index.php
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
    </VirtualHost>

### Fixtures

Use the main Organization fixture to create masterdata:


  php public/index.php run organizationfixture

Then run the per_prganzation fixture file

  php public/index.php run company_namedatafixture




### Configuration files

There are many configurations files:
Files containing `.global` will be on all environments.
Files containing `.local` will work only on local environment.
There will be also `.staging`, `.development`, `.production` which should be used on different environments.
 
 To setup your environment you have to add this on .htacces file : SetEnv APP_ENV `<environment>`.
 
 In order to work on your local machine, you have to create all config files with `.local` and 
 copy the content from the `.development` files and set your environment to be local, like: `SetEnv APP_ENV local`