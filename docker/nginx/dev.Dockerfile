FROM nginx:1.28-alpine

COPY ./docker/nginx/dev.nginx.conf /etc/nginx/conf.d/default.conf
