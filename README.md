Charm/Map
============

A data structure similar to arrays, but you can use `null`, resources, booleans and objects
for keys.


Quick Start
-----------

```
$first = ["some array"];
$second = (object) ["prop" => "another array"];
$third = null;

$map = new Charm\Map();

// Any key
$map['hello'] = 'World';
$map[$first] = 'hello';
$map[$second] = $first;
$map[$third] = $map;

// Implicit value creation
$map['undefined key'][] = 1;    // 'undefined key' does not exist, but appending works
$map['undefined key'][] = 2;    // 'undefined key' is no longer undefined

// Iteratble
foreach ($map as $key => $value) {
}

// Countable
echo "Map has {count($map)} elements\n";
```

A map is different from PHP's built-in array in that you can use any value as the key. Normal
arrays don't allow you to use float numbers, resource pointers or objects as a key.


Template Type
-------------

To create a `Map<int, User>` or something similar, you can use the `Map::T(array $types)` function.

```
$map = Map::T(['int', User::class]);

$map[123] = "Hello"; // fails because we're expecting an object of type 'User'
```

Features
--------

What more do you need?


Caveats
-------

For scalar (ints, strings, booleans and arrays) values in PHP, it is the *literal value* 
which is used as a key. If you use an array as key, and then you modify the array - the
value may not be found.

```
$secretKey1 = ['foo'];
$secretKey2 = ['foo','bar'];

$map[$secretKey1] = "Important data";
$map[$secretKey2] = "Sensitive matters"

$secretKey1[] = 'bar';  // oops! You've modified $secretKey1 and you can't find "Important data" any more.

echo $map[$secretKey1]; // 'Sensitive matters' turns out it became identical to $secretKey2
```

You should avoid using arrays for keys; they are the slowest key type due to hashing.
