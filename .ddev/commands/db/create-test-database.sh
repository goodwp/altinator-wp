mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS db_test;"
mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON db_test.* TO 'db'@'%' IDENTIFIED BY 'db';"
mysql -uroot -proot -e "FLUSH PRIVILEGES;"
