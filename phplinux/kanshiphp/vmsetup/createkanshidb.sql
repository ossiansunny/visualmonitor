drop user if exists 'kanshiadmin'@'localhost';
create user 'kanshiadmin'@'localhost' identified by 'kanshipass';
grant all privileges on *.* to 'kanshiadmin'@'localhost';
drop database if exists kanshi;
create database kanshi;
