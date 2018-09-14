<?php

namespace App\Models;

use PDO;


/**
 * UserPending Model
 *
 * PHP version 7.0
 */
class Customerpending extends \Core\Model
{

    /**
     * adds new customer to customers_pending table
     *
     * @param string $token   Unique string
     *
     * @param integer $user_id  The partner's ID
     */
    public static function addToCustomersPending($token, $customerid)
    {
        // establish db connection
        $db = static::getDB();

        try
        {
            $sql = "INSERT INTO customers_pending set
                    token = :token,
                    customerid = :customerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':token'     => $token,
                ':customerid' => $customerid
            ];
            $result = $stmt->execute($parameters);

            // return to controller
            return $result;
        }
        catch(PDOException $e)
        {
           echo $e->getMessage();
           exit();
        }
    }


    /**
     * verifies customer account from email link
     *
     * @param  string   $token       Unique string
     * @param  integer  $id         The customer's ID `customer`.`id`
     *
     * @return boolean
     */
    public static function verifyNewCustomerAccount($token, $id)
    {
        // test
        // echo "Connected to verifyNewCustomerAccount in Customerpending model<br><br>";
        // echo $token . "<br>";
        // echo $id . "<br>";
        // exit();

        // check for match - token/id from query string with token/id db fields
        try {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM customers_pending
                    WHERE token = :token
                    AND customerid = :customerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':token' => $token,
                ':customerid' => $id
            ];
            $stmt->execute($parameters);

            // store dealer data in object
            $customer = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo "<pre>";
            // print_r($customer);
            // echo "</pre>";
            // exit();

            if(empty($customer))
            {
                echo "Unable to find match. Please re-register.";
                exit();
            }
            else
            {
                // activate customer account
                try
                {
                    $sql = "UPDATE customers SET
                            active = 1
                            WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $parameters = [
                        ':id' => $id
                    ];
                    $stmt->execute($parameters);
                }
                catch (PDOException $e)
                {
                    echo "Error adding new user.";
                    exit();
                }

                // delete pending customer from table to disable verify email link
                try
                {
                    $sql = "DELETE FROM customers_pending
                            WHERE token = :token";
                    $stmt =  $db->prepare($sql);
                    $parameters = [
                        ':token' => $token
                    ];
                    $stmt->execute($parameters);
                }
                catch(PDOException $e)
                {
                    echo "Error deleting customers pending record";
                    exit();
                }
            }
            // return true to Register controller
            return true;
        }
        catch(PDOException $e)
        {
            echo "Error retrieving data from users pending";
            exit();
        }
    }
}
