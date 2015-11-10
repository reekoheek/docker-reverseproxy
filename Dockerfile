FROM node:slim

RUN apt-key adv --keyserver hkp://pgp.mit.edu:80 --recv-keys 573BFD6B3D8FBC641079A6ABABF5BD827BD9BF62

RUN \
  echo "\n\
Acquire::HTTP::Proxy \"http://192.168.1.10:3128\";\n\
Acquire::HTTPS::Proxy \"http://192.168.1.10:3128\";\n\
" > /etc/apt/apt.conf.d/01proxy && \
  echo " \n\
deb http://kambing.ui.ac.id/debian/ jessie main\n\
deb http://kambing.ui.ac.id/debian/ jessie-updates main\n\
deb http://kambing.ui.ac.id/debian-security/ jessie/updates main\n\
" > /etc/apt/sources.list && \
  # apt-get -o Acquire::Check-Valid-Until=false update -y
  apt-get update -y

RUN apt-get install -y \
  nginx \
  ca-certificates \
  php5-cli \
  supervisor

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY ./entrypoint.sh /entrypoint.sh
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./api /api

RUN \
  ln -sf /dev/stdout /var/log/nginx/access.log && \
  ln -sf /dev/stderr /var/log/nginx/error.log && \
  mkdir -p /etc/nginx/upstream.d && \
  usermod -u 1000 www-data && \
  groupmod -g 1000 www-data

RUN apt-get install -y php5-xdebug

ENV USE_API true

ENTRYPOINT ["/entrypoint.sh"]
CMD ["nginx"]