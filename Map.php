<?php
namespace Charm;

require('src/TemplateTypeTrait.php');

class Map implements \Countable, \IteratorAggregate, \ArrayAccess {
    use Map\TemplateTypeTrait;

    private $nextId = 0;            // All values are stored sequentially in $this->values and keys in $this->keys. This counter determines the offset for new items.
    private $index = [];            // Object hash maps to an array of integer indexes for keys and values. This way hash collisions should not be a problem.
    private $keys = [];             // Keys of every item
    private $values = [];           // Values of every item
    private $count = 0;             // Count of all the items in the map

    private $hasLastItem = false;   // If we've returned a null value, we'll keep a reference to the returned value. This way we can support implicit key creation, e.g. `$map['key'][] = 'implicit array created').
    private $lastItemKey = null;    // The key of the last item we implicitly created
    private $lastItemHash = null;   // The hash of the last item we implicitly created
    private $lastItemValue = null;  // The reference to the last item we implicitly created

    public function getIterator() {
        $this->processLast();
        foreach ($this->values as $index => &$value) {
            yield $this->keys[$index] => $value;
        }
    }

    protected static function createT(array $validators, ...$constructorArgs): static {
        return new class($validators[1] ?? function(){}, $validators[0] ?? function(){}) extends Map {
            private
                $keyValidator,
                $valueValidator;

            public function __construct(callable $keyValidator, callable $valueValidator) {
                $this->keyValidator = $keyValidator;
                $this->valueValidator = $valueValidator;
            }

            public function set($key, $value) {
                ($this->keyValidator)($key, 'key');
                ($this->valueValidator)($value, 'value');
                parent::set($key, $value);
            }
        };
    }

    public function clear(): void {
        $this->nextId = 0;
        $this->index = [];
        $this->keys = [];
        $this->values = [];
        $this->count = 0;
        $this->hasLastItem = false;
        $this->lastItemKey = null;
        $this->lastItemHash = null;
        $n = null; $this->lastItemValue = &$n;
    }

    public function keys(): iterable {
        $this->processLast();
        yield from $this->keys;
    }

    public function values(): iterable {
        $this->processLast();
        foreach ($this->values as &$value) {
            yield $value;
        }
    }

    public function entries(): iterable {
        return $this->getIterator();
    }

    public function forEach(callable $fn) {
        foreach ($this as $key => &$value) {
            $fn($value, $key, $this);
        }
    }

    public function count() {
        $this->processLast();
        return $this->count;
    }

    public function set($key, $value) {
        $this->processLast();
        $hash = $this->getHash($key);
        if (isset($this->index[$hash]) || array_key_exists($hash, $this->index)) {
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
        $this->processLast();
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
        $this->processLast();
        $hash = $this->getHash($key);
        if (!isset($this->index[$hash])) {
            return $this->makeLast($key, $hash);
        }
        foreach ($this->index[$hash] as $index) {
            if ($this->keys[$index] === $key) {
                return $this->values[$index];
            }
        }
    }

    public function delete($key) {
        $this->processLast();
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

    /**
     * {@see ArrayAccess::offsetGet}
     */
    public function &offsetGet($key) {
        return $this->get($key);
    }

    /**
     * {@see ArrayAccess::offsetSet}
     */
    public function offsetSet($key, $value) {
        $this->set($key, $value);
    }

    /**
     * {@see ArrayAccess::offsetExists}
     */
    public function offsetExists($key) {
        return $this->has($key);
    }

    /**
     * {@see ArrayAccess::offsetUnset}
     */
    public function offsetUnset($key) {
        $this->delete($key);
    }

    /**
     * {@see JsonSerializable::jsonSerialize}
     */
    public function jsonSerialize() {
        $this->processLast();
        $res = [];
        foreach ($this->values as $index => $value) {
            $res[] = (object) [ 'key' => $this->keys[$index], 'value' => $value ];
        }
        return $res;
    }

    public function __set($key, $value) {
        throw new \OutOfBoundsException("Property '$key' is not defined", 1);
    }

    public function __get($key) {
        throw new \OutOfBoundsException("Property '$key' is not defined", 2);
    }

    public function __unset($key) {
        throw new \OutOfBoundsException("Property '$key' is not defined", 3);
    }

    public function __exists($key) {
        return false;
    }

    private function processLast() {
        if ($this->hasLastItem && $this->lastItemValue !== null) {
            if (isset($this->index[$this->lastItemHash])) {
                foreach ($this->index[$hash] as $index) {
                    if ($this->keys[$index] === $key) {
                        if ($this->values[$index] !== $this->lastItemValue) {
                            if ($this->values[$index] !== null) {
                                throw new \Exception("Internal value for '{$this->renderKey($this->lastItemKey)}' out of sync. This could happen because values are returned by reference from Charm\\Map.");
                            }
                            $this->values[$index] = $value;
                        }
                        goto inserted;
                    }
                }
            }
            $this->index[$this->lastItemHash][] = $this->nextId;
            $this->keys[$this->nextId] = $this->lastItemKey;
            $this->values[$this->nextId++] = $this->lastItemValue;
            $this->count++;
        inserted:
            $this->hasLastItem = false;
            $this->lastItemHash = null;
            $this->lastItemKey = null;
            $n = null; $this->lastItemValue = &$n;
            $this->lastItemValue = null;
        }
    }

    private function &makeLast($key, string $hash) {
        assert($this->hasLastItem === false, "Don't call makeLast() unless you called processLast() first");
        $this->hasLastItem = true;
        $this->lastItemKey = $key;
        $this->lastItemHash = $hash;
        $this->lastItemValue = null;
        return $this->lastItemValue;
    }

    private function getHash($key) {
        if (is_int($key)) {
            return 'i'.$key;
        } elseif (is_float($key)) {
            return 'f'.$key;
        } elseif (is_bool($key)) {
            return 'b'.($key ? '1' : '0');
        } elseif (is_string($key)) {
            if (strlen($key) > 31) {
                $key = md5($key, true);
            }
            return 's'.$key;
        } elseif (is_object($key)) {
            return 'o'.\spl_object_id($key);
        } elseif (is_resource($key)) {
            return 'r'.((int) $key);
        } elseif (is_array($key)) {
            return 'a'.md5(serialize, true);
        } elseif ($key === null) {
            return 'n';
        } else {
            throw new LogicException("Unsupported key type ".get_type($key));
        }
    }

    private function renderKey($key) {
        return '['.get_class($key).'#'.spl_object_id($key).']';
    }

}
