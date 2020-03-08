<?php
//Install and load 'Commando' - An Elegant PHP CLI Library
//This library makes it easier to deal with CLI arguments
try {
    require_once 'vendor/autoload.php';

    $cmd = new Commando\Command();
    $cmd->beepOnError();
    
    //Function which defines all the command line options
    function defineCommandOptions(&$cmd) {
        //Define the flag "--file" CSV filename
        $cmd->option('file')
        ->describedAs('This is the name of CSV file to be parsed.');

        //Define the flag "-u" PostgreSQL username
        $cmd->option('u')
            ->aka('username')
            ->describedAs('This is the PostgreSQL username.')
            ->default('postgres'); //set default username, in case no -u option is passed

        //Define the flag "-p" PostgreSQL password
        $cmd->option('p')
        ->aka('password')
        ->describedAs('This is the PostgreSQL password.')
        ->default('admin'); //set default password, in case no -p option is passed

        //Define the flag "-h" PostgreSQL host
        $cmd->option('h')
            ->aka('host')
            ->describedAs('This is the PostgreSQL host.')
            ->default('127.0.0.1'); //set default hostname, in case no -h option is passed

        //Define the flag "--create_table" directive
        $cmd->option('create_table')
            ->describedAs('This will cause the PostgreSQL users table to be built (and no further action will be taken).')
            ->boolean();

        //Define the flag "--dry_run" directive
        $cmd->option('dry_run')
            ->needs('file')
            ->describedAs('This will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won\'t be altered.')
            ->boolean();
    }

    function connectToDatabase(&$cmd) {
        //setup postgresql connection variables
        $host = "host={$cmd['h']}";
        $port = "port=5432";
        $dbname = "dbname=postgres";
        $credentials = "user={$cmd['u']} password={$cmd['p']}";

        //connect to PostgreSQL database
        $conn = pg_connect("$host $port $dbname $credentials");
        
        if (!$conn) {
            throw new Exception("Unable to connect to database\n");
        }
        //return the connection resource
        return $conn;
    }

    //execute the defineCommandOptions function by passing the $cmd object
    defineCommandOptions($cmd);

    //execute the connectToDatabase function which returns the connection resource
    $db_connection = connectToDatabase($cmd);

    //check if --create_table flag is passed. If yes, just create users table and exit program
    if ($cmd['create_table']) {

        $result = pg_query($db_connection, "CREATE TABLE users(
            name VARCHAR (50) NOT NULL,
            surname VARCHAR (50) NOT NULL,
            email VARCHAR (355) UNIQUE NOT NULL
         )");

        if (!$result) {
            throw new Exception("Unable to create users table.\n");
        } else {
            //if users table is created successfully, EXIT execution
            exit("Table users created successfully!\n");
        }
    }

    //check if --file command line option is provided. If yes, store in a variable, else throw error
    if ($cmd['file'] !== null) {
        $filename = $cmd['file'];

        //Before going further, check if the table 'users' exists in database.
        $result = pg_query($db_connection, "SELECT EXISTS (SELECT relname FROM pg_class WHERE relname = 'users')");
        $doesTableExist = pg_fetch_result($result, 0); //this will return "t" if query returned true (users table exists) or "f" if false
        
        //If 'users' table does not exist AND --dry_run command line option is not passed, throw error.
        //If --dry_run option is passed, it does not matter if 'users' table exist or not since no INSERT will be made. So we can go ahead.
        if ($doesTableExist === "f" && !$cmd['dry_run']) {
            throw new Exception("Table 'users' does not exist. Run the script again passing the --create_table directive.", 1);
        }
    }
    else {
        throw new Exception("--file [csv file name] option not provided. Run the script again providing the CSV file name to be parsed!", 1);
    }
    
    // Open the CSV file passed as argument for reading and print on STDOUT/insert into DB line by line
    if (($h = fopen("{$filename}", "r")) !== FALSE) {
    
        while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {

            if ($data[0] !== null && $data[0] !== "name") { //check if data exits and is not the first record in CSV file which is "name, surname, email"
                //Read the data from a single line
                //store record in variables and trim white spaces, convert to lowercase and capitalize Name and Surname.
                $name = ucfirst(strtolower(trim($data[0])));
                $surname = ucfirst(strtolower(trim($data[1])));
                $email = strtolower(trim($data[2]));

                //Remove all illegal characters from email 
                $email = filter_var($email, FILTER_SANITIZE_EMAIL); 
                
                //Validate Email. if not valid, show error message on STDOUT (screen) and skip inserting into DB 
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
                    echo("Error: $email is NOT a valid email address. Record will not be inserted into database!\n");
                    continue;
                }  

                //Escape special characters like quotes before inserting into database
                $name = pg_escape_literal($name);
                $surname = pg_escape_literal($surname);
                $email = pg_escape_literal($email);

                //check if --dry_run command line directive is provided, If yes, skip inserting record into database
                if (!$cmd['dry_run']) {
                    //INSERT record into database
                    echo "Inserting record $name $surname $email into database!\n";
                    $result = pg_query($db_connection, "INSERT INTO users (name, surname, email) VALUES ({$name}, {$surname}, {$email})");
                }
            }
            
        }
        // Close the file
        fclose($h);
    }
    //If --dry_run command line directive is provided, show message on STDOUT that dry run is complete
    if ($cmd['dry_run']) {
        //display message on screen
        echo "...dry run is complete! Database is not altered.";
    }

    //close PostgreSQL Database connection
    pg_close($db_connection); 
}

//catch exception
catch(Exception $e) {
  echo 'Error Message: ' .$e->getMessage();
}


?>
