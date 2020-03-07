<?php
//testing command line arguments using 'getopt' function

$shortopts = "u:p:h:";
$longopts = array("file:", "create_table", "dry_run");

$options = getopt($shortopts, $longopts);
var_dump($options);
//print_r($options);

// Open the CSV file passed as argument for reading and print line by line
if (($h = fopen("{$options['file']}", "r")) !== FALSE) {
  
    while (($data = fgetcsv($h, 1000, ",")) !== FALSE) 
    {		
        if ($data[0] != null) {
            // Read the data from a single line
            echo "$data[0] $data[1] $data[2] \n";
        }
        
    }
    // Close the file
    fclose($h);
} 

?>