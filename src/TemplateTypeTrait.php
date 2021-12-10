<?php
namespace Charm\Map;

trait TemplateTypeTrait {
    /**
     * This function should construct an instance of your class with type checking
     * enabled.
     *
     * For example:
     * ```
     *  protected function createT(array $validators, ...$constructorArgs) {
     *
     *      return new class($validators, ...$constructorArgs) extends MyClass {
     *          private $validators;
     *
     *          public function __construct($validators, ...$constructorArgs) {
     *              parent::__construct(...$constructorArgs);
     *              $this->validators = $validators;
     *          }
     *      }
     *
     *  }
     * ```
     *
     * Class::T(['array', SomeClass::class]) is analogous to `new Class<array, SomeClass>()`
     * two return an instance of your class
     */
    abstract protected static function createT(array $validators, ...$constructorArgs): static;

    protected static function throwTypeError(string $kind=null, string $classType, string $valueType) {
        if ($kind !== null) {
            throw new \TypeError("$classType expected $kind of type '$valueType'.");
        } else {
            throw new \TypeError("$classType expected type '$valueType'.");
        }
    }

    /**
     * Emulate template types by providing type checks for keys or values.
     *
     * Class<...T>
     */
    public static final function T(array $types, ...$constructorArgs) {

        $Ts = implode(", ", $types);
        $validators = [];

        foreach ($types as $type) {
            $type = $type ?? '*';

            $validator = static::createValidator($type);
            $validators[] = function($value, string $kind=null) use ($validator, $Ts, $type) {
                if (!$validator($value)) {
                    static::throwTypeError($kind, static::class."<$Ts>", $type);
                }
            };
        }

        return static::createT($validators, ...$constructorArgs);
    }

    private static function describeType($value) {
        if (is_object($value)) {
            return get_class($value);
        } else {
            return strtr(gettype($value), ['double' => 'float', 'boolean' => 'bool']);
        }
    }

    private static function createValidator(string $type): callable {
        $validators = [];
        foreach (explode("|", $type) as $subType) {
            $subType = trim($subType);
            if ($subType === '*') {
                $validators[] = function($value) {
                    // anything goes!
                    return true;
                };
            } elseif (function_exists($callback = '\\is_'.$subType)) {
                $validators[] = $callback;
            } elseif (class_exists($subType) || interface_exists($subType)) {
                $validators[] = function($value) use ($subType) {
                    return is_a($value, $subType);
                };
            } elseif (trait_exists($subType)) {
                $validators[] = function($value) use ($subType) {
                    return in_array($subType, class_uses($value, $subType));
                };
            } else {
                throw new \LogicException("Unknown type definition '$subType'");
            }
        }
        if (count($validators) === 1) {
            return $validators[0];
        }
        return function($value) use ($validators) {
            foreach ($validators as $validator) {
                if ($validator($value)) return true;
            }
            return false;
        };

    }
}
