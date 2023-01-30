<?php

/*
    Methods in use:
        +sanitize( $string )

        +isSet( $expected_input )

        +validationGeneral( $string, $max_length = 255, $test_null = true )
        +loopGeneral( $fields )

        -validationNULL( $string )
        -validationLength( $string, $max_length )
*/

class Validation
{
    static function sanitize( $string )
    {
        //test if NULL or empty -> no sanitization required
        if ( is_null( $string ) || empty( $string ) ) 
            return $string;

        //remove white space and sanitize
        $string = trim( $string );
        $string = strip_tags( $string );
        $string = htmlspecialchars( $string );

        return $string;
    }

    //test if expected parameters is provided
    static function isSet( $expected_input )
    {
        foreach( $expected_input as $field )
        {
            if ( !isset( $_POST[$field] ) )
                return ucfirst( $field ) . ': The ' . $field . ' field is not specified.';
        }

        return true;
    }

    //main function to test general validation -> if maxlength is set to 0, no length validation will be performed
    static function validationGeneral( $string, $max_length = 255, $test_null = true )
    {
        if ( $test_null )
        {
            $result = self::validationNULL( $string );
            
            if ( $result !== true )
                return $result;
            else
            {
                if ( $max_length !== 0 )
                {
                    $result = self::validationLength( $string, $max_length );
                    return $result;
                }     
                
                return $result;
            }
        }
    }
    
    //function to loop through an associative array and call validationGeneral
    static function loopGeneral( $fields )
    {
        foreach ( $fields as $field => $parameters)
        {
            $result = call_user_func_array( "self::validationGeneral", $parameters );
        
            if ( $result !== true )
                return ucfirst( $field ) . ': ' . $result;
        }   

        return true;
    }

    private static function validationNULL( $string )
    {
        if ( is_null( $string ) || empty( $string ) ) 
            return 'The input cannot be empty or NULL.';
        
        return true;
    }

    private static function validationLength( $string, $max_length )
    {
        if ( strlen( $string ) > $max_length ) 
            return "The input cannot exceed $max_length characters in length.";
        
        return true;
    }
}