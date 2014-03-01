# Dispatch - Experimental Class Version

This is an experimental version of Dispatch converted to class-based to improve
testability. A procedural facade is also implemented so it can be used in exactly the
same way as normal Dispatch.

A few enhancements are included e.g. a class autoloader. Just because.

**This is hacked code intended as proof-of-principle. Yes, it's ugly - I know.**

This is how to load it up:

```php
<?php
//This is index.php

require (__DIR__.DIRECTORY_SEPARATOR.'dispatch_class.php');

//set up a config. Only the 'dispatch.autoload' has to be set in the constructor
// all other config items can be set exactly as in non-class dispatch.

$config = array(
  'dispatch.autoload' => true,
  'dispatch.plugin_paths' => 'vendor/',
  'dispatch.autoload_paths' => 'lib/;helpers/'
);

$di = Dispatch::instance($config);

//The following statements are exactly the same now:

$di->config('dispatch.views','views/');

config('dispatch.views','views/');

//...the rest of the program is here ...

```

New config items added are `dispatch.autoload` (defaults to false=off), `dispatch.plugin_paths`,
`dispatch.autoload_paths` and `dispatch.request`.

`dispatch.request` is an array filled in by the framework before routing begins and has `method`,
`base`, `host`, `scheme` and `ajax` keys that have info about the current request.

The test sub directory has some very crude tests done with an adaptation of
the test class from Fat Free Framework. These are also a proof-of-principle
to show that the class-based version can be properly tested if someone has the
time to write the tests.....

## Original Version Code
Get the original code on GitHub: <http://github.com/noodlehaus/dispatch>.

## About the Author

Dispatch is written by [Jesus A. Domingo].

[Jesus A. Domingo]: http://noodlehaus.github.io/

## Credits and Contributors

This experimental class-based version converted from the original by Neil McGann

* nmcgann [nmcgann](https://github.com/nmcgann)

## LICENSE
MIT <http://noodlehaus.mit-license.org/>
