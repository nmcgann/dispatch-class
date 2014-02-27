<?php
/**
 * Tests for dispatch Routing
 */
include 'test.php';
include('test_utils.php');
// Set up
$test=new Test;
$title = 'Tests for Dispatch Class';

include('../Dispatch_class.php');
// set up for tests here

class testDispatch extends Dispatch {
  
  public function __construct()
  {  
    parent::__construct();
  }
    
    public function call_exit($msg = '')
    {
        echo 'Mock from call_exit: '.$msg."\n";
        //stubbed out routine with one that just returns to allow testing
    }
    
    protected function send_header()
    {
        //stubbed out routine with one that just echos parameters to allow testing
        $num_args = func_num_args();
        
        echo 'Mock from send_header:'."\n";
        echo json_encode(func_get_args())."\n";
       
    }    
}

function get_on($obj,$method,$path,$callback=null)
{
    ob_start();
    $obj->on($method,$path,$callback);
    $buff = ob_get_clean();
    
    return $buff;
}

$di = testDispatch::instance();
//$di->call_exit();

$test->expect(
    is_object($di),
    'Called Dispatch::instance() test for returned object is true.'
);

$test->expect(
    is_callable(array($di, 'on')),
    'on() is a callable function is true'
);

//simple callback with no parameters
$test_callback = function(){ echo "@in test callback@\n"; return ;};
$test_callback1 = function(){ echo "@in test callback1@\n"; return ;};
$test_callback2 = function(){ echo "@in test callback2@\n"; return ;};
$test_callback3 = function(){ echo "@in test callback3@\n"; return ;};

//no routes set
$buff = get_on($di,'GET','');
$test->expect(
    !preg_match('#^@in test callback@$#',preg_quote($buff,'#')),
    'Route (none set) does not match "" is true'
);

$buff = get_on($di,'GET','/');
$test->expect(
    !preg_match('#^@in test callback@$#',preg_quote($buff,'#')),
    'Route (none set) does not match "/" is true'
);


//simple routes distinguishing callbacks
$di->on('GET','/',$test_callback);
$di->on('GET','/test1',$test_callback1);
$di->on('GET','/test1/test2',$test_callback2);
$di->on('GET','/test3',$test_callback3);

//fake server values
$_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";

$buff = get_on($di,'GET','/');
$test->expect(
    preg_match('#^@in test callback@$#',preg_quote($buff,'#')),
    'Route "/" matches "/" is true'
);

$buff = get_on($di,'GET','/X');
$test->expect(
    !preg_match('#^@in test callback@$#',preg_quote($buff,'#')),
    'Route "/" not matches "/X" is true'
);
$test->expect(
    preg_match('#404#',preg_quote($buff,'#')),
    '--> "404" error code found in header response is true'
);

$buff = get_on($di,'GET','');
$test->expect(
    preg_match('#^@in test callback@$#',preg_quote($buff,'#')),
    'Route "/"  matches "" is true'
);

$buff = get_on($di,'GET','/test1');
$test->expect(
    preg_match('#^@in test callback1@$#',preg_quote($buff,'#')),
    'Route "/test1" matches "/test1" is true'
);

$buff = get_on($di,'GET','/test1/');
$test->expect(
    preg_match('#^@in test callback1@$#',preg_quote($buff,'#')),
    'Route "/test1" matches "/test1/" is true'
);

$buff = get_on($di,'GET','/test1/test2');
$test->expect(
    preg_match('#^@in test callback2@$#',preg_quote($buff,'#')),
    'Route "/test1/test2" matches "/test1/test2" is true'
);
$buff = get_on($di,'GET','/test3/');
$test->expect(
    preg_match('#^@in test callback3@$#',preg_quote($buff,'#')),
    'Route "/test3" matches "/test3/" is true'
);

//reset the object and start over
$test->message('*** Reset Object ***');
test_set_property($di,'instances',null);
$di = testDispatch::instance();

$buff = get_on($di,'GET','/');
$test->expect(
    preg_match('#404#',preg_quote($buff,'#')),
    'After reset and re-creation of class, route (none) does not match "/" is true'
);

//one named parameter
unset($test_callback);
unset($test_callback1);
unset($test_callback2);
unset($test_callback3);

$test_callback = function($p){ echo "@in test callback@".$p."\n"; return ;};
$test_callback1 = function($p,$q){ echo "@in test callback1@".$p."@",$q."\n"; return ;};
$test_callback2 = function($p,$q,$r){ echo "@in test callback2@".$p."@",$q."@",$r."\n"; return ;};

$buff = get_on($di,'GET','/:param1',$test_callback);

