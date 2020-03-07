<?php
//testing command line arguments

if (isset($argc)) {
	for ($i = 0; $i < $argc; $i++) {
		echo "Argument #" . $i . " - " . $argv[$i] . "\n";
	}
}
else {
	echo "argc and argv disabled\n";
}

$shortopts = "u:p:h:";
$longopts = array("file:", "create_table", "dry_run");

$options = getopt($shortopts, $longopts);
var_dump($options);
//print_r($options);

?>