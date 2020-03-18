<?php

include __DIR__.'/src/coder.php';
$service = new GoogleAuthenticator();
echo $service->createSecret().PHP_EOL;