<?php
ini_set("display_errors", 1);
$a = array('2','3','4');

echo "<br/>";
echo "original array::"; print_r($a);
echo "<br/>";
echo "original sum::".array_sum($a);
echo "<br/>";
$total = 0;
/*foreach ($a as $key => $value) {
	$b = $a;
	$temp = $a[$key]/2;
	$b[$key]= $temp;
	echo "<br>";
	print_r($b);
	echo "new sum::".array_sum($b);
	$sum = array_sum($b);
	if ($sum < $total){
		$total = $sum;
	}
}*/

foreach ($a as $key => $value) {
	$temp = $a[$key]/2;
	$a[$key]= $temp;
	echo "<br>";
	print_r($a);
	echo "new sum::".array_sum($a);
	$sum = array_sum($a);
	if ($sum < $total){
		$total = $sum;
	}
}
echo "<br/>";
echo "final smallest sum::". $total;