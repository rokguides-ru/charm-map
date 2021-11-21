Charm/Map
============

A fast and memory efficient map implementation implementation with O(1) performance for all
operations.

A map is different from PHP's built-in array in that you can use any value as the key. Normal
arrays don't allow you to use float numbers, resource pointers or objects as a key.

Features
--------

 * Any value as the key, including `null`, `true`, `false`, objects and resources
 * Maintains insertion order (even for integer keys)

Caution with arrays as keys
---------------------------

Using arrays as keys is not recommended; it works - but relies on serializing the array to
generate a hash, which is slow for larger arrays. Arrays that contains resources or objects
may not be serializable.

