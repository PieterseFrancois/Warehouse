<?php

class SQL
{
    //credentials to connect to the MySQL database
    static $credentials = array( 
        'host' => "localhost",
        'username' => "root",
        'password' => "",
        'port' => "",
        'database' => "warehouse_db",
    );

    static $table_users = 'users';
    static $table_products = 'products';
}

class Product_settings
{
    static $categories = [ 'Food', 'Clothes', 'Medicine', 'Household' ];

    static $min_quantity = 1;
    static $max_quantity = 100;

    static $name_max_length = 64;
}

class User_settings
{
    static $roles = [ 'cashier', 'admin' ];

    static $email_maxlength = 128;
    static $name_max_length = 50;
}

class URL
{
    //number of parameters allowed in the URL
    static $parameter_count = 2;
}

class JWT
{
    static $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];

    static $session_time_constant_seconds = 900; //15 minutes

    static $private_key = '%C*F-JaNdRfUjXn2r5u8x/A?D(G+KbPe';   //256bit key

    static $exemption_list = [
        'users::create',
        'users::login',
        'users::logout',
    ];

    //working with assumption that all other controllers are cashier protected if not exempt above
    static $admin_protected_list = [
        'products::delete',
    ];
}