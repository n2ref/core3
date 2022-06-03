FROM composer:2.3 as setup

RUN mkdir /swagger-php
WORKDIR /swagger-php

RUN composer require zircote/swagger-php

COPY . .

RUN mkdir /swagger-local

CMD ["bash", "/swagger-local/docker-commands.sh"]