FROM ubuntu:14.04
MAINTAINER Dmitry Kuzmin rockwith@me.com
ENV DEBIAN_FRONTEND noninteractive
RUN DEBIAN_FRONTEND=noninteractive \
	apt-get update;\
	apt-get -y install php5;\
	apt-get -y install php5-cli;\
	apt-get -y install software-properties-common
RUN DEBIAN_FRONTEND=noninteractive \
	apt-get -y --force-yes upgrade php5;\
	printf "%s\n/etc/init.d/apache2 start" >> /etc/bash.bashrc;\
	service apache2 start;\
	a2enmod rewrite;\
	service apache2 restart

COPY 000-default.conf /etc/apache2/sites-available/
COPY apache2.conf /etc/apache2/
COPY logs/ /var/www/html/logs/
COPY public/ /var/www/html/public/
COPY src/ /var/www/html/src/
COPY templates/ /var/www/html/templates/
COPY composer.lock /var/www/html/
COPY composer.json /var/www/html/
WORKDIR /var/www/html/
RUN DEBIAN_FRONTEND=noninteractive \
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');";\
	php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;";\
	php composer-setup.php;\
	php -r "unlink('composer-setup.php');";\
	php composer.phar install
WORKDIR /var/www/html/logs/
RUN chmod 666 *
RUN chown -R www-data:www-data /var/www/html/public
RUN chmod -R g+rw /var/www/html/public
EXPOSE 80