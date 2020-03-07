<?php
//testing command line arguments using 'getopt' function

$shortopts = "u:p:h:";
$longopts = array("file:", "create_table", "dry_run");

$options = getopt($shortopts, $longopts);
var_dump($options);
//print_r($options);

try {
    //setup postgresql connection variables
    $host = "host=127.0.0.1";
    $port = "port=5432";
    $dbname = "dbname=userdb";
    $credentials = "user=postgres password=admin";

    //connect to postgresql database
    $db_connection = pg_connect("$host $port $dbname $credentials");
    
    if (!$db_connection) {
        throw new Exception("Unable to connect to database\n");
    } else {
        echo "Connected to database successfully!\n";
    }

    pg_query($db_connection, "DELETE FROM users"); //for testing purpose so that script can run again without errors
    
    // Open the CSV file passed as argument for reading and print line by line
    if (($h = fopen("{$options['file']}", "r")) !== FALSE) {
    
        while (($data = fgetcsv($h, 1000, ",")) !== FALSE) 
        {		
            if ($data[0] !== null && $data[0] !== "name") { //check if data exits and not the first record which is "name, surname, email"
                //Read the data from a single line
                //store record in variables and trim white spaces and escaping special characters like quotes
                $name = pg_escape_literal(trim($data[0]));
                $surname = pg_escape_literal(trim($data[1]));
                $email = pg_escape_literal(trim($data[2]));

                echo "$name $surname $email \n";

                //INSERT record into database
                $result = pg_query($db_connection, "INSERT INTO users (name, surname, email) VALUES ({$name}, {$surname}, {$email})");
            }
            
        }
        // Close the file
        fclose($h);
    } 
}

//catch exception
catch(Exception $e) {
  echo 'Error Message: ' .$e->getMessage();
}


?>
