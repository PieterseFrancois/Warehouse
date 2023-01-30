<?php

/*
    Methods in use:
        +create()
        -validateCreate( $email, $password, $name, $role )

        +update()
        -validateUpdate()
    
        +login()
        -validateLogin( $email, $password )
        -updateLoginTimeStamp( $id )
    
        -duplicateEmail( $email, $excluded_id = "" )
*/

class Users
{
//class variables
    private static $no_length_validation = 0;

//create
    static function create()
    {
        //test if all expected fields where given
            $expected_input = [ 'email', 'password', 'name', 'role' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );    

        //get values from body
            $email = $_POST['email'];
            $password = $_POST['password'];
            $name = $_POST['name'];
            $role = $_POST['role'];

        //validation
            $validation_result = self::validateCreate( $email, $password, $name, $role );

            if ( $validation_result !== true )
                Response::return( false, $validation_result );    
           
            $validation_result = self::duplicateEmail( $email ); 

            if ( $validation_result !== true )
                Response::return( false, $validation_result );

        //hash password
            $passwordHash = password_hash( $password, PASSWORD_DEFAULT );

        //setup query
            $sql_query = "INSERT INTO " . SQL::$table_users . " 
                                 ( name, email, password, role )
                          VALUES ( :name, :email, :password, :role )";

            $parameters = [
                ':name' => $name, 
                ':email' => $email,
                ':password' => $passwordHash,
                ':role' => $role
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );
        
        Response::return( $success, $message );      
    }  
    
    private static function validateCreate( $email, $password, $name, $role )
    {
        //general validation
            $fields = [
                'email' => [ $email, User_settings::$email_maxlength ],
                'password' => [ $password ],
                'name' => [ $name, User_settings::$name_max_length ],
                'role' => [ $role, self::$no_length_validation ],
            ];
            
            $general_validation_result = Validation::loopGeneral( $fields );
            
            if ( $general_validation_result !== true )
                return $general_validation_result;
            
        //specific validation
            //email
            if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) )
                return 'Email: The email address is not valid.';
            
            //role
            if ( !in_array( $role, User_settings::$roles ) )
                return 'Role: The role should be one of the following: ' . implode(", ", User_settings::$roles ) . '.';
            
        return true;
    }

//update
    static function update()
    {
        //test if all expected fields where given
            $expected_input = [ 'id', 'email', 'name' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );    

        //get values from body
            $id = $_POST['id'];
            $email = $_POST['email'];
            $name = $_POST['name'];

        //validation
            $validation_result = self::validateUpdate( $id, $email, $name );

            if ( $validation_result !== true )
                Response::return( false, $validation_result );    
           
            $validation_result = self::duplicateEmail( $email, $id );

            if ( $validation_result !== true )
                Response::return( false, $validation_result );

        //setup query
            $sql_query = "UPDATE " . SQL::$table_users . " 
                          SET name = :name, email = :email
                          WHERE id = :id";

            $parameters = [
                ':id' => $id, 
                ':name' => $name,
                ':email' => $email,
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );

        //jwt with new user info
            $user_info = [
                'name' => $name, 
                'email' => $email,
            ];

            $jwt_result = TokenAuthentication::generateJwt( $user_info, Response::$jwt );

            if ( $jwt_result == false )
                Response::return( false, 'Error occurred whilst generating the token.' );    
            
        //set jwt in response class
            Response::$jwt = $jwt_result;
     
        Response::return( $success, $message );
    } 

    private static function validateUpdate( $id, $email, $name )
    {
        //general validation
            //give variables names for length
            $fields = [
                'id' =>  [ $id, self::$no_length_validation ],
                'email' => [ $email, User_settings::$email_maxlength ],
                'name' => [ $name, User_settings::$name_max_length ],
            ];

            $general_validation_result = Validation::loopGeneral( $fields );

            if ( $general_validation_result !== true )
                return $general_validation_result;

        //specific validation
            //email
            if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) )
                return 'Email: The email address is not valid.';

        return true;
    }

