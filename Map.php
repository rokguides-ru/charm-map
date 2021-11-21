<?php
namespace Charm;

class Map implements \Countable, \IteratorAggregate, \ArrayAccess {

    private $nextId = 0;
    private $index = [];        // [ $hash => [ $index, ... ], ... ]
    private $keys = [];         // [ $index => $key ], ... ]
    private $values = [];       // [ $index => $value ], ... ]
    private $count = 0;

    public function getIterator() {
        foreach ($this->values as $index => $value) {
            yield $this->keys[$index] => $value;
        }
    }

    public function count() {
        return $this->count;
    }

    public function &offsetGet($key) {
        return $this->get($key);
    }

    public function offsetSet($key, $value) {
        $this->set($key, $value);
    }

    public function offsetExists($key) {
        return $this->has($key);
    }

    public function offsetUnset($key) {
        $this->delete($key);
    }

    public function set($key, $value) {
        $hash = $this->getHash($key);
        if (isset($this->index[$hash])) {
            foreach ($this->index[$hash] as $index) {
                if ($this->keys[$index] === $key) {
                    $this->values[$index] = $value;
                    return;
                }
            }
        }
        $this->index[$hash][] = $this->nextId;
        $this->keys[$this->nextId] = $key;
        $this->values[$this->nextId++] = $value;
        $this->count++;
    }

    public function has($key): bool {
        $hash = $this->getHash($key);
        if (!isset($this->index[$hash])) {
            return false;
        }
        foreach ($this->index[$hash] as $index) {
            if ($this->keys[$index] === $key) {
                return true;
            }
        }
        return false;
    }

    public function &get($key) {
        $hash = $this->getHash($key);
        if (!isset($this->index[$hash])) {
            return null;
        }
        foreach ($this->index[$hash] as $index) {
            if ($this->keys[$index] === $key) {
                return $this->values[$index];
            }
        }
    }

    public function delete($key) {
        $hash = $this->getHash($key);
        if (!isset($this->index[$hash])) {
            return;
        }
        foreach ($this->index[$hash] as $indexKey => $index) {
            if ($this->keys[$index] === $key) {
                unset($this->values[$index]);
                unset($this->keys[$index]);
                if (count($this->index[$hash]) === 1) {
                    unset($this->index[$hash]);
                } else {
                    unset($this->index[$hash][$indexKey]);
                }
                $this->count--;
                return;
            }
        }
    }

    public function jsonSerialize() {
        $res = [];
        foreach ($this->values as $index => $value) {
            $res[] = (object) [ 'key' => $this->keys[$index], 'value' => $value ];
        }
        return $res;
    }

    private function getHash($key) {
        if (is_int($key)) {
            return 'int:'.$key;
        } elseif (is_float($key)) {
            return 'flt:'.$key;
        } elseif (is_bool($key)) {
            return 'bol:'.($key ? '1' : '0');
        } elseif (is_string($key)) {
            return 'str:'.$key;
        } elseif (is_object($key)) {
            return 'obj:'.\spl_object_id($key);
        } elseif (is_resource($key)) {
            return 'res:'.((int) $key);
        } elseif (is_array($key)) {
            return 'arr:'.md5(serialize);
        } elseif ($key === null) {
            return 'null';
        } else {
            throw new LogicException("Unsupported key type ".get_type($key));
        }
    }

}
