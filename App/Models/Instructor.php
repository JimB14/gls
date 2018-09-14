<?php

namespace App\Models;

use PDO;

/**
 * Instructor model
 */
class Instructor extends \Core\Model
{
    public static function addInstructor($instructor)
    {
        // add to database
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO instructors SET
                    email      = :email,
                    first_name = :first_name,
                    last_name  = :last_name,
                    state      = :state,
                    experience = :experience";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email'      => $instructor['email'],
                ':first_name' => $instructor['first_name'],
                ':last_name'  => $instructor['last_name'],
                ':state'      => $instructor['state'],
                ':experience' => $instructor['experience']
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
}
