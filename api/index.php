<?php

    header( 'Access-Control-Allow-Origin: *' );
    header( 'Access-Control-Allow-Methods: POST' );
    header( 'Access-Control-Allow-Headers: *');

    require 'controllers/_controllers.php';
    require 'core/_core.php';
    require 'config.php';

    //parse URL and sanitize
        list( $validation_result, $class, $controller ) = URL_parser::parse();

        if ( $validation_result !== true )
            Response::return( false, $validation_result );


    //if necessary, authorize token and extend expiry
        $result_authorization = Authorization::main( $class, $controller );

        if ( $result_authorization !== true )
            Response::return( false, "JWT_ERR: $result_authorization" );

            
    //execute the endpoint
        $class::$controller();