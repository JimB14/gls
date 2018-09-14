<?php

namespace App\Models;

use PDO;


/**
 * Trseriesnp Model
 *
 * PHP version 7.0
 */
class Trseriesnp extends \Core\Model
{

    /**
     * retrieves all TR Series NP lasers
     *
     * @return object  All lasers in trseries table
     */
    public static function getLasers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql =  "SELECT DISTINCT
                        trseriesnp.id, trseriesnp.mvc_model, trseriesnp.name,
                        trseriesnp.trseriesid, trseriesnp.price, trseriesnp.price_dealer,
                        trseriesnp.price_partner,
                    	trseries.series, trseries.model, trseries.beam,
                        trseries_images.thumb as img_thumb,
                        pistol_brands.name as pistolMfr,
                        pistols.slug,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                    FROM trseriesnp
                    INNER JOIN trseries
                        ON trseries.id = trseriesnp.trseriesid
                    INNER JOIN pistol_trseries_lookup
                    	ON trseries.id = pistol_trseries_lookup.trseriesid
                    INNER JOIN pistols
                    	ON pistol_trseries_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN trseries_images
                    	ON trseries_images.trseries_id = trseries.id
                    GROUP BY trseries.model
                    ORDER BY trseries.id";
            $stmt = $db->query($sql);
            $models = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
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
    public static function getTrseriesnpDetailsForDealerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        trseriesnp.id, trseriesnp.mvc_model, trseriesnp.name,
                        trseriesnp.trseriesid, trseriesnp.price, trseriesnp.price_dealer,
                        trseriesnp.price_partner,
                        trseries.series, trseries.model,
                        trseries.beam, trseries_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM trseriesnp
                    INNER JOIN trseries
                    	ON trseries.id = trseriesnp.trseriesid
                    INNER JOIN pistol_trseries_lookup
                    	ON trseries.id = pistol_trseries_lookup.trseriesid
                    INNER JOIN pistols
                    	ON pistol_trseries_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN trseries_images
                    	ON trseries_images.trseries_id = trseries.id
                    WHERE trseriesnp.id = :id
                    GROUP BY trseries.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $item = $stmt->fetch(PDO::FETCH_OBJ);

            // return to PurchaseController
            return $item;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
