<?php
include __DIR__.'/src/coder.php';
include __DIR__.'/src/keys.php';
array_shift($argv);

try {
    $storage = new KeyStorage($argv);
} catch (KeyStorageSetup $e) {
    echo $e->getMessage().PHP_EOL; return -1;
}
$name = array_shift($argv);
try {
    $key = $storage->getByName($name);
} catch (\InvalidArgumentException $e) {
    echo "get-code <name>".PHP_EOL; return -1;
} catch (KeyNotFound $e) {
    echo $e->getMessage().PHP_EOL; return -2;
}

$service = new GoogleAuthenticator();
$code = $service->getCode($key);
echo $code.PHP_EOL;