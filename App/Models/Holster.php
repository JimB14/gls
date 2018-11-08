<?php

namespace App\Models;

use PDO;


class Holster extends \Core\Model
{

    /**
     * retrieves brand specific products for products by brand page
     *
     * @param  INT   $id   The pistol brand ID
     *
     * @return Object
     */
    public static function getHolstersByBrand($id)
    {
        // echo "Connected to getHolstersByBrand() in Holster Model!"; exit();

        try
        {
            $db = static::getDB();

            $sql =  "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.model as holster_model,
                        holsters.waist, holsters.hand,
                        holster_images.thumb,
                        pistols.slug, pistols.id AS pistol_id,
                        pistol_brands.id as pistol_brand_id, pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holsters.id = holster_trseries_pistol_lookup.holsterid
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    WHERE pistol_brands.id = $id
                    GROUP BY holsters.id
                    ORDER BY holster_brands.id, trseries.model, holsters.id, holsters.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMesssage();
            exit();
        }
    }



    /**
     * retrieves holster by TR Series ID
     *
     * @param  Int  $id     The laser ID
     *
     * @return Object  The holsters
     */
    public static function byTrseries($id)
    {

        try
        {
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.model as holster_model,
                        holsters.waist, holsters.hand,
                        holster_images.thumb,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    WHERE trseries.id = :id
                    GROUP BY holsters.id
                    ORDER BY holsters.holster_brand_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Home Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves holster record by ID
     * @param  INT      $id     the record's ID
     * @return Obj              the holster record
     */
    public static function showHolster($id, $trseries_model)
    {
        // echo "Connected to showHolster() method of Holster model!";

        try
        {
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id, holsters.mvc_model, holsters.model as holster_model,
                        holsters.waist, holsters.hand, holsters.ad_title,
                        holsters.price, holsters.ad_blurb, holsters.feature01,
                        holsters.feature02, holsters.feature03, holsters.size,
                        holsters.weight, holsters.upc, holsters.mfr_href,
                        holsters.mfr_href_video, holsters.buy_link,
                        holsters.special_message,
                        holster_images.thumb, holster_images.small, holster_images.med,
                        holster_images.fullsize, holster_images.med_lefthand,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        pistol_brands.name as pistolMfr,
                        pistols.slug,
                        trseries_images.thumb AS laser_thumb, trseries_images.alt_color_thumb,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN trseries_images
                        ON trseries.id = trseries_images.trseries_id
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    WHERE holsters.id = :id
                    AND trseries.model = :model
                    GROUP BY holsters.id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id,
                ':model' => $trseries_model
            ];
            $stmt->execute($parameters);
            $holster = $stmt->fetch(PDO::FETCH_OBJ);

            return $holster;
        }
        catch ( PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves holster data for holster page table
     * route: https://armalaser.com/holsters
     *
     * @return object The holsters & pistol data
     */
    public static function getHolstersDataForTable()
    {
       try
       {
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        pistol_brands.name as pistolMfr,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') as pistol_models,
                        trseries.model as trseries_model, trseries.id as trseries_id,
                        holster_brands.name as holsterMfr,
                        pistols.slug,
                        GROUP_CONCAT(DISTINCT holsters.id ORDER BY holsters.id ASC SEPARATOR ', ') as holster_ids,
                        GROUP_CONCAT(DISTINCT holsters.model SEPARATOR ', ') as holster_models,
                        holsters.waist, holsters.mfr_href,
                        holster_images.thumb
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    GROUP BY pistols.model
                    ORDER BY pistol_brands.name, trseries.model";
            $stmt = $db->query($sql);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
       }
       catch(PDOException $e)
       {
            echo $e->getMessage();
            exit();
       }
    }



    /**
     * retrieves unique holster models and data
     *
     * @return Obj      the model names & images
     */
    public static function getUniqueModelData()
    {
        try
        {
           $db = static::getDB();

           $sql = "SELECT DISTINCT
                   	    holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.model as holster_model,
                        holsters.waist,
                        holster_images.thumb,
                        holsters.mfr_href
                   FROM holsters
                   INNER JOIN holster_brands
                   	ON holster_brands.id = holsters.holster_brand_id
                   INNER JOIN holster_images
                   	ON holster_images.holster_id = holsters.id
                   WHERE holsters.model IN ('MiniTuck', 'MiniSlide', 'SnapSlide', 'SuperTuck', 'Insider', 'Mini Scabbard', 'Nemesis', 'LG-6-Long', 'LG-6-Short', 'MD-4-Gen-1-Medium', 'MD-4-Modified', 'SM-2', 'SM-3-Small', 'SM-5-Modified')
                   GROUP BY holsters.model
                   ORDER BY holsterMfr, holsters.waist";
           $stmt = $db->query($sql);
           $table_header_data = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $table_header_data;
        }
        catch(PDOException $e)
        {
           echo $e->getMessage();
           exit();
        }
    }



    /**
     * retrieves holsters by TR Series ID and holster model
     * @param  INT          $id         the TR Series laser ID
     * @param  String       $model      the holster model name
     * @return Object                   the holster records
     */
    public static function byTrseriesIdAndHolsterModel($id, $model)
    {
        // echo "Connected to byTrseriesIdAndHolsterModel() method in Holster model!<br>";

        try
        {
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.model as holster_model,
                        holsters.waist, holsters.hand,
                        holster_images.thumb,
                        pistol_brands.name as pistolMfr, pistols.slug,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    WHERE trseries.id = :id
                    AND holsters.model = :model
                    GROUP BY holsters.id
                    ORDER BY holsters.holster_brand_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id,
                ':model' => $model
            ];
            $stmt->execute($parameters);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves holster record by ID
     * @param  INT      $id     the record's ID
     * @return Obj              the holster record
     */
    public static function getHolsterDetailsForCart($id, $trseries_model)
    {
        // echo "Connected to getHolsterDetailsForCart() method of Holster model!";

        if ($trseries_model != '') 
        {
            try
            {
                $db = static::getDB();
    
                $sql = "SELECT DISTINCT
                            holster_brands.name as holsterMfr,
                            holsters.id, holsters.name, holsters.model as holster_model,
                            holsters.waist, holsters.hand, holsters.ad_title,
                            holsters.price, holsters.weight, holsters.special_message,
                            holster_images.thumb,
                            trseries.id as trseries_id, trseries.model as trseries_model,
                            pistol_brands.name as pistolMfr,
                            GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                        FROM holsters
                        INNER JOIN holster_trseries_pistol_lookup
                            ON holster_trseries_pistol_lookup.holsterid = holsters.id
                        INNER JOIN trseries
                            ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                        INNER JOIN pistols
                            ON pistols.id = holster_trseries_pistol_lookup.pistolid
                        INNER JOIN holster_brands
                            ON holster_brands.id = holsters.holster_brand_id
                        INNER JOIN pistol_brands
                            ON pistol_brands.id = pistols.brand_id
                        INNER JOIN holster_images
                            ON holster_images.holster_id = holsters.id
                        WHERE holsters.id = :id
                        AND trseries.model = :model
                        GROUP BY holsters.id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id'    => $id,
                    ':model' => $trseries_model
                ];
                $stmt->execute($parameters);
                $item = $stmt->fetch(PDO::FETCH_OBJ);
    
                // return to Cart controller
                return $item;
            }
            catch ( PDOException $e)
            {
                echo $e->getMessage();
                exit();
            }
        } 
        // phone order only
        else 
        {
            try
            {
                $db = static::getDB();
    
                $sql = "SELECT DISTINCT
                            holster_brands.name as holsterMfr,
                            holsters.id, holsters.name, holsters.model as holster_model,
                            holsters.waist, holsters.hand, holsters.ad_title,
                            holsters.price, holsters.weight, holsters.special_message,
                            holster_images.thumb,
                            trseries.id as trseries_id, trseries.model as trseries_model,
                            pistol_brands.name as pistolMfr,
                            GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                        FROM holsters
                        INNER JOIN holster_trseries_pistol_lookup
                            ON holster_trseries_pistol_lookup.holsterid = holsters.id
                        INNER JOIN trseries
                            ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                        INNER JOIN pistols
                            ON pistols.id = holster_trseries_pistol_lookup.pistolid
                        INNER JOIN holster_brands
                            ON holster_brands.id = holsters.holster_brand_id
                        INNER JOIN pistol_brands
                            ON pistol_brands.id = pistols.brand_id
                        INNER JOIN holster_images
                            ON holster_images.holster_id = holsters.id
                        WHERE holsters.id = :id
                        AND trseries.model = :model
                        GROUP BY holsters.id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id'    => $id,
                    ':model' => $trseries_model
                ];
                $stmt->execute($parameters);
                $item = $stmt->fetch(PDO::FETCH_OBJ);
    
                // return to Cart controller
                return $item;
            }
            catch ( PDOException $e)
            {
                echo $e->getMessage();
                exit();
            }
        }
        
    }




    /**
     * retrieves holsters for new order (replace tab)
     *
     * @param  Int  $id     The laser ID
     *
     * @return Object  The holsters
     */
    public static function getHolstersForAdmin()
    {
        try
        {
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.mvc_model, holsters.name,
                        holsters.model as holster_model, holsters.waist, holsters.hand,
                        holsters.price, holsters.price_dealer, holster_images.thumb,
                        holsters.upc,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    GROUP BY holsters.id
                    ORDER BY trseries.id";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves holsters to populate drop-down
     *
     *
     * @return Object  The holsters
     */
    public static function getHolstersForDropdown()
    {
        try
        {
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id, holsters.mvc_model, holsters.name,
                        holsters.model as holster_model, holsters.waist, holsters.hand,
                        holsters.price, holsters.price_dealer, holster_images.thumb,
                        holsters.upc,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    GROUP BY holsters.id
                    ORDER BY holster_brands.name";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves holster field items for Admin shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getHolsterDetailsForAdminCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id, holsters.mvc_model, holsters.name,
                        holsters.model as holster_model, holsters.waist, holsters.hand,
                        holsters.price, holsters.price_dealer, holsters.price_partner,
                        holsters.upc,
                        holster_images.thumb,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    WHERE holsters.id = :id
                    GROUP BY holsters.id
                    ORDER BY pistol_brands.name";
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




    /**
     * retrieves holster by TR Series ID
     *
     * @param  Int  $id     The laser ID
     *
     * @return Object  The holsters
     */
    public static function getHolstersForDealers()
    {
        try
        {
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.mvc_model, holsters.name,
                        holsters.model as holster_model, holsters.waist, holsters.hand,
                        holsters.price, holsters.price_dealer, holster_images.thumb,
                        holsters.upc,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    GROUP BY holsters.id
                    ORDER BY pistol_brands.name";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves holster field items for Dealer shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getHolsterDetailsForDealerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id, holsters.mvc_model, holsters.name,
                        holsters.model as holster_model, holsters.waist, holsters.hand,
                        holsters.price, holsters.price_dealer, holsters.price_partner,
                        holsters.upc,
                        holster_images.thumb,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    WHERE holsters.id = :id
                    GROUP BY holsters.id
                    ORDER BY pistol_brands.name";
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




    /**
     * retrieves holster by TR Series ID
     *
     * @param  Int  $id     The laser ID
     *
     * @return Object  The holsters
     */
    public static function getHolstersForPartners()
    {
        try
        {
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.mvc_model, holsters.name,
                        holsters.model as holster_model, holsters.waist, holsters.hand,
                        holsters.price, holsters.price_dealer, holsters.price_partner,
                        holsters.upc,
                        holster_images.thumb,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    GROUP BY holsters.id
                    ORDER BY pistol_brands.name";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves holster field items for Partner shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getHolsterDetailsForPartnerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id, holsters.mvc_model, holsters.name,
                        holsters.model as holster_model, holsters.waist, holsters.hand,
                        holsters.price, holsters.price_dealer, holsters.price_partner,
                        holsters.upc,
                        holster_images.thumb,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    WHERE holsters.id = :id
                    GROUP BY holsters.id
                    ORDER BY pistol_brands.name";
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





    /**
     * retrieves holster by pistol ID
     *
     * @param  Int  $id   The pistol ID
     *
     * @return Object  The holsters
     */
    public static function byPistolId($id)
    {

        try
        {
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT DISTINCT
                        holster_brands.name as holsterMfr,
                        holsters.id as holster_id, holsters.model as holster_model,
                        holsters.waist, holsters.hand,
                        holster_images.thumb,
                        pistol_brands.name as pistolMfr,
                        trseries.id as trseries_id, trseries.model as trseries_model,
                        GROUP_CONCAT(DISTINCT pistols.model SEPARATOR ', ') as pistol_models
                    FROM holsters
                    INNER JOIN holster_trseries_pistol_lookup
                        ON holster_trseries_pistol_lookup.holsterid = holsters.id
                    INNER JOIN trseries
                        ON trseries.id = holster_trseries_pistol_lookup.trseriesid
                    INNER JOIN pistols
                        ON pistols.id = holster_trseries_pistol_lookup.pistolid
                    INNER JOIN holster_brands
                        ON holster_brands.id = holsters.holster_brand_id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    INNER JOIN holster_images
                        ON holster_images.holster_id = holsters.id
                    WHERE pistols.id = :id
                    GROUP BY holsters.id
                    ORDER BY holsters.holster_brand_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



// = = = = = refactored updates above  = = = = = = = = = = = = = = = = = = = //





   /**
    * retrieves all holsters
    *
    * @return object The holsters & pistol data
    */
   public static function allHolsters()
   {
      // retrieve data
      try
      {
         // establish db connection
         $db = static::getDB();

         // http://www.mysqltutorial.org/mysql-group_concat/
         $sql = "SELECT DISTINCT
                    id, laser_id, pistol_mfr, pistol_model_match, laser_model_match,
                    GROUP_CONCAT(DISTINCT model SEPARATOR ', ') AS matches
                 FROM holsters
                 GROUP BY pistol_model_match
                 ORDER BY pistol_mfr";
         $stmt = $db->query($sql);
         $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

          // return to Controller
          return $holsters;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   /**
    * retrieves all holsters by pistol brand name
    *
    * @return object The holsters & pistol data
    */
   public static function getHolstersByPistolBrand($pistol_brand_name)
   {

      // retrieve data
      try
      {
           // establish db connection
           $db = static::getDB();

           // retrieve data
           $sql = "SELECT * FROM holsters
                   WHERE pistol_mfr = :pistol_mfr
                   ORDER BY mfr, pistol_model_match, alpha_name";
           $stmt = $db->prepare($sql);
           $parameters = [
              ':pistol_mfr' => $pistol_brand_name
           ];
           $stmt->execute($parameters);

           $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

           // return to Home Controller
           return $holsters;
      }
      catch(PDOException $e)
      {
           echo $e->getMessage();
           exit();
      }
   }




   /**
    * retrieves pistol brand name by laser model name
    *
    * @return object The holsters & pistol data
    */
   public static function getPistolBrandNameByLaserModelName($laser_model)
   {

      // retrieve data
      try
      {
           // establish db connection
           $db = static::getDB();

           // retrieve data
           $sql = "SELECT pistol_mfr FROM holsters
                   WHERE laser_model_match = :laser_model_match
                   LIMIT 1";
           $stmt = $db->prepare($sql);
           $parameters = [
              ':laser_model_match' => $laser_model
           ];
           $stmt->execute($parameters);

           $pistol_brand = $stmt->fetch(PDO::FETCH_OBJ);

           // return to Controller
           return $pistol_brand;
      }
      catch(PDOException $e)
      {
           echo $e->getMessage();
           exit();
      }
   }


    /**
     * retrieves all holsters & pistol match data
     *
     * @return object The holsters & pistol data
     */
    public static function getAllHolsters()
    {
        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT pistols.id as pistol_id, pistols.pistol_brand_name,
                    pistols.pistol_model_name, lasers.laser_series,
                    lasers.laser_model, holsters.ad_title as name,
                    holsters.price, holsters.id as holster_id
                    FROM holsters
                    INNER JOIN pistolholsterlaser
                    ON holsters.id = holsterid
                    INNER JOIN pistols
                    ON pistols.id = pistolid
                    INNER JOIN lasers
                    ON lasers.id = laserid
                    ORDER BY pistol_brand_name,
                    pistol_model_name";
            $stmt = $db->query($sql);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Home Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



   /**
    * returns images of matching holster_description
    *
    * @param  Int    $id   The brand ID
    *
    * @return boolean
    */
   public static function findMatchingHolsterImages($id)
   {

      // retrieve data
      try
      {
          // establish db connection
          $db = static::getDB();

         /// retrieve data
         $sql = "SELECT pistols.id as pistolId, pistols.pistol_brand_name,
                 pistols.pistol_model_name, lasers.id as laserId,
                 lasers.laser_model as model, lasers.mfr_name as mfr_match,
                 lasers.mfr_model_match, holsters.id as holsterId,
                 holsters.sort_id, holsters.laser_series,
                 holsters.image_med as holster_image_med,
                 holsters.image_small as holster_image_small,
                 holsters.image_thumb as holster_image_thumb,
                 holsters.laser_series as holster_match_laser_series,
                 holsters.mfr, holsters.model as holster_model, holsters.hand,
                 holsters.ad_title,
                 holsters.price as holster_price,
                 holsters.ad_blurb as holsterAdBlurb,
                 holsters.feature01 as holster_feature01,
                 holsters.feature02 as holster_feature02,
                 holsters.feature03 as holster_feature03,
                 holsters.size, holsters.weight,
                 holsters.upc as holster_upc, holsters.mfr_href,
                 holsters.buy_link as holster_buy_link,
                 holsters.special_message as holster_special_message
                 FROM pistols
                 INNER JOIN pistolholsterlaser
                 ON pistols.id = pistolid
                 INNER JOIN holsters
                 ON holsters.id = holsterid
                 INNER JOIN lasers
                 ON lasers.id = laserid
                 WHERE pistol_brand_id = :id
                 AND lasers.laser_color = 'red'
                 ORDER BY holsters.sort_id";  // 'red' prevents duplicate results (red & green returned)
         $stmt = $db->prepare($sql);
         $parameters = [
             ':id' => $id
         ];
         $stmt->execute($parameters);
         $holsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

         // test
         // echo '<pre>';
         // print_r($holsters);
         // echo '</pre>';
         // exit();

         if($holsters){
            // loop thru array and store images into new array
            foreach($holsters as $holster){
               // echo $holster['holster_image_thumb'].'<br>';
               // store value in array
               $holster_thumb_images[] = $holster['holster_image_thumb'];
            }

            // test
            // echo '<pre>';
            // print_r($holster_thumb_images);
            // echo '</pre>';
            // exit();

            // store unique elements only
            $holster_images = array_unique($holster_thumb_images);

            // test
            // echo '<pre>';
            // print_r($unique);
            // echo '</pre>';
            // exit();

            $results = [
               'holsters' => $holsters,
               'holster_images' => $holster_images
            ];

            // return ASSOC ARRAY to Home Controller
            return $results;
         }
         else
         {
            return false;
         }
    }
    catch(PDOException $e)
    {
         echo $e->getMessage();
         exit();
    }
}



    /**
     * retrieves holster by brand ID
     *
     * @param  Int $id The brand ID
     *
     * @return Object  The brand products
     */
    public static function getHolsters($id)
    {
        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT pistols.id as pistolId, pistols.pistol_brand_name,
                    pistols.pistol_model_name, lasers.id as laserId,
                    lasers.laser_model as model, lasers.mfr_name as mfr_match,
                    lasers.mfr_model_match, holsters.id as holsterId,
                    holsters.sort_id,
                    holsters.image_med as holster_image_med,
                    holsters.image_small as holster_image_small,
                    holsters.image_thumb as holster_image_thumb,
                    holsters.laser_series as holster_match_laser_series,
                    holsters.mfr, holsters.model as holster_model, holsters.hand,
                    holsters.ad_title,
                    holsters.price as holster_price,
                    holsters.ad_blurb as holsterAdBlurb,
                    holsters.feature01 as holster_feature01,
                    holsters.feature02 as holster_feature02,
                    holsters.feature03 as holster_feature03,
                    holsters.size, holsters.weight,
                    holsters.upc as holster_upc, holsters.mfr_href,
                    holsters.buy_link as holster_buy_link,
                    holsters.special_message as holster_special_message
                    FROM pistols
                    INNER JOIN pistolholsterlaser
                    ON pistols.id = pistolid
                    INNER JOIN holsters
                    ON holsters.id = holsterid
                    INNER JOIN lasers
                    ON lasers.id = laserid
                    WHERE pistol_brand_id = :id
                    AND lasers.laser_color = 'red'
                    ORDER BY holsters.sort_id";  // 'red' prevents duplicate results (red & green returned)
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Home Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




   public static function getHolstersByLaserModel($laser_model)
   {
      // retrieve data
      try
      {
         // establish db connection
         $db = static::getDB();

         // retrieve data
         $sql = "SELECT * FROM holsters
                WHERE laser_model_match = :laser_model_match
                ORDER BY mfr, model, hand, pistol_model_match, alpha_name";
         $stmt = $db->prepare($sql);
         $parameters = [
           ':laser_model_match' => $laser_model
         ];
         $stmt->execute($parameters);

         $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

         // return to Holsters Controller
         return $holsters;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }




   public static function getHolstersByLaserAndHolsterModel($laser, $holster_model)
   {
      // retrieve data
      try
      {
         // establish db connection
         $db = static::getDB();

         // retrieve data
         $sql = "SELECT * FROM holsters
                 WHERE laser_model_match = :laser_model_match
                 AND model = :model";
         $stmt = $db->prepare($sql);
         $parameters = [
           ':laser_model_match' => $laser,
           ':model'             => $holster_model,
         ];
         $stmt->execute($parameters);

         $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

         // return to Holsters Controller
         return $holsters;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }





    /**
     * retrieves holsters by Laser ID
     *
     * @param  Int $id The brand ID
     *
     * @return Object  The brand products
     */
    public static function getHolstersByLaserId($id)
    {
        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT pistols.id as pistolId, pistols.pistol_brand_name,
                    pistols.pistol_model_name, lasers.id as laserId,
                    lasers.laser_model as model, lasers.mfr_name as mfr_match,
                    lasers.mfr_model_match, holsters.id as holsterId,
                    holsters.sort_id,
                    holsters.image_med as holster_image_med,
                    holsters.image_small as holster_image_small,
                    holsters.image_thumb as holster_image_thumb,
                    holsters.laser_series as holster_match_laser_series,
                    holsters.mfr, holsters.model as holster_model, holsters.hand,
                    holsters.ad_title,
                    holsters.price as holster_price,
                    holsters.ad_blurb as holsterAdBlurb,
                    holsters.feature01 as holster_feature01,
                    holsters.feature02 as holster_feature02,
                    holsters.feature03 as holster_feature03,
                    holsters.size, holsters.weight,
                    holsters.upc as holster_upc, holsters.mfr_href,
                    holsters.buy_link as holster_buy_link,
                    holsters.special_message as holster_special_message
                    FROM pistols
                    INNER JOIN pistolholsterlaser
                    ON pistols.id = pistolid
                    INNER JOIN holsters
                    ON holsters.id = holsterid
                    INNER JOIN lasers
                    ON lasers.id = laserid
                    WHERE lasers.id = :id
                  --   AND lasers.laser_color = 'red'
                    ORDER BY holsters.sort_id";  // 'red' prevents duplicate results (red & green returned)
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Holsters Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * retrieves holsters by pistol ID
     *
     * @param  Int $id The brand ID
     *
     * @return Object  The brand products
     */
    public static function getHolstersByPistol($pistol_id)
    {
        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT pistols.id as pistol_id, pistols.pistol_brand_name,
                    pistols.pistol_model_name, lasers.laser_model as model,
                    holsters.*
                    FROM pistols
                    INNER JOIN pistolholsterlaser
                    ON pistols.id = pistolid
                    INNER JOIN holsters
                    ON holsters.id = holsterid
                    INNER JOIN lasers
                    ON lasers.id = laserid
                    WHERE pistols.id = :id
                    AND lasers.laser_color = 'red'";  // prevents duplicate results (red & green returned)
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $pistol_id
            ];
            $stmt->execute($parameters);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Home Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    /**
     * retrieves all holsters & pistol match data
     *
     * @return object The holsters & pistol data
     */
    public static function getHolstersTableData()
    {
        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM holsters
                    ORDER BY created_at DESC";
            $stmt = $db->query($sql);
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Home Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves holster based on field
     *
     * @param  string $name The holster name
     *
     * @return object       The holster
     */
    public static function getHolstersByName($name)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM holsters
                    WHERE laser_model LIKE '$name%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Admin/Accessories Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves data for one holster
     *
     * @return object The holsters & pistol data
     */
    public static function getHolster($id, $pistol_id)
    {
        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT pistols.id as pistolId, pistols.pistol_brand_name,
                    pistols.pistol_model_name, lasers.id as laserId,
                    lasers.laser_model as model, lasers.mfr_name as mfr_match,
                    lasers.mfr_model_match, holsters.id as holsterId,
                    holsters.laser_series,
                    holsters.image_full_size as holster_image_full_size,
                    holsters.image_med as holster_image_med,
                    holsters.image_small as holster_image_small,
                    holsters.image_thumb as holster_image_thumb,
                    holsters.laser_series as holster_match_laser_series,
                    holsters.mfr, holsters.model as holster_model, holsters.hand,
                    holsters.ad_title,
                    holsters.price as holster_price,
                    holsters.ad_blurb as holsterAdBlurb,
                    holsters.feature01 as holster_feature01,
                    holsters.feature02 as holster_feature02,
                    holsters.feature03 as holster_feature03,
                    holsters.size, holsters.weight, holsters.hand,
                    holsters.upc as holster_upc, holsters.mfr_href,
                    holsters.buy_link as holster_buy_link,
                    holsters.special_message as holster_special_message
                    FROM pistols
                    INNER JOIN pistolholsterlaser
                    ON pistols.id = pistolid
                    INNER JOIN holsters
                    ON holsters.id = holsterid
                    INNER JOIN lasers
                    ON lasers.id = laserid
                    WHERE holsters.id = :id
                    AND pistols.id = :pistol_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'        => $id,
                ':pistol_id' => $pistol_id
            ];
            $stmt->execute($parameters);
            $holster = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Home Controller
            return $holster;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * posts new holster to holsters table
     *
     * @return boolean
     */
    public static function postNewHolster()
    {
      // retrieve form data
      $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
      $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
      $image_med = ( isset($_REQUEST['image_med']) ) ? filter_var($_REQUEST['image_med']): '';
      $image_med_reverse = ( isset($_REQUEST['image_med_reverse']) ) ? filter_var($_REQUEST['image_med_reverse']): '';
      $image_small = ( isset($_REQUEST['image_small']) ) ? filter_var($_REQUEST['image_small']): '';
      $image_thumb = ( isset($_REQUEST['image_thumb']) ) ? filter_var($_REQUEST['image_thumb']): '';
      $pistol_mfr = ( isset($_REQUEST['pistol_mfr']) ) ? filter_var($_REQUEST['pistol_mfr']): '';
      $pistol_model_match = ( isset($_REQUEST['pistol_model_match']) ) ? filter_var($_REQUEST['pistol_model_match']): '';
      $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
      $laser_model_match = ( isset($_REQUEST['laser_model_match']) ) ? filter_var($_REQUEST['laser_model_match']): '';
      $mfr = ( isset($_REQUEST['mfr']) ) ? filter_var($_REQUEST['mfr']): '';
      $model = ( isset($_REQUEST['model']) ) ? filter_var($_REQUEST['model']): '';
      $hand = ( isset($_REQUEST['hand']) ) ? filter_var($_REQUEST['hand']): '';
      $ad_title = ( isset($_REQUEST['ad_title']) ) ? filter_var($_REQUEST['ad_title']): '';
      $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
      $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
      $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
      $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
      $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
      $size = ( isset($_REQUEST['size']) ) ? filter_var($_REQUEST['size']): '';
      $weight = ( isset($_REQUEST['weight']) ) ? filter_var($_REQUEST['weight']): '';
      $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
      $mfr_href = ( isset($_REQUEST['mfr_href']) ) ? filter_var($_REQUEST['mfr_href']): '';
      $mfr_href_video = ( isset($_REQUEST['mfr_href_video']) ) ? filter_var($_REQUEST['mfr_href_video']): '';
      $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
      $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO holsters SET
            image              = :image,
            image_full_size    = :image_full_size,
            image_med          = :image_med,
            image_med_reverse  = :image_med_reverse,
            image_small        = :image_small,
            image_thumb        = :image_thumb,
            pistol_mfr         = :pistol_mfr,
            pistol_model_match = :pistol_model_match,
            laser_series       = :laser_series,
            laser_model_match  = :laser_model_match,
            mfr                = :mfr,
            model              = :model,
            hand               = :hand,
            ad_title           = :ad_title,
            price              = :price,
            ad_blurb           = :ad_blurb,
            feature01          = :feature01,
            feature02          = :feature02,
            feature03          = :feature03,
            size               = :size,
            weight             = :weight,
            upc                = :upc,
            mfr_href           = :mfr_href,
            mfr_href_video     = :mfr_href_video,
            buy_link           = :buy_link,
            special_message    = :special_message";
            $stmt = $db->prepare($sql);
            $parameters = [
               ':image'              => $image,
               ':image_full_size'    => $image_full_size,
               ':image_med'          => $image_med,
               ':image_med_reverse'  => $image_med_reverse,
               ':image_small'        => $image_small,
               ':image_thumb'        => $image_thumb,
               ':pistol_mfr'         => $pistol_mfr,
               ':pistol_model_match' => $pistol_model_match,
               ':laser_series'       => $laser_series,
               ':laser_model_match'  => $laser_model_match,
               ':mfr'                => $mfr,
               ':model'              => $model,
               ':hand'               => $hand,
               ':ad_title'           => $ad_title,
               ':price'              => $price,
               ':ad_blurb'           => $ad_blurb,
               ':feature01'          => $feature01,
               ':feature02'          => $feature02,
               ':feature03'          => $feature03,
               ':size'               => $size,
               ':weight'             => $weight,
               ':upc'                => $upc,
               ':mfr_href'           => $mfr_href,
               ':mfr_href_video'     => $mfr_href_video,
               ':buy_link'           => $buy_link,
               ':special_message'    => $special_message
            ];
            $result = $stmt->execute($parameters);

            // return result to Admin/Holsters Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * deletes holster based on ID
     *
     * @param  Int $id  The holster ID
     *
     * @return boolean
     */
    public static function deleteHolster($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM holsters
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            // return to Admin/Accessories Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves holster by ID
     *
     * @param  Int $id  The holster's ID
     *
     * @return Object   The holster data
     */
    public static function getHolsterById($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // $sql = "SELECT lasers.image_thumb, holsters.*
            //          FROM holsters
            //          INNER JOIN lasers ON
            //          holsters.laser_id = lasers.id
            //          WHERE holsters.laser_id = :laser_id";
            $sql = "SELECT * FROM holsters
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            // $parameters = [
            //     ':laser_id' => $id
            // ];
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $holster = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Admin/Holsters Controller
            return $holster;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * updates holster record in holsters table
     *
     * @return boolean
     */
    public static function updateHolster($id)
    {
        // retrieve form data
        $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
        $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
        $image_med = ( isset($_REQUEST['image_med']) ) ? filter_var($_REQUEST['image_med']): '';
        $image_med_reverse = ( isset($_REQUEST['image_med_reverse']) ) ? filter_var($_REQUEST['image_med_reverse']): '';
        $image_small = ( isset($_REQUEST['image_small']) ) ? filter_var($_REQUEST['image_small']): '';
        $image_thumb = ( isset($_REQUEST['image_thumb']) ) ? filter_var($_REQUEST['image_thumb']): '';
        $pistol_mfr = ( isset($_REQUEST['pistol_mfr']) ) ? filter_var($_REQUEST['pistol_mfr']): '';
        $pistol_model_match = ( isset($_REQUEST['pistol_model_match']) ) ? filter_var($_REQUEST['pistol_model_match']): '';
        $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
        $laser_model_match = ( isset($_REQUEST['laser_model_match']) ) ? filter_var($_REQUEST['laser_model_match']): '';
        $mfr = ( isset($_REQUEST['mfr']) ) ? filter_var($_REQUEST['mfr']): '';
        $model = ( isset($_REQUEST['model']) ) ? filter_var($_REQUEST['model']): '';
        $hand = ( isset($_REQUEST['hand']) ) ? filter_var($_REQUEST['hand']): '';
        $ad_title = ( isset($_REQUEST['ad_title']) ) ? filter_var($_REQUEST['ad_title']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $size = ( isset($_REQUEST['size']) ) ? filter_var($_REQUEST['size']): '';
        $weight = ( isset($_REQUEST['weight']) ) ? filter_var($_REQUEST['weight']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $mfr_href = ( isset($_REQUEST['mfr_href']) ) ? filter_var($_REQUEST['mfr_href']): '';
        $mfr_href_video = ( isset($_REQUEST['mfr_href_video']) ) ? filter_var($_REQUEST['mfr_href_video']): '';
        $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE holsters SET
                    image              = :image,
                    image_full_size    = :image_full_size,
                    image_med          = :image_med,
                    image_med_reverse  = :image_med_reverse,
                    image_small        = :image_small,
                    image_thumb        = :image_thumb,
                    pistol_mfr         = :pistol_mfr,
                    pistol_model_match = :pistol_model_match,
                    laser_series       = :laser_series,
                    laser_model_match  = :laser_model_match,
                    mfr                = :mfr,
                    model              = :model,
                    hand               = :hand,
                    ad_title           = :ad_title,
                    price              = :price,
                    ad_blurb           = :ad_blurb,
                    feature01          = :feature01,
                    feature02          = :feature02,
                    feature03          = :feature03,
                    size               = :size,
                    weight             = :weight,
                    upc                = :upc,
                    mfr_href           = :mfr_href,
                    mfr_href_video     = :mfr_href_video,
                    buy_link           = :buy_link,
                    special_message    = :special_message
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'                 => $id,
                ':image'              => $image,
                ':image_full_size'    => $image_full_size,
                ':image_med'          => $image_med,
                ':image_med_reverse'  => $image_med_reverse,
                ':image_small'        => $image_small,
                ':image_thumb'        => $image_thumb,
                ':pistol_mfr'         => $pistol_mfr,
                ':pistol_model_match' => $pistol_model_match,
                ':laser_series'       => $laser_series,
                ':laser_model_match'  => $laser_model_match,
                ':mfr'                => $mfr,
                ':model'              => $model,
                ':hand'               => $hand,
                ':ad_title'           => $ad_title,
                ':price'              => $price,
                ':ad_blurb'           => $ad_blurb,
                ':feature01'          => $feature01,
                ':feature02'          => $feature02,
                ':feature03'          => $feature03,
                ':size'               => $size,
                ':weight'             => $weight,
                ':upc'                => $upc,
                ':mfr_href'           => $mfr_href,
                ':mfr_href_video'     => $mfr_href_video,
                ':buy_link'           => $buy_link,
                ':special_message'    => $special_message
            ];
            $result = $stmt->execute($parameters);

            // return result to Admin/Holsters Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




   public static function getHolsterByBrand($name)
   {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT pistols.pistol_brand_name, pistols.pistol_model_name,
                    lasers.laser_series, lasers.laser_model, holsters.ad_title as name,
                    holsters.price, holsters.id
                    FROM holsters
                    INNER JOIN pistolholsterlaser
                    ON holsters.id = holsterid
                    INNER JOIN pistols
                    ON pistols.id = pistolid
                    INNER JOIN lasers
                    ON lasers.id = laserid
                    WHERE pistols.pistol_brand_name LIKE '$name%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $holsters = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Holsters Controller
            return $holsters;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
   }



   public static function getReverseImageMed($id)
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         // query
         $sql = "SELECT image_med, image_med_reverse FROM holsters
                 WHERE id = :id";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':id' => $id
         ];
         $stmt->execute($parameters);

         $holsterMedImages = $stmt->fetch(PDO::FETCH_OBJ);

         return $holsterMedImages;
      }
      catch (PDOxception $e) {
         echo $e->getMessage();
         exit();
      }
   }

}
