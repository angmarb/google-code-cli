<?php
class KeyNotFound extends \Exception {

}
class KeyStorageSetup extends \Exception {

}
class InvalidKeyArguments extends KeyStorageSetup {
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct(
            "-f file OR --file=file to specify key file" .
            $message, $code, $previous);
    }
}

class InvalidKeyFile extends KeyStorageSetup {
    private $path;
    public function __construct(string $path) {
        parent::__construct("Path $path is not readable for key storage");
        $this->path = $path;
    }

    public function getPath() {
        return $this->path;
    }
}
class WriteFailed extends KeyStorageSetup {
    public function __construct(string $path) {
        parent::__construct("Path $path failed (no access or not exists)");
    }
}

class StorageItem {
    private $key;
    private $name;

    public function __construct(string $key, string $name) {
        $this->name = $name;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }
}

class KeyStorage {
    /**
     * @var string[]
     */
    protected $warnings = [];
    /** @var StorageItem[] */
    protected $byName = [];

    protected $filename;
    public function __construct(array &$args) {
        $len = count($args);
        $filename = $_SERVER['HOME'].'/.gcode/keys';
        for ($i = 0; $i < $len; ++$i) {
            if ($args[$i] === '-f') {
                $filename = $args[$i+1];
                array_splice($args,$i, 2);
                break;
            } elseif($args[$i] === '--file=') {
                $filename = substr($args[$i],7);
                array_splice($args,$i, 1);
                break;
            }
        }
        if (!$filename) {
            throw new InvalidKeyArguments();
        }
        if (!is_readable($filename)) {
            throw new InvalidKeyFile($filename);
        }
        $this->filename = $filename;
        $this->readFile($filename);
    }

    public static function createFile(string $path) {
        if (!is_readable($path)) {
            $dir = dirname($path);
            if (is_dir($dir)) {
                $result = file_put_contents($path,'');
                if ($result === false) {
                    throw new WriteFailed($path);
                }
            }
            $result = mkdir($dir,0700,true);
            if (!$result) {
                throw new WriteFailed($dir);
            }
            $result = file_put_contents($path,'');
            if ($result === false) {
                throw new WriteFailed($path);
            }
            chmod($path,0600);
        }
        $foo = ['-f',$path];
        return new self($foo);
    }

    protected function readFile(string $path) {
        $contents = file_get_contents($path);
        if (false === $contents) throw new InvalidKeyFile($path);
        foreach(explode(PHP_EOL,$contents) as $i => $row) {
            $items = explode(',',$row);
            if (count($items) !== 2) {
                $this->warnings[] = "Line[$i]: failed to parse string into 2 chunks - ignoring";
                continue;
            }
            list($name, $key) = $items;
            try {
                $item = new StorageItem($key,$name);
            } catch (\InvalidArgumentException $e) {
                $this->warnings[] = "Line[$i]: {$e->getMessage()}";
                continue;
            }
            $this->byName[$item->getName()] = $item;
        }
    }

    public function getWarnings(): array {
        return $this->warnings;
    }

    protected function compositeFileContent() {
        return implode(PHP_EOL,
            array_map(function (StorageItem $i) {
                return "{$i->getName()},{$i->getKey()}";
            },array_values($this->byName))
        );
    }

    public function add(StorageItem $item) {
        $this->byName[$item->getName()] = $item;
        $result = file_put_contents($this->filename, $this->compositeFileContent());
        if (false === $result) {
            unset($this->byName[$item->getName()]);
            throw new WriteFailed($this->filename);
        }

    }

    public function getByName($name) : string {
        if (!$name) {
            throw new KeyNotFound("Key \"\" not found");
        }
        if (isset($this->byName[$name])) {
            return $this->byName[$name]->getKey();
        }
        throw new KeyNotFound("Key \"$name\" not found");
    }
}
