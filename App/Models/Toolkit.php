<?php

namespace App\Models;

use PDO;


class Toolkit extends \Core\Model
{

    public static function getToolkit($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM toolkits
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $toolkit = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $toolkit;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getToolkits()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM toolkits";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $toolkits = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $toolkits;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


//  = = = = = refactored updates above  = = = = = = = = = = = = = = = = = = =

    
}
