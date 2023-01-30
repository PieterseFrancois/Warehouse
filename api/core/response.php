<?php

class Response
{
    static $jwt = "";   //approach work on multi user?
    static $extra_message = "";

    static function return( $success, $message, $data = null )
    {
        $response['success'] = $success;
        $response['message'] = $message . self::$extra_message;

        if ( self::$jwt !== "" )
            $response['token'] = self::$jwt;
        
        if ( !is_null( $data ) )
        {
            $response['data'] = $data;
        }

        exit( json_encode( $response ) );
    }
}