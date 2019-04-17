FROM composer:1.8 as builder
WORKDIR /reddit_php_graphql
COPY . /reddit_php_graphql
RUN composer install

FROM php:7.2-apache
WORKDIR /reddit_php_graphql
COPY --from=builder /reddit_php_graphql /var/www/html/
