#/bin/bash

php ./vendor/zircote/swagger-php/bin/openapi -o /swagger-local/openapi-core3.json -b autoload.php ./classes/Rest/Methods.php