//login
    static function login()
    {
        //test if all expected fields where given
            $expected_input = [ 'email', 'password' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );    
       
        //get values from body
            $email = $_POST['email'];
            $password = $_POST['password'];

        //validation
            $validation_result = self::validateLogin( $email, $password );

            if ( $validation_result !== true )
                Response::return( false, $validation_result );  

        //setup query
            $sql_query = "SELECT password, id, name, role
                          FROM ( " . SQL::$table_users . " )
                          WHERE ( email = :email )";

            $parameters = [
                ':email' => $email,
            ];
           
        //execute query
            list( $success, $user_result ) = Database::executeSQL( $sql_query, $parameters );

            if ( !$success )
                Response::return( false, $user_result );
                
        //if email was not found
            if ( empty( $user_result ) )
                Response::return( false, 'The credentials entered were not valid. Check your email and password and try again.' );    

            $user_db = array_shift( $user_result );
        
        //compare password
            if ( !password_verify( $password, $user_db['password'] ) )
                Response::return( false, 'The credentials entered were not valid. Check your email and password and try again.' );
        
        //if up to this point -> credentials are valid
            //compile user info
            $id = $user_db['id'];

            $user_info = [
                'id' => $id,
                'email' => $email,
                'name' => $user_db['name'],
                'role' => $user_db['role'],
            ];

            $jwt_result = TokenAuthentication::generateJwt( user_info: $user_info );

            //check if error occured
            if ( $jwt_result == false )
                Response::return( false, 'Error occurred whilst generating the token.' ); 
            
            //set jwt in response class
            Response::$jwt = $jwt_result;

        //update lastLogin field
            $update_result = self::updateLoginTimeStamp( $id );
        
            $extra_message = '';
            if ( $update_result !== true )
                $extra_message = " Could not set user login timestamp:  $update_result";

        //return TokenAuthentication response
            Response::return( true, "User logged in successfully.$extra_message" );
    }

    private static function validateLogin( $email, $password )
    {
        //general validation
            $fields = [
                'email' => [ $email, User_settings::$email_maxlength ],
                'password' => [ $password ],
            ];
           
            $general_validation_result = Validation::loopGeneral( $fields );
            
            if ( $general_validation_result !== true )
                return $general_validation_result;
                
        //specific validation
            //email
            if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) )
                return 'Email: The email address is not valid.';

        return true;
    }
 
    private static function updateLoginTimeStamp( $id )
    {
        //setup query
            $sql_query = "UPDATE " . SQL::$table_users . " 
                          SET lastLogin = CURRENT_TIMESTAMP
                          WHERE ( id = :id )";

            $parameters = [
                ':id' => $id
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );

            if ( $success !== true )
                return $message;
        
        return true;
    }

//logout
    static function logout()
    {
        //test if expected field was given
            $expected_input = [ 'id' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );

        //get user id
            $id = $_POST['id'];

        //prepare query
            $sql_query = "UPDATE " . SQL::$table_users . " 
                          SET jwt = ''
                          WHERE ( id = :id )";

            $parameters = [
                ':id' => $id,
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );

            if ( !$success )
                Response::return( false, $message );

        Response::return( $success, 'User was successfully logged out.' );        
    }

//multi-purpose  
    //check if a product already exists in the database
    private static function duplicateEmail( $email, $excluded_id = "" )
    {
        //if a user needs to be excluded from duplicate check -> used primarily during UPDATE
        if ( $excluded_id != "" )
        {            
            $sql_query = "SELECT COUNT(*) as total
                          FROM ( " . SQL::$table_users . " )
                          WHERE ( email = :email ) AND ( id != :id )";

            $parameters = [
                ':email' => $email,
                ':id' => $excluded_id,
            ];
                          
            list( $success, $query_result ) = Database::executeSQL( $sql_query, $parameters );
        }
        else               
        {                             
            $sql_query = "SELECT COUNT(*) as total      
                          FROM ( " . SQL::$table_users . " )
                          WHERE ( email = :email )";

            $parameters = [
                ':email' => $email,
            ];
            
            list( $success, $query_result ) = Database::executeSQL( $sql_query, $parameters );
        }

        //if error occurred
            if ( !$success )
                return $query_result;

        //access 'COUNT(*) as total' from query
            $count = array_shift( $query_result );

            if ( $count['total'] !== 0 )
                return 'Email: The email address, ' . $email . ', already exists.';

        return true;
    }
}

/*  
    Methods not in use:
        +read()    

        +delete()

----------------------------------------------------------------------------------------------------------
    Method declarations
----------------------------------------------------------------------------------------------------------

    static function read()
    {
        //prepare query
            $sql_query = "SELECT id, name, email, role 
                          FROM ( " . SQL::$table_users . " )";
    
        //execute query
            list( $success, $query_result ) = Database::executeSQL( $sql_query );
        
        if ( !$success )
            Response::return( false, $query_result );
    
        Response::return( $success, 'Users successfully retrieved from database.', $query_result );
    }    

    static function delete() //insert validation
    {        
        //test if expected field was given
            $expected_input = [ 'id' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );    

        //insert validation for id?
            
        //get user id
            $id = $_POST['id'];

        //prepare query
            $sql_query = "DELETE FROM " . SQL::$table_users . " 
                          WHERE ( id = :id )";

            $parameters = [
                ':id' => $id,
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );

            if ( !$success )
                Response::return( false, $message );
    
        Response::return( $success, 'User was successfully removed from the database.' );
    } 

----------------------------------------------------------------------------------------------------------
    End
----------------------------------------------------------------------------------------------------------  
*/