<?php

namespace App\Models;

use PDO;


/**
 * UserPending Model
 *
 * PHP version 7.0
 */
class Partnerpending extends \Core\Model
{

    /**
     * adds new user to partners_pending table
     *
     * @param string $token   Unique string
     *
     * @param integer $user_id  The partner's ID
     */
    public static function addUserToPartnersPending($token, $partnerid)
    {
        // establish db connection
        $db = static::getDB();

        try
        {
            $sql = "INSERT INTO partners_pending set
                    token = :token,
                    partnerid = :partnerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':token'     => $token,
                ':partnerid' => $partnerid
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
     * verifies partner account from email link
     *
     * @param  string   $token       Unique string
     * @param  integer  $user_id     The user's ID
     *
     * @return boolean
     */
    public static function verifyNewPartnerAccount($token, $id)
    {
        // test
        // echo "Connected to verifyNewPartnerAccount in Partnerpending model<br><br>";
        // echo $token . "<br>";
        // echo $id . "<br>";
        // exit();

        // check for match - token/id from query string with token/id db fields
        try {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM partners_pending
                    WHERE token = :token
                    AND partnerid = :partnerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':token' => $token,
                ':partnerid' => $id
            ];
            $stmt->execute($parameters);

            // store partner data in object
            $partner = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo "<pre>";
            // print_r($partner);
            // echo "</pre>";
            // exit();

            if(empty($partner))
            {
                echo "Unable to find match. Please re-register.";
                exit();
            }
            else
            {
                // activate partner account
                try
                {
                    $sql = "UPDATE partners SET
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

                // delete pending partner from table to disable verify email link
                try
                {
                    $sql = "DELETE FROM partners_pending
                            WHERE token = :token";
                    $stmt =  $db->prepare($sql);
                    $parameters = [
                        ':token' => $token
                    ];
                    $stmt->execute($parameters);
                }
                catch(PDOException $e)
                {
                    echo "Error deleting users pending record";
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
