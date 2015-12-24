# Ts3-Multibot-Engine #

Engine for ts3 multibot

### Advantages ###

* Performance
* Scalability
* Ability to run multiple processes
* Operation commands
* Permission system
* Easy setup
* Base on ts3admin.class
* PHP7 compatible
* Fully compatible with linux

### Important Information ###

* To run Windows you need to run each process separately
* If you want to create some function or command should fully rely on ts3admin.class

## Configuration ##

#### Linux ####
To start the commands engine you must use "php core/core.php --lang eng --startmde commands".
If you want to shut down multibot on linux you must use "killall php" or kill all screens with "ExusMultibot".
Alternatively, you can use the script. "./multibot.sh start" and "./multibot.sh stop"

#### Windows ####
To start the commands engine you must use "php core/core.php --lang eng --startmde commands".
If you use Windows to start the process of Multiboot you have to use "php core/core.php --lang eng --startmde multibot".

### Creating a new function ###
All you have to do to create a new function to write it on the basis "functionExample.class.php" and to create a configuration file based on "ExampleConfigFile.conf" with the same name as This property has a function. If wyszstko perform properly and put configuration in the "config/functions" and the file functions in the "functions" should work properly.

### Creating a new command ###
To create a new command you need to write it based on the files "commandExample" and put it in the folder "commands". If you've done everything correctly, everything should work properly.
