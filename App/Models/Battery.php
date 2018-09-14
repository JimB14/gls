<?php

namespace App\Models;

use PDO;


class Battery extends \Core\Model
{

    public static function getBattery($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM batteries
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $battery = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $battery;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getBatteries()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM batteries";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $batteries = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $batteries;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }









//  = = = = = refactored updates above  = = = = = = = = = = = = = = = = = = =








}
