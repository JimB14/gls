<?php

namespace App\Models;

use PDO;


/**
 * UserPending Model
 *
 * PHP version 7.0
 */
class Dealerpending extends \Core\Model
{

    /**
     * adds new user to partners_pending table
     *
     * @param string $token   Unique string
     *
     * @param integer $user_id  The partner's ID
     */
    public static function addUserToDealersPending($token, $dealerid)
    {
        // establish db connection
        $db = static::getDB();

        try
        {
            $sql = "INSERT INTO dealers_pending set
                    token = :token,
                    dealerid = :dealerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':token'     => $token,
                ':dealerid' => $dealerid
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
     * verifies dealer account from email link
     *
     * @param  string   $token       Unique string
     * @param  integer  $user_id     The user's ID
     *
     * @return boolean
     */
    public static function verifyNewDealerAccount($token, $id)
    {
        // test
        // echo "Connected to verifyNewDealerAccount in Dealerpending model<br><br>";
        // echo $token . "<br>";
        // echo $id . "<br>";
        // exit();

        // check for match - token/id from query string with token/id db fields
        try {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT *
                    FROM dealers_pending
                    WHERE token = :token
                    AND dealerid = :dealerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':token' => $token,
                ':dealerid' => $id
            ];
            $stmt->execute($parameters);

            // store dealer data in object
            $dealer = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo "<pre>";
            // print_r($dealer);
            // echo "</pre>";
            // exit();

            if(empty($dealer))
            {
                echo "Unable to find match. Please re-register.";
                exit();
            }
            else
            {
                // activate dealer account
                try
                {
                    $sql = "UPDATE dealers SET
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
                    echo "Error adding new dealer.";
                    exit();
                }

                // delete pending dealer from table to disable verify email link
                try
                {
                    $sql = "DELETE FROM dealers_pending
                            WHERE token = :token";
                    $stmt =  $db->prepare($sql);
                    $parameters = [
                        ':token' => $token
                    ];
                    $stmt->execute($parameters);
                }
                catch(PDOException $e)
                {
                    echo "Error deleting delers pending record";
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
