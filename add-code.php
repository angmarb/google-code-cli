<?php
include __DIR__.'/src/coder.php';
include __DIR__.'/src/keys.php';
array_shift($argv);

try {
    $storage = new KeyStorage($argv);
} catch (InvalidKeyFile $e) {
    echo $e->getMessage().PHP_EOL;
    echo "Trying to create file".PHP_EOL;
    try {
        $storage = KeyStorage::createFile($e->getPath());
    } catch (WriteFailed $e2) {
        echo $e2->getMessage().PHP_EOL;
        echo "Failed to create file, exiting".PHP_EOL; return -1;
    }
} catch (KeyStorageSetup $e) {
    echo $e->getMessage().PHP_EOL; return -1;
}
$name = array_shift($argv);
$key = array_shift($argv);
try {
    $item = new StorageItem($key,$name);
} catch (\Throwable $e) {
    echo $e->getMessage().PHP_EOL; return -1;
}
try {
    $service = new GoogleAuthenticator();
    $code = $service->getCode($item->getKey());
    echo $code.PHP_EOL;
    $storage->add($item);
} catch (KeyStorageSetup $e) {
    echo $e->getMessage().PHP_EOL; return -1;
}

