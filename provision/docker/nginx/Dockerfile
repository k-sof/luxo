ARG NGINX_VERSION=1.17

FROM nginx:${NGINX_VERSION}-alpine AS luxo_nginx

COPY provision/docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/app/public

COPY public ./
