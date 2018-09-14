<?php

namespace App\Models;

use PDO;


/**
 * Laser Model
 *
 * PHP version 7.0
 */
class Gtoflx extends \Core\Model
{
    /**
     * retrieves brand specific products
     *
     * @param  INT  $id   The pistol brand ID
     *
     * @return Object
     */
    public static function getLasersByBrand($id)
    {
        // echo "Connected to getLasersByBrand() in Gtoflx Model! <br> $id"; exit();

        // if glock is selected
        if ($id == 9)
        {
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "SELECT DISTINCT
                            pistols.brand_id, pistols.slug,
                            pistol_brands.name AS pistolMfr,
                            gtoflx.id AS gtoflx_id, gtoflx.series, gtoflx.model AS laser_model, gtoflx_images.thumb,
                            GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                            GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                        FROM gtoflx
                        INNER JOIN pistol_gtoflx_lookup
                            ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                        INNER JOIN pistols
                         ON pistol_gtoflx_lookup.pistolid = pistols.id
                        INNER JOIN pistol_brands
                            ON pistol_brands.id = pistols.brand_id
                        INNER JOIN gtoflx_images
                        ON gtoflx.id = gtoflx_images.gtoflx_id
                        WHERE pistols.brand_id = $id
                        GROUP BY gtoflx.model
                        ORDER BY gtoflx.id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id' => $id
                ];
                $stmt->execute($parameters);
                $gtoflx = $stmt->fetchAll(PDO::FETCH_OBJ);

                // return to Controller
                return $gtoflx;
            }
            catch(PDOException $e)
            {
                echo $e->getMesssage();
                exit();
            }
        }
        else
        {
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "SELECT DISTINCT
                            pistols.brand_id, pistols.slug,
                            pistol_brands.name AS pistolMfr,
                            gtoflx.id AS gtoflx_id, gtoflx.series, gtoflx.model AS laser_model, gtoflx_images.thumb,
                            GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '<br>') AS pistol_models
                            -- GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                        FROM gtoflx
                        INNER JOIN pistol_gtoflx_lookup
                            ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                        INNER JOIN pistols
                         ON pistol_gtoflx_lookup.pistolid = pistols.id
                        INNER JOIN pistol_brands
                            ON pistol_brands.id = pistols.brand_id
                        INNER JOIN gtoflx_images
                        ON gtoflx.id = gtoflx_images.gtoflx_id
                        WHERE pistols.brand_id = $id
                        GROUP BY gtoflx.model
                        ORDER BY gtoflx.id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id' => $id
                ];
                $stmt->execute($parameters);
                $gtoflx = $stmt->fetchAll(PDO::FETCH_OBJ);

                // return to Controller
                return $gtoflx;
            }
            catch(PDOException $e)
            {
                echo $e->getMesssage();
                exit();
            }
        }
    }



    /**
     * retrieves gto-flx laser record by ID
     *
     * @param  String  $laserId     The laser model ID
     *
     * @return Object               The laser record
     */
    public static function getGtoflxLaser($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistols.id AS pistol_id, pistols.slug,
                        pistol_brands.name AS pistolMfr,
                        gtoflx.*,
                        gtoflx_images.id AS images_id, gtoflx_images.fullsize,
                        gtoflx_images.med, gtoflx_images.small, gtoflx_images.thumb,
                        gtoflx_images.alt_color_thumb,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_hrefs
                    FROM pistols
                    INNER JOIN pistol_gtoflx_lookup
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN gtoflx
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE gtoflx.id = $id
                    GROUP BY gtoflx.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $laser = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $laser;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves gto-flx lasers
     *
     * @param  String  $laserId     The laser model ID
     *
     * @return Object               The laser record
     */
    public static function getLasers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        gtoflx.id, gtoflx.mvc_model, gtoflx.name, gtoflx.series,
                        gtoflx.model, gtoflx.beam, gtoflx.price, gtoflx.price_dealer,
                        gtoflx.price_partner, gtoflx_images.thumb AS img_thumb,
                        gtoflx.upc,
                        pistols.id AS pistol_id, pistols.slug,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_hrefs
                    FROM gtoflx
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    GROUP BY gtoflx.model
                    ORDER BY pistol_brands.name";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $gtoflxs = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
            return $gtoflxs;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves product links by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getGtoflxLinks($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT
                    gtoflx_product_links.*,
                    batteries.image_thumb as battery_image_thumb,
                    toolkits.image_thumb as toolkit_image_thumb
                    FROM gtoflx_product_links
                    INNER JOIN batteries
                        ON batteries.id = gtoflx_product_links.battery_id
                    INNER JOIN toolkits
                        ON toolkits.id = gtoflx_product_links.toolkit_id
                    WHERE gtoflx_product_links.gtoflx_id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $productLinks = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $productLinks;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves laser field items for shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getGtoflxDetailsForCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        gtoflx.id, gtoflx.name, gtoflx.series, gtoflx.model, gtoflx.beam,
                        gtoflx.price, gtoflx.price_dealer, gtoflx.weight,
                        gtoflx_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_gtoflx_lookup
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN gtoflx
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE gtoflx.id = :id
                    GROUP BY gtoflx.model";
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




    /**
     * retrieves laser field items for shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getGtoflxDetailsForDealerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        gtoflx.id, gtoflx.name, gtoflx.series, gtoflx.model,
                        gtoflx.beam, gtoflx.price_dealer, gtoflx.price_partner,
                        gtoflx.weight, gtoflx_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_gtoflx_lookup
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN gtoflx
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE gtoflx.id = :id
                    GROUP BY gtoflx.model";
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




    public static function getLasersForWarrantyRegistrationDropdown()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT id, name, beam FROM gtoflx ";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $lasers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $lasers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves gto-flx laser record by pistol ID
     *
     * @param  String  $laserId     The laser model ID
     *
     * @return Object               The laser record
     */
    public static function byPistolId($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistols.id AS pistol_id,
                        pistol_brands.name AS pistolMfr, pistols.slug,
                        gtoflx.*,
                        gtoflx_images.id AS images_id, gtoflx_images.fullsize,
                        gtoflx_images.med, gtoflx_images.small, gtoflx_images.thumb,
                        gtoflx_images.alt_color_thumb,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_hrefs
                    FROM pistols
                    INNER JOIN pistol_gtoflx_lookup
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN gtoflx
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE pistols.id = $id
                    GROUP BY gtoflx.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $laser = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $laser;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves laser based on laser model
     *
     * @param  string $laser_model The laser model
     *
     * @return object              The laser
     */
    public static function getLaser($laser_model)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        gtoflx.id, gtoflx.mvc_model, gtoflx.name, gtoflx.series,
                        gtoflx.model, gtoflx.beam, gtoflx.price, gtoflx.price_dealer,
                        gtoflx.price_partner, gtoflx_images.thumb AS img_thumb,
                        gtoflx.upc,
                        pistols.id AS pistol_id, pistols.slug,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_hrefs
                    FROM gtoflx
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistol_gtoflx_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN gtoflx_images
                    	ON gtoflx_images.gtoflx_id = gtoflx.id
                    WHERE gtoflx.model LIKE '%$laser_model%'
                    GROUP BY gtoflx.model
                    ORDER BY pistol_brands.name";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $lasers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Main Controller
            return $lasers;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



}
