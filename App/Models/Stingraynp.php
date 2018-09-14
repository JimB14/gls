<?php

namespace App\Models;

use PDO;


/**
 * Stingraynp Model
 *
 * PHP version 7.0
 */
class Stingraynp extends \Core\Model
{

    /**
     * retrieves all Stingray NP lasers
     *
     * @return object  All lasers in `stingrays` table
     */
    public static function getLasers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql =  "SELECT DISTINCT
                        stingraynp.id, stingraynp.mvc_model, stingraynp.name, stingraynp.stingrayid,
                        stingraynp.stingrayid, stingraynp.price, stingraynp.price_dealer,
                        stingraynp.price_partner,
                        stingrays.series, stingrays.model, stingrays.beam,
                        stingray_images.thumb as img_thumb,
                        pistol_brands.name as pistolMfr,
                        pistols.slug,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                    FROM stingraynp
                    INNER JOIN stingrays
                        ON stingrays.id = stingraynp.stingrayid
                    INNER JOIN pistol_stingray_lookup
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN pistols
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN stingray_images
                    	ON stingrays.id = stingraynp.stingrayid
                    GROUP BY stingrays.model
                    ORDER BY stingrays.id";
            $stmt = $db->query($sql);
            $models = $stmt->fetchALL(PDO::FETCH_OBJ);

            return $models;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves laser field items for Dealer shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getStingraynpDetailsForDealerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        stingraynp.id, stingraynp.mvc_model, stingraynp.name,
                        stingraynp.stingrayid, stingraynp.price, stingraynp.price_dealer,
                        stingraynp.price_partner,
                        stingrays.series, stingrays.model,
                        stingrays.beam, stingray_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM stingraynp
                    INNER JOIN stingrays
                    	ON stingrays.id = stingraynp.stingrayid
                    INNER JOIN pistol_stingray_lookup
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN pistols
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN stingray_images
                    	ON stingray_images.stingray_id = stingrays.id
                    WHERE stingraynp.id = :id
                    GROUP BY stingrays.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $item = $stmt->fetch(PDO::FETCH_OBJ);

            return $item;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
