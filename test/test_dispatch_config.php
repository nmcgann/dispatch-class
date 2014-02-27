<?php
/**
 * Tests for dispatch Config
 */
include 'test.php';
include('test_utils.php');
// Set up
$test=new Test;
$title = 'Tests for Dispatch Class';

include('../Dispatch_class.php');
// set up for tests here

class testDispatch extends Dispatch {
    
    public $error_msg = '';
    public $error_code = '';
     
    //custom error and exception handlers set to convert trigger_error() to exception
    //this allows try/catch to be used to continue execution when a function
    //calls trigger_error() with E_USER_ERROR. Essential for testing!
     public function __construct()
     {  
        parent::__construct();
        
        $di = $this;
        
		set_exception_handler(
			function($obj) use ($di) { 

                if($obj->getcode() !== E_USER_ERROR) 
                    die('Exception handler called with an unexpected error (not E_USER_ERROR)');              
               return true;
			}
		);
		set_error_handler(
			function($code,$text) {
				if (1 /*error_reporting() */) 
					throw new ErrorException($text,$code);
			}
		);
    }       
  
}

// ----------------------------------------------------------------------------
//create test object
$di = testDispatch::instance();

$test->expect(
    is_object($di),
    'Called Dispatch::instance() test for returned object is true.'
);

$test->expect(
    is_callable(array($di, 'config')),
    'config() is a callable function is true'
);

// ----------------------------------------------------------------------------
//create a temporary file name
$tmpfname = tempnam(sys_get_temp_dir(), 'TST');
//delete so doesn't exist
unlink($tmpfname);

//test expected to fail with a trigger_error() - converted to exception in test class
//so error can be trapped and execution can continue
try
{
    $di->config('source',$tmpfname);
}
catch (Exception $e)
{
    $di->error_msg = $e->getmessage();
    $di->error_code = $e->getcode();
}

$test->expect(
    preg_match("#^File passed to config\(\'source\'\) not found$#", $di->error_msg),
    'Attempt to load non-existent config file triggers error is true'
);
$test->expect(
    $di->error_code === E_USER_ERROR,
    '--> error code is E_USER_ERROR is true'
);
// ----------------------------------------------------------------------------
$di->error_msg = '';
$di->error_code = '';

//create a temporary file name
$tmpfname = tempnam(sys_get_temp_dir(), 'TST');

$str = <<< EOC
; This is a sample configuration file
; Comments start with ';', as in php.ini

;not in a keyed section
testval1 = 1234
testval2 = "this is a string"
[global]
;in a keyed section
testval3 = 'value3'

EOC;
//write temp file as include file
file_put_contents($tmpfname,$str);

try
{
    $di->config('source',$tmpfname);
}
catch (Exception $e)
{
    $di->error_msg = $e->getmessage();
    $di->error_code = $e->getcode();
}
//get rid of temporary file after reading
unlink($tmpfname);

$test->expect(
    $di->error_msg === '' && $di->error_code === '',
    'Attempt to read temporary config file succeeds is true'
);
$test->expect(
     ($val = $di->config('testval1')) === '1234',
    '--> Attempt to read key set in config file succeeds is true'
);

$test->expect(
     ($val = $di->config('testval3')) !== 'value3',
    '--> Attempt to read key set in config file hierarchical section fails is true'
);
$val = $di->config('global');
$test->expect(
     $val['testval3'] === 'value3',
    '--> Attempt to read key set in config file hierarchical section suceeds is true'
);

// ----------------------------------------------------------------------------
//empty config
$di->config();
$conf_data = test_get_property($di,'config_config');

$test->expect(
     empty($conf_data),
    'Call to config() to reset all data suceeds is true'
);

// ----------------------------------------------------------------------------
$test_arr = array('key1'=>'val1','key2'=>'val2','key3'=>'val13');

$di->config($test_arr);

$conf_data = test_get_property($di,'config_config');
$t = array_diff_assoc($conf_data,$test_arr);
$test->expect(
     empty($t),
    'Call to config() to add an array of config data suceeds is true'
);

$test_arr2 = array('key4'=>'val4','key5'=>'val5','key6'=>'val16');
$di->config($test_arr2);

$conf_data = test_get_property($di,'config_config');
$t = array_diff_assoc($conf_data,$test_arr+$test_arr2);

$test->expect(
     empty($t),
    'Call to config() to add an additional array of config data suceeds is true'
);
$test->expect(
     ($val = $di->config('key4')) === 'val4',
    '--> Attempt to read key set in config array succeeds is true'
);
$test->expect(
     ($val = $di->config('key7')) === null,
    '--> Attempt to read non-existent key returns null is true'
);



//display results
include 'test_display_helper.php';

/* end */
