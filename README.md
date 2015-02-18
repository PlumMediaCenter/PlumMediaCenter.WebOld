PlumMediaCenter Server
===============

PHP Based mp4 media (video only right now) library website. Also included is a prototype Roku app that consumes the videos from this video library website.

More detailed instructions to come as I have time. 

You must have a web server that can run PHP. You must also have root access to a mysql database. 

Copy the contents of this folder to a folder inside of your web server and name that folder 'PlumMediaCenter'

Navigate to Setup.php and follow the instructions. 

Add the following to the httpd.conf file in order to add an alias to access files not in the root web directory.


Alias /video /Videos
<Directory "/Videos">
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
</Directory>


#add the following to php.ini to get xdebug running with netbeans
[XDebug]
zend_extension = "\xampp\php\ext\php_xdebug.dll"
xdebug.remote_enable=on
;xdebug.remote_log="/var/log/xdebug.log"
xdebug.remote_host=localhost
xdebug.remote_handler=dbgp
xdebug.remote_port=9000

Install the mod-h264 module to allow pseudostreaming of mp4 files.
http://h264.code-shop.com/trac/wiki/Mod-H264-Streaming-Apache-Version2
