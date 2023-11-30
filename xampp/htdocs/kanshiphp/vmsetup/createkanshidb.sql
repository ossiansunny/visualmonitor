drop user if exists 'kanshiuser'@'localhost';
create user 'kanshiuser'@'localhost' identified by 'kanshipass';
grant all privileges on *.* to 'kanshiuser'@'localhost';
drop database if exists kanshi;
create database kanshi;
