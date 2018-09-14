<?php

namespace App\Models;

use PDO;

/**
 * Pistol Model
 *
 * PHP version 7.0
 */
class Pistol extends \Core\Model
{
    public static function getModelDetails($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM pistols
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $model = $stmt->fetch(PDO::FETCH_OBJ);

            return $model;
        }
        catch(PDOException $e)
        {
            echo  $e->getMessage();
            exit();
        }
    }




    public static function getIdByMfrModel($mfr, $model)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT id as pistolid, brand_id, brand_name
                    FROM pistols
                    WHERE brand_name = :brand_name
                    AND model = :model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':brand_name' => $mfr,
                ':model'      => $model
            ];
            $stmt->execute($parameters);
            $results = $stmt->fetch(PDO::FETCH_OBJ);

            return $results;
        }
        catch(PDOException $e)
        {
            echo  $e->getMessage();
            exit();
        }
    }
}
