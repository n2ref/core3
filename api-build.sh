#/bin/bash

docker build --tag swagger-php:0.1 .
docker run -ti --rm -v $(pwd):/swagger-local swagger-php:0.1
sudo chown $(whoami) openapi-core3.json