<?php
/**
 * Unit test display helper.
 * 
 */

if(!isset($test) || !is_object($test) || !method_exists($test,'results') || !method_exists($test,'message')) 
    die('Test Object not found');
if(!isset($title)) die('Title not set');
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------

// Display the results
$status = true;
$str = '<table><tr><th>Result</th><th>Test</th><th>Info</th></tr><tbody>';
foreach ($test->results() as $result) 
{

    $str .= '<tr>';
    if ($result['status'])
    {
        $str .= '<td><b style="color:green;">PASS</b></td>';
        $str .= '<td>'.$result['text'].'</td>';
        $str .= '<td></td>';
    }
    else
    {
        $str .= '<td><b style="color:red;">FAIL</b></td>';
       	$str .= '<td>'.$result['text'].'</td>';
 		$str .= '<td>'.$result['source'].'</td>';
 		$status = false;
    }
    
    $str .= '</tr>';

}
$str .= '</tbody></table>';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title><?php echo $title; ?></title>
<style>
body{
	width: 960px;
	margin: 0 auto;
	background:#fff;
	color:#000;
	font:14px Arial, Helvetica, sans-serif;
/*	position:relative;*/
}
#container {
	margin: 20px 20px;
}
table {
border-collapse:collapse;
}
table tr,td,th {
	border:1px solid black;	
}
</style>
</head>
<body>
<div id="container">
<h2><?php echo $title; ?></h2>
<h4><?php echo ($status)? '<b style="color:green;">ALL TESTS PASS</b>': '<b style="color:red;">SOME TESTS FAIL</b>'; ?></h4>
<?php echo $str; ?>
</div>
</body>
</html>