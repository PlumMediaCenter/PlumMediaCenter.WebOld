#you must first log in as root

#delete any previous references to the user or the database
delete from mysql.user where user = 'plumvideoplayer';
drop user 'plumvideoplayer'@'localhost';
drop database plumvideoplayer;

#create the database
create database plumvideoplayer;

#create the user
flush privileges;
create user 'plumvideoplayer'@'localhost' identified by 'plumvideoplayer';
flush privileges;

#set the password again, which will reset the hash and allow the user to login
set password for plumvideoplayer@localhost=password('plumvideoplayer');
flush privileges;

#allow video
grant all on plumvideoplayer.* to 'plumvideoplayer'@'localhost';
flush privileges;