$buff = get_on($di,'GET','/0123456789');
$test->expect(
    preg_match('#^@in test callback@0123456789$#',preg_quote($buff,'#')),
    'Route "/:param1" matches "/0123456789" is true'
);
$buff = get_on($di,'GET','/_a_b_c_d_e_f_');
$test->expect(
    preg_match('#^@in test callback@_a_b_c_d_e_f_$#',preg_quote($buff,'#')),
    'Route "/:param1" matches "/_a_b_c_d_e_f_" is true'
);

$buff = get_on($di,'GET','/abcdef/xyz');
$test->expect(
    preg_match('#404#',preg_quote($buff,'#')),
    'Route "/:param1" does not match "/abcdef/xyz" is true'
);

$buff = get_on($di,'GET','/:param1/:param2',$test_callback1);

$buff = get_on($di,'GET','/abcdef/xyz');
$test->expect(
    preg_match('#^@in test callback1@abcdef@xyz$#',preg_quote($buff,'#')),
    'Route "/:param1/:param2" does match "/abcdef/xyz" is true'
);

$buff = get_on($di,'GET','/abcdef/xyz/123');
$test->expect(
    preg_match('#404#',preg_quote($buff,'#')),
    'Route "/:param1/:param2" does not match "/abcdef/xyz/123" is true'
);

$buff = get_on($di,'GET','/:param1/:param2/[a-z]+',$test_callback1);
$buff = get_on($di,'GET','/abcdef/xyz/123');
$test->expect(
    preg_match('#404#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/[a-z]+" does not match "/abcdef/xyz/123" is true'
);
$buff = get_on($di,'GET','/abcdef/xyz/hello');
$test->expect(
    preg_match('#^@in test callback1@abcdef@xyz$#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/[a-z]+" does match "/abcdef/xyz/hello" is true'
);

$buff = get_on($di,'GET','/:param1/:param2/:param3@\d+',$test_callback2);
$buff = get_on($di,'GET','/abcdef/xyz/123');
$test->expect(
    preg_match('#^@in test callback2@abcdef@xyz@123$#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/:param3@\d+" does match "/abcdef/xyz/123" is true'
);

$buff = get_on($di,'GET','/:param1/:param2/ABC:param3@\d+',$test_callback2);
$buff = get_on($di,'GET','/abcdef/xyz/123/ABC');
$test->expect(
    preg_match('#404#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/ABC:param3@\d+" does not match "/abcdef/xyz/123/ABC" is true'
);
$buff = get_on($di,'GET','/abcdef/xyz/ABC9');
$test->expect(
    preg_match('#^@in test callback2@abcdef@xyz@9$#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/ABC:param3@\d+" does match "/abcdef/xyz/ABC9" is true'
);

$buff = get_on($di,'GET','/:param1/:param2/:param3@ABCD*',$test_callback2);
$buff = get_on($di,'GET','/abcdef/xyz/ABC');
$test->expect(
    preg_match('#404#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/:param3@ABCD*" does not match "/abcdef/xyz/ABC" is true'
);
$buff = get_on($di,'GET','/abcdef/xyz/ABCD');
$test->expect(
    preg_match('#^@in test callback2@abcdef@xyz@ABCD$#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/:param3@ABCD*" matches "/abcdef/xyz/ABCD" is true'
);
$buff = get_on($di,'GET','/abcdef/xyz/ABCD/123/efg/_23_');
$test->expect(
    preg_match('#^@in test callback2@abcdef@xyz@ABCD/123/efg/_23_$#',preg_quote($buff,'#')),
    'Route "/:param1/:param2/:param3@ABCD*" matches "/abcdef/xyz/ABCD/123/efg/_23_" is true'
);

//reset the object and start over
$test->message('*** Reset Object ***');
test_set_property($di,'instances',null);
$di = testDispatch::instance();
unset($test_callback);
unset($test_callback1);
unset($test_callback2);
unset($test_callback3);

$test_callback = function($p,$q){ echo "@in test callback@".$p.(is_null($q)?'':'@'.$q)."\n"; return ;};

$buff = get_on($di,'GET','/:param1(/:param2)',$test_callback);
$buff = get_on($di,'GET','/abcdef/xyz');
$test->expect(
    preg_match('#@in test callback@abcdef@xyz#',preg_quote($buff,'#')),
    'Route "/:param1(/:param2)" does match "/abcdef/xyz" is true'
);
$buff = get_on($di,'GET','/abcdef/');
$test->expect(
    preg_match('#@in test callback@abcdef#',preg_quote($buff,'#')),
    'Route "/:param1(/:param2)" does match "/abcdef/" is true'
);

//var_dump($buff);

//display results
include 'test_display_helper.php';

/* end */