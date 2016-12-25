<?php

require __DIR__."/../vendor/autoload.php";
require __DIR__."/mock/ContainerMock.php";

# create mocks for the following namspaced functions
# this way we don't have to worry about them being called first
# see https://github.com/php-mock/php-mock#requirements-and-restrictions
$mockFunctions = [
    ["sndsgd", "openssl_encrypt"],
    ["sndsgd", "openssl_decrypt"],
    ["sndsgd", "openssl_digest"],
];

foreach ($mockFunctions as list($namespace, $name)) {
    (new \phpmock\MockBuilder())
        ->setNamespace($namespace)
        ->setName($name)
        ->setFunction(function(){})
        ->build()
        ->define();
}
