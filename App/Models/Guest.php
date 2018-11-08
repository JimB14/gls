<?php

namespace App\Models;

use PDO;
use \Core\View;


class Guest extends \Core\Model
{
    /**
     * check if user's email exists in `guests` table
     * @param  String   $email      The user's email address
     * @return Object               Object with or without content
     */
    public static function doesGuestExist($email)
    {
        // echo "Connected to doesGuestExist()"; exit();

        // check if email is in `guests` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM guests
                    WHERE email = :email";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);
            $guest = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $guest;
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * updates guest's billing and shipping info in `guests` table
     *
     * @param  Int      $id              `guests`.`id`
     * @param  Array    $billing_data    Data from form
     * @param  Array    $shipping_data   Data from form
     * @return Boolean                   Success or failure
     */
    public static function updateBillingShippingInfo($id, $billing_data, $shipping_data)
    {
        // echo "Connected!";

        // test
            // echo '<pre>';
            // echo '<h4>Billing data:</h4>';
            // print_r($billing_data);
            // echo '</pre>';
            // // echo '<pre>';
            // // echo '<h4>Shipping data:</h4>';
            // print_r($shipping_data);
            // echo '</pre>';
            // exit();

        try
        {
            $db = static::getDB();

            $sql = "UPDATE guests SET
                    billing_firstname     = :billing_firstname,
                    billing_lastname      = :billing_lastname,
                    billing_company       = :billing_company,
                    billing_phone         = :billing_phone,
                    billing_address       = :billing_address,
                    billing_address2      = :billing_address2,
                    billing_city          = :billing_city,
                    billing_state         = :billing_state,
                    billing_zip           = :billing_zip,
                    shipping_firstname    = :shipping_firstname,
                    shipping_lastname     = :shipping_lastname,
                    shipping_company      = :shipping_company,
                    shipping_phone        = :shipping_phone,
                    shipping_address      = :shipping_address,
                    shipping_address2     = :shipping_address2,
                    shipping_city         = :shipping_city,
                    shipping_state        = :shipping_state,
                    shipping_zip          = :shipping_zip,
                    addresstype           = :addresstype,
                    shipping_instructions = :shipping_instructions
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'                    => $id,
                ':billing_firstname'     => $billing_data['billing_firstname'],
                ':billing_lastname'      => $billing_data['billing_lastname'],
                ':billing_company'       => $billing_data['billing_company'],
                ':billing_phone'         => $billing_data['billing_phone'],
                ':billing_address'       => $billing_data['billing_address'],
                ':billing_address2'      => $billing_data['billing_address2'],
                ':billing_city'          => $billing_data['billing_city'],
                ':billing_state'         => $billing_data['billing_state'],
                ':billing_zip'           => $billing_data['billing_zip'],
                ':shipping_firstname'    => $shipping_data['shipping_firstname'],
                ':shipping_lastname'     => $shipping_data['shipping_lastname'],
                ':shipping_company'      => $shipping_data['shipping_company'],
                ':shipping_phone'        => $shipping_data['shipping_phone'],
                ':shipping_address'      => $shipping_data['shipping_address'],
                ':shipping_address2'     => $shipping_data['shipping_address2'],
                ':shipping_city'         => $shipping_data['shipping_city'],
                ':shipping_state'        => $shipping_data['shipping_state'],
                ':shipping_zip'          => $shipping_data['shipping_zip'],
                ':addresstype'           => $shipping_data['addresstype'],
                ':shipping_instructions' => $shipping_data['shipping_instructions']
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * adds guest to `guests` table
     *
     * @param  Int      $email           The guest's email
     * @param  Array    $billing_data    Data from form
     * @param  Array    $shipping_data   Data from form
     * @return Array                     Result and new ID
     */
    public static function addGuest($email, $billing_data, $shipping_data)
    {
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // test
            // echo '<h4>Billing data: </h4>';
            // echo '<pre>';
            // print_r($billing_data);
            // echo '</pre>';
            // echo '<h4>Shipping data: </h4>';
            // echo '<pre>';
            // print_r($shipping_data);
            // echo '</pre>';
            // exit();

        try
        {
            $db = static::getDB();

            $sql = "INSERT INTO guests SET
                    billing_firstname     = :billing_firstname,
                    billing_lastname      = :billing_lastname,
                    billing_company       = :billing_company,
                    billing_phone         = :billing_phone,
                    billing_address       = :billing_address,
                    billing_address2      = :billing_address2,
                    billing_city          = :billing_city,
                    billing_state         = :billing_state,
                    billing_zip           = :billing_zip,
                    email                 = :email,
                    ip                    = :ip,
                    shipping_firstname    = :shipping_firstname,
                    shipping_lastname     = :shipping_lastname,
                    shipping_company      = :shipping_company,
                    shipping_phone        = :shipping_phone,
                    shipping_address      = :shipping_address,
                    shipping_address2     = :shipping_address2,
                    shipping_city         = :shipping_city,
                    shipping_zip          = :shipping_zip,
                    addresstype           = :addresstype,
                    shipping_state        = :shipping_state,
                    shipping_instructions = :shipping_instructions";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email'                 => $email,
                ':ip'                    => $user_ip,
                ':billing_firstname'     => $billing_data['billing_firstname'],
                ':billing_lastname'      => $billing_data['billing_lastname'],
                ':billing_company'       => $billing_data['billing_company'],
                ':billing_phone'         => $billing_data['billing_phone'],
                ':billing_address'       => $billing_data['billing_address'],
                ':billing_address2'      => $billing_data['billing_address2'],
                ':billing_city'          => $billing_data['billing_city'],
                ':billing_state'         => $billing_data['billing_state'],
                ':billing_zip'           => $billing_data['billing_zip'],
                ':shipping_firstname'    => $shipping_data['shipping_firstname'],
                ':shipping_lastname'     => $shipping_data['shipping_lastname'],
                ':shipping_company'      => $shipping_data['shipping_company'],
                ':shipping_phone'        => $shipping_data['shipping_phone'],
                ':shipping_address'      => $shipping_data['shipping_address'],
                ':shipping_address2'     => $shipping_data['shipping_address2'],
                ':shipping_city'         => $shipping_data['shipping_city'],
                ':shipping_state'        => $shipping_data['shipping_state'],
                ':shipping_zip'          => $shipping_data['shipping_zip'],
                ':addresstype'           => $shipping_data['addresstype'],
                ':shipping_instructions' => $shipping_data['shipping_instructions']
            ];
            $result = $stmt->execute($parameters);

            // store new ID
            $id = $db->lastInsertId();

            // store result & new guest ID in array, & return to controller
            $results = [
                'result' => $result,
                'id'     => $id
            ];

            return $results;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    /**
     * get guest record by ID
     *
     * @param  Int   $id   the ID of the record
     * @return Object     the guest record
     */
    public static function getGuest($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM guests
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $guest = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $guest;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getGuestByEmail($email)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM guests
                    WHERE email = :email";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);

            $guest = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $guest;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


}
