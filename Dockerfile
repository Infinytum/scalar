FROM docker.infinytum.dev/library/web-deploy

LABEL Maintainer="Scalar"

# Install PHP deps
RUN apk update
RUN apk add php7 php7-fpm php7-mysqli php7-json php7-openssl php7-curl \
    php7-zlib php7-xml php7-phar php7-intl php7-dom php7-xmlreader php7-ctype \
    php7-mbstring php7-gd nginx supervisor curl openssl php7-session ssmtp php7-pdo php7-pdo_mysql git php7-pdo_sqlite


# Install Framework
RUN mkdir -p /srv/scalar
ADD ./ /srv/scalar/
RUN chmod -R 777 /srv

# Configure NGINX
ADD nginx.conf /etc/nginx/http.d/default.conf