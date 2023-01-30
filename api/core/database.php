<?php

/*
    Methods in use:
        +buildConnection()

        +executeSQL( $query, $parameters = [] )

*/

class Database {

    private static $connection = [];
    private static $pdo_parameters = [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    //build the connection to the database
    static function buildConnection()
    {
        try
        {
            $database_name = SQL::$credentials['database'];
            
            if ( !isset( self::$connection[$database_name] ) )
            {
                $connection_string = "mysql:host=" . SQL::$credentials['host'] . ";dbname=" . $database_name . ";port=" . SQL::$credentials['port'] . ";charset=utf8mb4";
                
                self::$connection[$database_name] = new PDO( $connection_string, SQL::$credentials['username'], SQL::$credentials['password'], self::$pdo_parameters );
            }

            return [ true, 'Success' ];
        }
        catch ( PDOException $e )
        {
            return [ false, "Database error occurred whilst creating record. " . $e->getMessage() . "; file: " . $e->getFile() . "; line " . $e->getLine() ];   
        }
    }  

    static function executeSQL( $query, $parameters = [] )
    {
        $query = trim( $query );
        $query = str_replace( ["\r","\n","\r\n"], '', $query );
        $query_type = explode(' ', $query)[0];

        list( $success, $message ) = self::buildConnection();

        if ( !$success )
            return [ false, "Database error occurred whilst creating record. PDO connection failed." . $message ];

        try
        {
            $database_name = SQL::$credentials['database'];
            
            //  Prepare the query statement and bind the parameters, if any
            $query_session = self::$connection[ $database_name ] -> prepare( $query );

            foreach ( $parameters as $key => $parameter )
                $query_session -> bindValue( $key, $parameter );

            //  Execute the query
            $result = $query_session -> execute();
            if ( $result === false)
                return [ false, "Database error occurred whilst creating record. The database query was unsuccessful." ];

            //  Structure function output based on the query type
            switch ( $query_type )
            {
                case 'SELECT':
                    // Output data of each row
                    $result = $query_session -> fetchAll( PDO::FETCH_ASSOC );
                    return [ true, $result ];

                case 'INSERT':
                    return [ true, self::$connection[$database_name] -> lastInsertId() ];

                case 'UPDATE':
                    return [ true, 1 ];

                default:
                    return [ true, 0 ];
            }
        } 
        catch ( PDOException $e )
        {
            return [ false, "Database error occurred whilst creating record. Query failed! PDO error: " . 
                            $e->getMessage() . "; file: " . $e->getFile() . "; line " . $e->getLine() . "; code " . $e->getCode() ];
        }
    }

}