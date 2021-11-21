<?php
require 'vendor/autoload.php';

$map = new Charm\Map();

$test = new stdClass;
$test2 = new stdClass;

$map[$test] = "Frode";
$map[$test2] = "Børli";

$map[1.23] = "Hallois!";
$map[1.24] = "Hallaballa";
$map[] = "Heisann";

$map[[123]] = "Hllo";

foreach ($map as $instance => $value) {
    var_dump($instance, $value);
}

echo "COUNT: ".count($map)."\n";
unset($map[1.23]);
echo "COUNT: ".count($map)."\n";
