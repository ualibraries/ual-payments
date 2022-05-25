FROM php:alpine3.14 AS composer_build

COPY . /app

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer  /usr/bin/composer

RUN composer install


FROM node:16-alpine3.14 AS node_build

COPY . /app

WORKDIR /app

RUN npm install && npm run build


FROM php:alpine3.14 AS prod

COPY . /app

WORKDIR /app

COPY --from=node_build /app/public/build /app/public/build
COPY --from=composer_build /app/vendor /app/vendor
