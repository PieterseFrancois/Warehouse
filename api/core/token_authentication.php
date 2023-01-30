<?php

/*
    Methods in use:
        +generateJwt( $user_id, $user_info = [], $current_jwt = null )
        -createPayload( $user_info = [], $current_jwt = null )
        -createJwt( $payload )

        +authorize( $class, $controller )
        -validateJwt( $jwt_validate, $payload )

        -decodeToken( $jwt )
        -base64urlEncode( $string )
    
        -databaseReadJwt( $user_id )
        -databaseStoreJwt( $jwt, $user_id )
*/

class TokenAuthentication
{
//generate jwt
    static function generateJwt( $user_info = [], $current_jwt = '' )
    {      
        //assemble payload
            $payload = self::createPayload( $user_info, $current_jwt );

            if ( $payload === false )
                return false;

        //assemble jwt
            $jwt = self::createJwt( $payload );

        //save in database
            //get user id from payload
            $user_id = $payload['id'];

            $result = self::databaseStoreJwt( $jwt, $user_id );

            if ( $result !== true )
                return false;

        return $jwt;
    }

    private static function createPayload( $user_info, $current_jwt )
    {
        //check if need to work from existing payload
            if ( $current_jwt !== '' )
            {
                $token_info = self::decodeToken( $current_jwt );

                if ( $token_info === false )
                    return false;

                $payload = $token_info['payload'];
            }  

        //in all cases exp will be adjusted
            $payload['exp'] = time() + JWT::$session_time_constant_seconds;            

        //if the user_info array has data, merge with payload
            $payload = array_merge( $payload, $user_info );

        return $payload;
    }

    private static function createJwt( $payload )
    {
        //encode headers and payload
            $header_encoded = self::base64urlEncode( json_encode( JWT::$header ) );
            $payload_encoded = self::base64urlEncode( json_encode( $payload ) );

        //generate signature and encode
            $signature = hash_hmac( 'SHA256', "$header_encoded.$payload_encoded", JWT::$private_key, true );
            $signature_encoded = self::base64urlEncode( $signature );
    
        //build jwt
            $jwt = "$header_encoded.$payload_encoded.$signature_encoded";
            
        return $jwt;
    }

//authorize
    static function authorize( $class, $controller )
    {
        //test if in exempt list
            if ( in_array( "$class::$controller", JWT::$exemption_list ) )            
                return true;
    
        //test if token is set
            if ( !isset( $_SERVER['HTTP_AUTHORIZATION'] ) )
                return 'Token is not set.';

        //retrieve and sanitize token    
            $jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt_clean = Validation::sanitize( $jwt );  //necessary?

            if ( $jwt !== $jwt_clean )  //ensures $_SERVER['HTTP_AUTHORIZATION'] can be safely used later
                return 'Invalid token. _a_';    

        //deconstruct token
            $token_info = self::decodeToken( $jwt );

            if ( $token_info === false )
                return 'Invalid token. _b_';

        //validate token
            $result = self::validateJwt( $jwt_clean, $token_info['payload'] );

            if ( $result !== true )
                return $result;

        //test if correct role permission
            $in_protected_array = in_array( "$class::$controller", JWT::$admin_protected_list );
            $admin_role = ( $token_info['payload']['role'] === 'admin' );

            if ( $in_protected_array && !$admin_role )
                return "Unauthorized user.";

        return true;   
    }

    private static function validateJwt( $jwt_validate, $payload )
    {

        //payload NULL -> necessary?
            if ( is_null( $payload ) )
                return "Invalid token. _c_"; 
        
        //user_id
            if( !isset( $payload['id'] ) )
                return "Invalid token. _d_";   
                        
            $user_id_clean = Validation::sanitize( $payload['id'] );

        //get jwt from database
            list( $success, $jwt_read_result ) = self::databaseReadJwt( $user_id_clean );

            if( !$success )
                return $jwt_read_result;

        //get jwt from read result and compare
            $jwt_read_result = array_shift( $jwt_read_result );
            $jwt_db = $jwt_read_result['jwt'];      

            if ( $jwt_db !== $jwt_validate )
                return "Invalid token. _e_";
                    
        //expiration time
            //not necessary to test if exp is set as above verification confirmed token is the same

            $exp = $payload['exp'];

            if ( $exp < time() )
                return "Token is no longer valid.";

        //verify role remained the same: get role from read result and payload and compare -> multi user scenario
            //not necessary to test if exp is set as above verification confirmed token is the same
            $role_db = $jwt_read_result['role'];                         

            if ( $role_db !== $payload['role'] )
                return "Role has been changed since last authentication.";

        //build signature again from header and payload and compare?

        return true;
    }

//multi-purpose
    private static function decodeToken( $jwt )
    {
        $token_info = [];

        //split up the JWT
            $token_parts = explode( '.', $jwt );
            
            if ( count( $token_parts ) !== 3 )  //expecting three parts
                return false;

        //decode token parts
            //header
            $header_decoded = base64_decode( $token_parts[0] );

            if ( $header_decoded === false )    //test if error occurred
                return false;

            $token_info['header'] = json_decode( $header_decoded, true );

            //payload
            $payload_decoded = base64_decode( $token_parts[1] );

            if ( $payload_decoded === false )    //test if error occurred
                return false;
           
            $token_info['payload'] = json_decode( $payload_decoded, true );

            //signature
            $token_info['signature'] = $token_parts[2];
       
        return $token_info;
    }

    private static function base64urlEncode( $string )
    {
        $string_temp = base64_encode( $string );

        $string_temp = strtr( $string_temp, '+/', '-_' );

        $string_temp = rtrim( $string_temp, '=' );

        return $string_temp;
    }

//database interaction
    private static function databaseReadJwt( $user_id )
    {
        //prepare query
            $sql_query = "SELECT role, jwt 
                          FROM ( " . SQL::$table_users . " )
                          WHERE ( id = :id )";

            $parameters = [
                ':id' => $user_id,
            ];

        //execute query
            list( $success, $result ) = Database::executeSQL( $sql_query, $parameters );

        return [ $success, $result ];
    }

    private static function databaseStoreJwt( $jwt, $user_id )
    {                                                  
        //setup query
            $exp = time() + JWT::$session_time_constant_seconds;

            $sql_query = "UPDATE " . SQL::$table_users . " 
                        SET jwt = :jwt, tokenExpiry = :exp
                        WHERE ( id = :id )";

            $parameters = [
                ':jwt' => $jwt,
                ':exp' => $exp,
                ':id' => $user_id,
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );

            if ( $success !== true )
                return $message;
            
        return true;    
    }

}