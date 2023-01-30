<?php

/*
    Methods in use:
        +create()
        -validateCreate( $name, $category, $quantity, $user_id )

        +read()

        +update()
        -validateUpdate()
        
        +delete()
    
        -duplicateEmail( $name, $excluded_id = "" )
*/

class Products
{
//class variable(s)
    private static $no_length_validation = 0;

//create
    static function create()
    {
        //test if all expected fields where given
            $expected_input = [ 'name', 'category', 'quantity', 'user_id' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );    

        //get values from body
            $name = $_POST['name'];
            $category = $_POST['category'];
            $quantity = $_POST['quantity'];
            $user_id = $_POST['user_id'];

        //validation
            $validation_result = self::validateCreate( $name, $category, $quantity, $user_id );

            if ( $validation_result !== true )
                Response::return( false, $validation_result );    
           
            $validation_result = self::duplicateProduct( $name ); 

            if ( $validation_result !== true )
                Response::return( false, $validation_result );

        //setup query
            $sql_query = "INSERT INTO " . SQL::$table_products . " 
                                 ( name, category, quantity, createdBy )
                          VALUES ( :name, :category, :quantity, :user_id )";

            $parameters = [
                ':name' => $name, 
                ':category' => $category,
                ':quantity' => $quantity,
                ':user_id' => $user_id,
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );

        Response::return( $success, $message );      
    }  
    
    private static function validateCreate( $name, $category, $quantity, $user_id )
    {
        //general validation
            $fields = [
                'name' => [ $name, Product_settings::$name_max_length ],
                'category' => [ $category, self::$no_length_validation ],
                'quantity' => [ $quantity, self::$no_length_validation ],
                'user_id' => [ $user_id, self::$no_length_validation ],
            ];
            
            $general_validation_result = Validation::loopGeneral( $fields );
            
            if ( $general_validation_result !== true )
                return $general_validation_result;
            
        //specific validation
        
            //category
            if ( !in_array( $category, Product_settings::$categories ) )
                return 'Category: The product category should be one of the following: ' . implode(", ", Product_settings::$categories ) . '.';
        
            //quantity
            if ( $quantity < Product_settings::$min_quantity || $quantity > Product_settings::$max_quantity )
                return 'Quantity: The quantity should be between ' . Product_settings::$min_quantity . ' and ' . Product_settings::$max_quantity . '.';

        return true;
    }

//read
    static function read()
    {
        //prepare query
            $sql_query = "SELECT id, category, name, quantity 
                          FROM ( " . SQL::$table_products . " )";
    
        //execute query
            list( $success, $products_result ) = Database::executeSQL( $sql_query );
        
        if ( !$success )
            Response::return( false, $products_result );
    
        Response::return( $success, 'Products successfully retrieved from database.', $products_result );
    }

//delete
    static function delete() //insert validation?
    {        
        //test if expected field was given
            $expected_input = [ 'id' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );    

        //insert validation of id field?
            
        //get product id
            $id = $_POST['id'];

        //prepare query
            $sql_query = "DELETE FROM " . SQL::$table_products . " 
                          WHERE ( id = :id )";

            $parameters = [
                ':id' => $id,
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );

            if ( !$success )
                Response::return( false, $message );
    
        Response::return( $success, 'Product was successfully removed from the database.' );
    } 

//multi-purpose
    //check if a product already exists in the database
    private static function duplicateProduct( $name, $excluded_id = "" )
    {
        //if a product needs to be excluded from duplicate check -> used primarily during UPDATE
        if ( $excluded_id != "" )
        {            
            $sql_query = "SELECT COUNT(*) as total
                          FROM ( " . SQL::$table_products . " )
                          WHERE ( name = :name ) AND ( id != :id )";

            $parameters = [
                ':name' => $name,
                ':id' => $excluded_id,
            ];
                          
            list( $success, $query_result ) = Database::executeSQL( $sql_query, $parameters );
        }
        else               
        {                             
            $sql_query = "SELECT COUNT(*) as total      
                          FROM ( " . SQL::$table_products . " )
                          WHERE ( name = :name )";

            $parameters = [
                ':name' => $name,
            ];
            
            list( $success, $query_result ) = Database::executeSQL( $sql_query, $parameters );
        }

        //if error occured
            if ( !$success )
                return $query_result;

        //access 'COUNT(*) as total' from query
            $count = array_shift( $query_result );

            if ( $count['total'] !== 0 )
                return "Name: A product with the name, $name, already exists.";

        return true;
    }

}

