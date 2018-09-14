<?php

namespace App\Models;

use PDO;


class Pistolbrand extends \Core\Model
{
    public static function getLogos()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM pistol_brands_images
                    INNER JOIN pistol_brands ON
                    pistol_brands.id = pistol_brands_images.pistol_brand_id
                    ORDER BY pistol_brand_id";
            $stmt = $db->query($sql);
            $logos = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $logos;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




public static function getBrandId($name)
{
    try
    {
        // establish db connection
        $db = static::getDB();

        $sql = "SELECT * FROM pistol_brands
                WHERE name = :name";
        $stmt = $db->prepare($sql);
        $parameters = [
            ':name' => $name
        ];
        $stmt->execute($parameters);
        $results = $stmt->fetch(PDO::FETCH_OBJ);

        // store ID in variable
        $id = $results->id;

        return $id;
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
        exit();
    }
}


/**
 * retrieve pistol brand names
 * @return Object  the brand data
 */
public static function getBrands()
{
    try
    {
        // establish db connection
        $db = static::getDB();

        $sql = "SELECT * FROM pistol_brands
                ORDER BY name";
        $stmt = $db->query($sql);
        $brands = $stmt->fetchAll(PDO::FETCH_OBJ);

        // return to Flxs Controller
        return $brands;
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
        exit();
    }
}



/**
 * retrieves brand name by the brand ID
 * @param  INT      $id     the brand ID
 * @return String           the brand name
 */
public static function getBrandName($id)
{
    try
    {
        // establish db connection
        $db = static::getDB();

        $sql = "SELECT name
                FROM pistol_brands
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $parameters = [
            ':id' => $id
        ];
        $stmt->execute($parameters);
        $results = $stmt->fetch(PDO::FETCH_OBJ);

        // store brand name in variable
        $name = $results->name;

        return $name;
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
        exit();
    }
}

// = = = = = = refactored updates above = = = = = = = = = = = = = = = = = = = //









}
