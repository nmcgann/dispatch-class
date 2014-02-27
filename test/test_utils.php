<?php
/**
 * Test utilities
 * 
 */

/**
 * test_set_property()
 * 
 * function to set a property in a class - works on public, protected and private.
 * 
 * @param mixed $class
 * @param mixed $name
 * @param mixed $value
 * @return void
 */
function test_set_property($class,$name,$value) {
  
	$ref = new ReflectionClass($class);
	$refProp = $ref->getProperty($name);
	$refProp->setAccessible( true );
	$refProp->setValue(null, $value);

}

/**
 * test_get_property()
 * 
 * function to get a property in a class - works on public, protected and private.
 * 
 * @param mixed $class
 * @param mixed $name
 * @return
 */
function test_get_property($class,$name) {
  
	$ref = new ReflectionClass($class);
	$refProp = $ref->getProperty($name);
	$refProp->setAccessible( true );
    
	return $refProp->getValue($class);
}

/* end */