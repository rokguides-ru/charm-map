<?php
namespace Charm\Map;

interface MapInterface extends \Countable, \JsonSerializable {

    public function &get($key);
    public function set($key, $value);
    public function has($key): bool;
    public function delete($key);

}

