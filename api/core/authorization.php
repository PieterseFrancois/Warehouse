<?php

class Authorization
{
    static function main( $class, $controller )
    {
        //authorise token
            $result_authorization = TokenAuthentication::authorize( $class, $controller );

            if ( $result_authorization !== true )
                return $result_authorization;

        //update jwt exp
            //test if in exempt list
            if ( in_array( "$class::$controller", JWT::$exemption_list ) )            
                return true;
                
            $current_jwt = $_SERVER['HTTP_AUTHORIZATION'];
            $jwt_result = TokenAuthentication::generateJwt( current_jwt: $current_jwt );

            //set extra message to indicate token could not be extended
            if ( $jwt_result == false )
            {
                Response::$extra_message = " Token expiry could not be extended.";
                Response::$jwt = $current_jwt;
            }
            else
                Response::$jwt = $jwt_result;  
        
        return true;
    }      
}

