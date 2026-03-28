php create_sitecontent.php 

if [ "$?" == 0 ];then
	cp topics.json /var/www/html/mocks/
	cp calculator.json /var/www/html/mocks/
fi
