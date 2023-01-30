<?php

/*
    Methods in use:
        +parse()

        -validateParameterExistence( $class, $controller )
*/

class URL_parser
{
    static function parse()
    {

        //get URI and remove first character which is a '/'
            $path = substr( $_SERVER['REQUEST_URI'], 1 );

            $parameters = explode( '/', $path );

        //test number of parameters 
            if ( count( $parameters ) !== URL::$parameter_count )
                return [ 'The path of the URL can only contain ' . URL::$parameter_count . ' parameters.', null, null ] ;

        //assign parameters to their function
            $class = $parameters[0];
            $controller = $parameters[1];

        //sanitize paramaters
            $class = Validation::sanitize( $class );
            $controller = Validation::sanitize( $controller );

        //validate the parameters
            $validation_result = URL_parser::validateParameterExistence( $class, $controller );

            if ( $validation_result !== true )
                return [ $validation_result, null, null  ];
        
        //decode body
            if ( empty( $_POST ) || is_null( $_POST ) )
                $_POST = json_decode( file_get_contents( "php://input" ), true );

        //check if NULL -> asssign empty array
            $_POST = is_null( $_POST ) ? [] : $_POST;

        //go through each element in the $_POST variable and sanitize
            foreach ( $_POST as $key => $value )
            {
                //sanitize input only if string -> if array ignore for now -> endpoint deal with it
                $key_clean = Validation::sanitize( $key );
                $value_clean = is_string( $value ) ? Validation::sanitize( $value ) : $value;                  

                unset( $_POST[$key] );
                $_POST[$key_clean] = $value_clean;
            }

        return [ true, $class, $controller ];
    }

    private static function validateParameterExistence( $class, $controller )
    {
        $class_file = "controllers/" . $class . ".php";

        if ( !file_exists( $class_file ) )
            return 'The file ' . $class_file . ' does not exist.';
        
        if ( !class_exists( $class ) )
            return 'The class \'' . $class . '\' does not exist.';

        if ( !method_exists( $class, $controller ) )
            return 'The endpoint \'' . $controller . '\' does not exist in the class \'' . $class . '\'.';

        return true;
    }
} 