/*  
    Methods not in use:
        +update()
        -validateUpdate( $name, $category, $quantity )
        -validateCombinedQuantity( $name, $quantity )

----------------------------------------------------------------------------------------------------------
    Method declarations
----------------------------------------------------------------------------------------------------------

    static function update()
    {
        //test if all expected fields where given
            $expected_input = [ 'name', 'category', 'quantity', 'id' ];
            $validation_result = Validation::isSet( $expected_input );

            if ( $validation_result !== true )
                Response::return( false,  $validation_result );    

        //get values from body
            $name = $_POST['name'];
            $category = $_POST['category'];
            $quantity = $_POST['quantity'];
            $id = $_POST['id'];

        //validation
            $validation_result = self::validateUpdate( $name, $category, $quantity );

            if ( $result !== true )
                Response::return( false, $validation_result );    
           
        //    $validation_result = self::duplicateEmail( $email, $id ); //replace with error code 

            if ( $validation_result !== true )
                Response::return( false, $validation_result );

        //setup query
            $sql_query = "UPDATE " . SQL::$table_products . " 
                          SET name = :name, category = :category, quantity = :quantity
                          WHERE id = :id";

            $parameters = [
                ':id' => $id, 
                ':name' => $name,
                ':category' => $category,
                ':quantity' => $quantity,
            ];

        //execute query
            list( $success, $message ) = Database::executeSQL( $sql_query, $parameters );
        
        Response::return( $success, $message );
    } 

    private static function validateUpdate( $name, $category, $quantity )
    {
        //general validation
            $fields = [
                'name' => [ $name, Products::$name_max_length ],
                'category' => [ $category, self::$no_length_validation ],
                'quantity' => [ $quantity, self::$no_length_validation ],
            ];
            
            $general_validation_result = Validation::loopGeneral( $fields );
            
            if ( $general_validation_result !== true )
                return $general_validation_result;
            
        //specific validation
        
            //category
            if ( !in_array( $category, Products::$categories ) )
                return 'Category: The product category should be one of the following: ' . implode(", ", Products::$categories ) . '.';
        
            //quantity
            if ( $quantity < Products::$min_quantity || $quantity > Products::$max_quantity )
                return 'Quantity: The quantity should be between ' . Products::$min_quantity . ' and ' . Products::$max_quantity . '.';

            //combined quantity
                $quantity_validation_result = self::validateCombinedQuantity( $name, $quantity );

                if ( $quantity_validation_result !== true )
                    return $quantity_validation_result;

        return true;
    }

    private static function validateCombinedQuantity( $name, $quantity )
    {
        //setup query
            $sql_query = "SELECT quantity      
                          FROM ( " . SQL::$table_products . " )
                          WHERE ( name = :name )";

            $parameters = [
                ':name' => $name,
            ];

        //execute query
            list( $success, $quantity_result ) = Database::executeSQL( $sql_query, $parameters );
              
            if ( !$success )
                return $quantity_result;

        //combined quantity
            $combined_quantity = $quantity_result + $quantity;

            if ( $combined_quantity > Products::$max_quantity )
                return 'This action will result in the quantity exceeding the maximum of ' . Products::$max_quantity . ' units.';

        return true;   
    }

----------------------------------------------------------------------------------------------------------
    End
----------------------------------------------------------------------------------------------------------  
*/