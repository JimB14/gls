<?php

namespace App\Models;

use PDO;


class Product extends \Core\Model
{

    public static function get()
    {
        // echo "<br>Connected to get() in Product model!";
        
        try
        {
            $db = static::getDB();

            $sql = "SELECT id, mvc_model, name, price FROM trseries
                    UNION ALL
                    SELECT id, mvc_model, name, price FROM accessories
                    UNION ALL
                    SELECT id, mvc_model, name, price FROM batteries
                    UNION ALL
                    SELECT id, mvc_model, name, price FROM flx
                    UNION ALL
                    SELECT id, mvc_model, name, price FROM gtoflx
                    UNION ALL
                    SELECT id, mvc_model, name, price FROM holsters
                    UNION ALL
                    SELECT id, mvc_model, name, price FROM stingrays
                    UNION ALL
                    SELECT id, mvc_model, name, price FROM toolkits
                    LIMIT 25";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $products;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function find($searchword) 
    {
        // explode into array
        $wordArr = explode(' ', $searchword);

        // implode into comma separated string
        $wordStr = implode(',', $wordArr);
        
        try
        {
            $db = static::getDB();

            $sql = "SELECT id, mvc_model, name, price
                        FROM trseries
                        -- WHERE trseries.name IN ('$wordStr')
                        WHERE name LIKE '%$searchword%'
                        UNION
                    SELECT id, mvc_model, name, price 
                        FROM gtoflx
                        WHERE name LIKE '%$searchword%'
                        UNION    
                    SELECT id, mvc_model, name, price 
                        FROM accessories
                        WHERE name LIKE '%$searchword%'
                     UNION
                    SELECT id, mvc_model, name, price 
                        FROM batteries
                        WHERE name LIKE '%$searchword%'
                        UNION
                    SELECT id, mvc_model, name, price 
                        FROM flx
                        WHERE name LIKE '%$searchword%'
                        UNION                   
                    SELECT id, mvc_model, name, price 
                        FROM holsters
                        WHERE name LIKE '%$searchword%'
                        UNION
                    SELECT id, mvc_model, name, price 
                        FROM stingrays
                        WHERE name LIKE '%$searchword%'
                        UNION
                    SELECT id, mvc_model, name, price 
                        FROM toolkits
                        WHERE name LIKE '%$searchword%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $products;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     *  Retrieves products by IDs
     *  @params $itemStr    Array   The IDs
     *  @return Object              The records
     */
    public static function getProductsById($itemStr) 
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT id, name, price
                        FROM trseries
                        WHERE id IN ($itemStr)
                        UNION
                    SELECT id, name, price 
                        FROM gtoflx
                        WHERE id IN ($itemStr)
                        UNION    
                    SELECT id, name, price 
                        FROM accessories
                        WHERE id IN ($itemStr)
                        UNION
                    SELECT id, name, price 
                        FROM batteries
                        WHERE id IN ($itemStr)
                        UNION
                    SELECT id, name, price 
                        FROM flx
                        WHERE id IN ($itemStr)
                        UNION                   
                    SELECT id, name, price 
                        FROM holsters
                        WHERE id IN ($itemStr)
                        UNION
                    SELECT id, name, price 
                        FROM stingrays
                        WHERE id IN ($itemStr)
                        UNION
                    SELECT id, name, price 
                        FROM toolkits
                        WHERE id IN ($itemStr)";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $products;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }
    



    


}
