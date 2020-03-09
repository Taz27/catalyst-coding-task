# PHP Coding Challenge (Catalyst IT Australia)
## Coded By: TARAN MAND

I have coded this PHP script as per the specified task requirements. There are **13 commits** in this repository which shows the development process from scratch.

I have used *PHP version 7.2.10 and PostgreSQL version 12*. The *default* credentials to connect to database are:

    - username: postgres
    - password: admin
    - dbname: postgres
    - host: 127.0.0.1
    - port: 5432

However you can pass *username, password and host* at runtime by using command line options -u -p and -h.

To run this script, you need to install ***Commando - An Elegant PHP CLI Library***. You need to download and build [Composer](https://getcomposer.org/) first and make it [globally accessible](https://getcomposer.org/doc/00-intro.md#globally). Then *cd* to the script directory (cloned repository) and run the command *composer install*. By doing this, Composer will look for *composer.json* file (which I have already included in this repository) and install the required *Commando Library*.

To execute the script, run the command *php user_upload.php --help*. This will output the list of *command line options/directives* with details which you can use. 

