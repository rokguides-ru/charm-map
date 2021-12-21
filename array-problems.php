<?php

var_dump([ '1' => 'key converted to int' ]);
var_dump([ 1.23 => 'key converted to int' ]);
var_dump([ false => 'key converted to int' ]);
var_dump([ true => 'key converted to int' ]);
var_dump([ 1.23 => 'my key is not 1.70' ][1.70]);
var_dump([ false => 'my key is not 0' ][0]);
var_dump([ 0 => 'my key is not false' ][false]);
var_dump([ false => 'my key is not 0' ][0]);
var_dump([ 1 => 'my key is not true' ][true]);
var_dump([ true => 'my key is not 1' ][1]);
var_dump([ null => 'my key is not ""' ][""]);
