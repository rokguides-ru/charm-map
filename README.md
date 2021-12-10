Charm/Map
============

A Map is one of the most important data structures that PHP doesn't have. A high
performance map is quite sophisticated and you can't really "hack it together"
every time you actually need it, and simple array scanning approaches are very slow
in comparison.

 * Keys of any type - objects, arrays, booleans and even `null`.
 * Values of any type.
 * "Template type emulation": `Map<string, int>` can be created using
   `$map = Map::T([ 'string', 'int' ])`.
 * Countable: `count($map)`
 * Iterable: `foreach ($map as $key => $value) {}`
 * Implicit item creation: `$map['newKey'][] = "Value";` does not trigger a notice,
   even if 'newKey' does not exist.

Which problem it solves is up to you. It allows you to associate any PHP value with
any other PHP value and is very useful in building graphs and caching and detecting
loop recursion in tree structures.

The inherent problem with arrays:

```
// various types are converted to int
$array[1.23] = "Foo";       // actual value: [ 1 => "Foo" ]
$array[true] = "Foo";       // actual value: [ 1 => "Foo" ]
$array[null] = "Foo";       // actual value: [ "" => "Foo" ]

// Other types are fatal errors
$array[ [1,2,3] ];          // PHP Fatal error:  Illegal offset type
$array[ $user ];            // PHP Fatal error:  Illegal offset type
$array[ tmpfile() ];        // PHP Fatal error:  Illegal offset type
```


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


Friend of a Friend traversal example (FOAF)
-------------------------------------------

```
$friends = new Map();
$friends[$user_A][$user_B] = 1;                   // register all current friendships
$friends[$user_A][$user_C] = 1;
$friends[$user_B][$user_C] = 1;                   
$friends[$user_B][$user_D] = 1;                   // only $user_B is friend with $user_D

// traverse friends of friends graph
foreach ( $friends[$user_A] as $friend_1 => $distance_1) {

    foreach ($friends[$friend] as $friend_2 => $distance_2) {

        if ( $user_A === $friend_2 ) {
            // ignore friendships back to myself
        } elseif ( isset( $friends[$user_A][$friend_2] ) ) {
            // i am already a friend
        } else {
            echo "$friend_2 is a friend of a friend to you!\n";
        }
    }
}
```

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
