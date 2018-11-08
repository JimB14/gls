<?php

namespace App\Models;

use PDO;


class Flx extends \Core\Model
{

    /**
     * retrieves all FLX records
     *
     * @return Object   The FLX records
     */
    public static function getAllFlx()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistol_brands.name AS pistolMfr,
                        pistols.model as pistol_model, pistols.slug,
                        flx.id AS flx_id, flx.model AS flx_model, flx.name,
                        flx.price, flx.price_dealer, flx.price_partner, flx.upc,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.id ASC SEPARATOR ', ') AS pistol_models
                    FROM flx
                    INNER JOIN gtoflx
                    	ON gtoflx.id = flx.gtoflx_id
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistols.id = pistol_gtoflx_lookup.pistolid
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    GROUP BY flx.id
                    ORDER BY pistol_brands.name, flx.model";
            $stmt = $db->query($sql);
            $flx = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
            return $flx;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves all FLX records
     *
     * @return Object   The FLX records
     */
    public static function getFlxForDropdown()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistol_brands.name AS pistolMfr,
                        pistols.model as pistol_model, pistols.slug,
                        flx.id, flx.model AS flx_model, flx.name,
                        flx.price, flx.price_dealer, flx.price_partner, flx.upc,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.id ASC SEPARATOR ', ') AS pistol_models
                    FROM flx
                    INNER JOIN gtoflx
                    	ON gtoflx.id = flx.gtoflx_id
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistols.id = pistol_gtoflx_lookup.pistolid
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    GROUP BY flx.id
                    ORDER BY pistol_brands.name, flx.model";
            $stmt = $db->query($sql);
            $flx = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
            return $flx;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves all FLX records for replace tab
     *
     * @return Object   The FLX records
     */
    public static function getAllFlxForAdmin()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistol_brands.name AS pistolMfr,
                        pistols.model as pistol_model, pistols.slug,
                        flx.id AS flx_id, flx.model AS flx_model, flx.mvc_model, flx.name,
                        flx.price, flx.price_dealer, flx.price_partner, flx.upc,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.id ASC SEPARATOR ', ') AS pistol_models
                    FROM flx
                    INNER JOIN gtoflx
                    	ON gtoflx.id = flx.gtoflx_id
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistols.id = pistol_gtoflx_lookup.pistolid
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    GROUP BY flx.id
                    ORDER BY flx.id";
            $stmt = $db->query($sql);
            $flx = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
            return $flx;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    /**
     * retrieve single FLX record by ID
     * @param  INT      $id     the ID of the record
     * @return Object           the FLX record
     */
    public static function getFlx($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistol_brands.name AS pistolMfr,
                        pistols.model as pistol_model,
                        flx.id, flx.mvc_model, flx.name, flx.model AS flx_model,
                        flx.price, flx.price_dealer, flx.price_partner,
                        flx.weight, flx.ad_blurb, flx.feature01, flx.feature02,
                        flx.feature03, flx.patent_pend, flx.upc, flx.buy_link,
                        flx.special_message,
                        gtoflx_images.raw, gtoflx_images.fullsize, gtoflx_images.med,
                        gtoflx_images.small, gtoflx_images.thumb,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.id ASC SEPARATOR ', ') AS pistol_models
                    FROM flx
                    INNER JOIN gtoflx
                    	ON gtoflx.id = flx.gtoflx_id
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN gtoflx_images
                    	ON gtoflx.id = gtoflx_images.gtoflx_id
                    INNER JOIN pistols
                    	ON pistols.id = pistol_gtoflx_lookup.pistolid
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN flx_images
                        ON flx_images.flx_id = flx.id
                    WHERE flx.id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $flx = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $flx;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieve all FLX records & display in View
     * @param  INT      $id     the ID of the brand
     * @return View   All FLX records
     */
    public static function byBrand($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistol_brands.name AS pistolMfr,
                        pistols.model as pistol_model, pistols.slug,
                        flx.id AS flx_id, flx.model AS flx_model, flx.price
                    FROM flx
                    INNER JOIN gtoflx
                    	ON gtoflx.id = flx.gtoflx_id
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistols.id = pistol_gtoflx_lookup.pistolid
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE pistol_brands.id = :id
                    ORDER BY pistols.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $flx = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
            return $flx;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieve pistol Mfr names for FLX products
     * @return Object       the names
     */
    public static function getBrandsServed()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistol_brands.id, pistol_brands.name AS pistolMfr
                    FROM flx
                    INNER JOIN gtoflx
                    	ON gtoflx.id = flx.gtoflx_id
                    INNER JOIN pistol_gtoflx_lookup
                    	ON gtoflx.id = pistol_gtoflx_lookup.gtoflxid
                    INNER JOIN pistols
                    	ON pistols.id = pistol_gtoflx_lookup.pistolid
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    ORDER BY pistol_brands.name";
            $stmt = $db->query($sql);
            $flx_brands = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
            return $flx_brands;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



// = = = = = = refactored updates above = = = = = = = = = = = = = = = = = = = //




    public static function getPistolMatch($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT pistols.pistol_brand_name, pistols.pistol_model_name
                    FROM pistols
                    INNER JOIN pistolflx
                    ON pistols.id =  pistolid
                    INNER JOIN flx
                    ON flx.id = flxid
                    WHERE flx.id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $match = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Flxs Controller
            return $match;
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
     * @return object              The lasers
     */
    public static function getFlxByModel($laser_model)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM flx
                    WHERE laser_model LIKE '$laser_model%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $flxs = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Admin/Flxs Controller
            return $flxs;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * posts new FLX to flx table
     *
     * @return boolean
     */
    public static function postNewFlx()
    {
        $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
        $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
        $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
        $laser_model = ( isset($_REQUEST['laser_model']) ) ? filter_var($_REQUEST['laser_model']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $patent_pend = ( isset($_REQUEST['patent_pend']) ) ? filter_var($_REQUEST['patent_pend']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO flx SET
                    image           = :image,
                    image_full_size = :image_full_size,
                    laser_series    = :laser_series,
                    laser_model     = :laser_model,
                    price           = :price,
                    ad_blurb        = :ad_blurb,
                    feature01       = :feature01,
                    feature02       = :feature02,
                    feature03       = :feature03,
                    patent_pend     = :patent_pend,
                    upc             = :upc,
                    buy_link        = :buy_link,
                    special_message = :special_message";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':image'           => $image,
                ':image_full_size' => $image_full_size,
                ':laser_series'    => $laser_series,
                ':laser_model'     => $laser_model,
                ':price'           => $price,
                ':ad_blurb'        => $ad_blurb,
                ':feature01'       => $feature01,
                ':feature02'       => $feature02,
                ':feature03'       => $feature03,
                ':patent_pend'     => $patent_pend,
                ':upc'             => $upc,
                ':buy_link'        => $buy_link,
                ':special_message' => $special_message
            ];
            $result = $stmt->execute($parameters);

            // return result to Main Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * deletes laser based on ID
     *
     * @param  Int $id  The laser ID
     *
     * @return boolean
     */
    public static function deleteFlx($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM flx
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            // return to Main Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves flx by ID
     *
     * @param  Int $id  The laser's ID
     *
     * @return Object   The laser data
     */
    public static function getFlxById($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM flx
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $flx = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Admin/Flxs Controller
            return $flx;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * posts new FLX to flx table
     *
     * @return boolean
     */
    public static function updateFlx($id)
    {
        // retrieve form data
        $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
        $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
        $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
        $laser_model = ( isset($_REQUEST['laser_model']) ) ? filter_var($_REQUEST['laser_model']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $patent_pend = ( isset($_REQUEST['patent_pend']) ) ? filter_var($_REQUEST['patent_pend']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE flx SET
                    image           = :image,
                    image_full_size = :image_full_size,
                    laser_series    = :laser_series,
                    laser_model     = :laser_model,
                    price           = :price,
                    ad_blurb        = :ad_blurb,
                    feature01       = :feature01,
                    feature02       = :feature02,
                    feature03       = :feature03,
                    patent_pend     = :patent_pend,
                    upc             = :upc,
                    buy_link        = :buy_link,
                    special_message = :special_message
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'              => $id,
                ':image'           => $image,
                ':image_full_size' => $image_full_size,
                ':laser_series'    => $laser_series,
                ':laser_model'     => $laser_model,
                ':price'           => $price,
                ':ad_blurb'        => $ad_blurb,
                ':feature01'       => $feature01,
                ':feature02'       => $feature02,
                ':feature03'       => $feature03,
                ':patent_pend'     => $patent_pend,
                ':upc'             => $upc,
                ':buy_link'        => $buy_link,
                ':special_message' => $special_message
            ];
            $result = $stmt->execute($parameters);

            // return result to Main Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }
}
