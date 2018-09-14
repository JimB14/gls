<?php

namespace App\Models;

use PDO;


/**
 * Gtonp Model
 *
 * PHP version 7.0
 */
class Gtonp extends \Core\Model
{

    /**
     * retrieves all GTO & GTO NP lasers
     *
     * @return object  All lasers in gtoflx table
     */
    public static function getLasers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql =  "SELECT DISTINCT
                        gtonp.id, gtonp.mvc_model, gtonp.name,
                        gtonp.gtoflxid, gtonp.price, gtonp.price_dealer, gtonp.price_partner,
                    	gtoflx.series, gtoflx.model, gtoflx.beam,
                        gtoflx_images.thumb as img_thumb,
                        pistol_brands.name as pistolMfr,
                        pistols.slug,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                    FROM gtonp
                    INNER JOIN gtoflx
                        ON gtoflx.id = gtonp.gtoflxid
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    GROUP BY gtoflx.model
                    ORDER BY gtoflx.id";
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
    public static function getGtonpDetailsForDealerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        gtonp.id, gtonp.mvc_model, gtonp.name,
                        gtonp.gtoflxid, gtonp.price, gtonp.price_dealer, gtonp.price_partner,
                        gtoflx.series, gtoflx.model,
                        gtoflx.beam, gtoflx_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM gtonp
                    INNER JOIN gtoflx
                    	ON gtoflx.id = gtonp.gtoflxid
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    WHERE gtonp.id = :id
                    GROUP BY gtoflx.model";
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
