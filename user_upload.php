<?php
//Install and load 'Commando' - An Elegant PHP CLI Library
//This library makes it easier to deal with CLI arguments
try {
    require_once 'vendor/autoload.php';

    $cmd = new Commando\Command();
    $cmd->beepOnError();
    
    // Define the flag "--file" CSV filename
    $cmd->option('file')
        ->require()
        ->describedAs('This is the name of CSV file to be parsed.');

    // Define the flag "-u" PostgreSQL username
    $cmd->option('u')
        ->aka('username')
        ->describedAs('This is the PostgreSQL username.')
        ->default('postgres');
    
    // Define the flag "-p" PostgreSQL password
    $cmd->option('p')
    ->aka('password')
    ->describedAs('This is the PostgreSQL password.')
    ->default('admin');

    // Define the flag "-h" PostgreSQL host
    $cmd->option('h')
        ->aka('host')
        ->describedAs('This is the PostgreSQL host.')
        ->default('127.0.0.1');

    //setup postgresql connection variables
    $host = "host={$cmd['h']}";
    $port = "port=5432";
    $dbname = "dbname=userdb";
    $credentials = "user={$cmd['u']} password={$cmd['p']}";

    //var_dump($host);
    //var_dump($credentials);

    //connect to PostgreSQL database
    $db_connection = pg_connect("$host $port $dbname $credentials");
    
    if (!$db_connection) {
        throw new Exception("Unable to connect to database\n");
    } else {
        echo "Connected to database successfully!\n";
    }

    pg_query($db_connection, "DELETE FROM users"); //for testing purpose so that script can run again without errors
    
    // Open the CSV file passed as argument for reading and print line by line
    if (($h = fopen("{$cmd['file']}", "r")) !== FALSE) {
    
        while (($data = fgetcsv($h, 1000, ",")) !== FALSE) 
        {		
            if ($data[0] !== null && $data[0] !== "name") { //check if data exits and not the first record which is "name, surname, email"
                //Read the data from a single line
                //store record in variables and trim white spaces, convert to lowercase and capitalize Name and Surname.
                $name = ucfirst(strtolower(trim($data[0])));
                $surname = ucfirst(strtolower(trim($data[1])));
                $email = strtolower(trim($data[2]));

                //Remove all illegal characters from email 
                $email = filter_var($email, FILTER_SANITIZE_EMAIL); 
                
                //Validate Email. if not valid, show error message on STDOUT (screen) and skip inserting into DB 
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
                    echo("Error: $email is NOT a valid email address. Record not inserted into database!\n");
                    continue;
                }  

                //Escape special characters like quotes before inserting into database
                $name = pg_escape_literal($name);
                $surname = pg_escape_literal($surname);
                $email = pg_escape_literal($email);

                echo "$name $surname $email \n"; //print on screen for testing and debugging purpose

                //INSERT record into database
                $result = pg_query($db_connection, "INSERT INTO users (name, surname, email) VALUES ({$name}, {$surname}, {$email})");
            }
            
        }
        // Close the file
        fclose($h);
    }
    //close Database connection
    pg_close($db_connection); 
}

//catch exception
catch(Exception $e) {
  echo 'Error Message: ' .$e->getMessage();
}


?>
