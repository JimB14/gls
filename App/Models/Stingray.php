<?php

namespace App\Models;

use PDO;


/**
 * Laser Model
 *
 * PHP version 7.0
 */
class Stingray extends \Core\Model
{

    /**
     * retrieves brand specific products
     *
     * @param  INT      $id     The pistol brand ID
     *
     * @return Object
     */
    public static function getLasersByBrand($id)
    {
        // echo "Connected to getLasersByBrand() in Gtoflx Model!"; exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                	   stingrays.id, stingrays.series, stingrays.model,
                       pistol_brands.name AS pistolMfr,
                       stingray_images.thumb,
                       GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM stingrays
                    INNER JOIN pistol_stingray_lookup
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN pistols
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN stingray_images
                    	ON stingray_images.stingray_id = stingrays.id
                    WHERE pistols.brand_id = $id
                    GROUP BY stingrays.model
                    ORDER BY stingrays.id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $stingray = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $stingray;
        }
        catch(PDOException $e)
        {
            echo $e->getMesssage();
            exit();
        }
    }




    /**
     * return Stingrays for logged in Dealer
     *
     * @return Object   The lasers
     */
    public static function getLasers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        stingrays.id, stingrays.mvc_model, stingrays.name, stingrays.series,
                        stingrays.model, stingrays.beam, stingrays.price, stingrays.price_dealer,
                        stingrays.price_partner, stingray_images.thumb AS img_thumb,
                        stingrays.upc,
                        pistols.id AS pistol_id, pistols.slug,
                        pistol_brands.name AS pistolMfr,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_hrefs
                    FROM stingrays
                    INNER JOIN pistol_stingray_lookup
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN pistols
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN stingray_images
                    	ON stingray_images.stingray_id = stingrays.id
                    GROUP BY stingrays.model
                    ORDER BY stingrays.id
                    LIMIT 1";
            $stmt = $db->prepare($sql);

            $stmt->execute();
            $stingrays = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $stingrays;
        }
        catch(PDOException $e)
        {
            echo $e->getMesssage();
            exit();
        }
    }




    public static function getPistolMatches()
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        pistol_brands.name AS pistolMfr,
                        pistols.id AS pistol_id, pistols.model AS pistol_model,
                        stingrays.model, stingrays.id AS stingray_id
                    FROM stingrays
                    INNER JOIN pistol_stingray_lookup
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN pistols
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    ORDER BY pistol_brands.name, pistols.model";
            $stmt = $db->prepare($sql);

            $stmt->execute();
            $matches = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $matches;
        }
        catch(PDOException $e)
        {
            echo $e->getMesssage();
            exit();
        }
    }



    /**
     * retrieves stringray laser record by ID
     *
     * @param  INT  $id     The laser model ID
     *
     * @return Object      The laser record
     */
    public static function getStingrayLaser($id, $pistolMfr)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        pistols.id AS pistol_id,
                        pistol_brands.id as pistolMfr_id, pistol_brands.name AS pistolMfr,
                        stingrays.*,
                        stingray_images.id AS images_id, stingray_images.fullsize,
                        stingray_images.med, stingray_images.small, stingray_images.thumb,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_stingray_lookup
                        ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN stingrays
                        ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN stingray_images
                        ON stingray_images.stingray_id = stingrays.id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    WHERE stingrays.id = :id
                    AND pistol_brands.name = :pistolMfr
                    GROUP BY stingrays.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'        => $id,
                ':pistolMfr' => $pistolMfr
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
     * retrieves stingray product links from `lasers_product_links`
     *
     * @param  Int    $id   The stingray laser ID
     *
     * @return Object       Stingray laser details
     */
    public static function getStingrayProductLinks($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT stingray_product_links.*,
                    batteries.image_thumb as battery_image_thumb,
                    toolkits.image_thumb as toolkit_image_thumb
                    FROM stingray_product_links
                    INNER JOIN batteries
                        ON batteries.id = stingray_product_links.battery_id
                    INNER JOIN toolkits
                        ON toolkits.id = stingray_product_links.toolkit_id
                    WHERE stingray_id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $productLinks = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Stingrays Controller
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
    public static function getStingrayDetailsForCart($id, $pistolMfr)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        stingrays.id, stingrays.name, stingrays.series, stingrays.model,
                        stingrays.beam, stingrays.price, stingrays.price_dealer,
                        stingrays.price_partner, stingrays.weight,
                        stingray_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_stingray_lookup
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN stingrays
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN stingray_images
                    	ON stingray_images.stingray_id = stingrays.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE stingrays.id = :id
                    AND pistol_brands.name = :pistolMfr
                    GROUP BY stingrays.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'        => $id,
                ':pistolMfr' => $pistolMfr
            ];
            $stmt->execute($parameters);

            $item = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Cart controller
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
    public static function getStingrayDetailsForDealerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        stingrays.id, stingrays.name, stingrays.series, stingrays.model,
                        stingrays.beam, stingrays.price_dealer, stingrays.price_partner,
                        stingrays.weight, stingray_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_stingray_lookup
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN stingrays
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN stingray_images
                    	ON stingray_images.stingray_id = stingrays.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE stingrays.id = :id
                    GROUP BY stingrays.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $item = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $item;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves laser field items for Admin shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getStingrayDetailsForAdminCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        stingrays.id, stingrays.name, stingrays.series, stingrays.model,
                        stingrays.beam, stingrays.price, stingrays.price_dealer,
                        stingrays.price_partner, stingrays.weight, stingray_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_stingray_lookup
                    	ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN stingrays
                    	ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN stingray_images
                    	ON stingray_images.stingray_id = stingrays.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE stingrays.id = :id
                    GROUP BY stingrays.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $item = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
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

            // retrieve data
            $sql = "SELECT id, name, beam FROM stingrays";
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
     * retrieves stringray laser record by pistol ID
     *
     * @param  INT  $id     The pistol model ID
     *
     * @return Object       The laser record
     */
    public static function byPistolId($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        pistols.id AS pistol_id,
                        pistol_brands.id as pistolMfr_id, pistol_brands.name AS pistolMfr,
                        stingrays.*,
                        stingray_images.id AS images_id, stingray_images.fullsize,
                        stingray_images.med, stingray_images.small, stingray_images.thumb,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_stingray_lookup
                        ON pistol_stingray_lookup.pistolid = pistols.id
                    INNER JOIN stingrays
                        ON stingrays.id = pistol_stingray_lookup.stingrayid
                    INNER JOIN stingray_images
                        ON stingray_images.stingray_id = stingrays.id
                    INNER JOIN pistol_brands
                        ON pistol_brands.id = pistols.brand_id
                    WHERE pistols.id = :id
                    GROUP BY stingrays.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'        => $id
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







    // = = = =  refactored updates above = = = = = = = = = = = = = = = = = = //





    /**
     * retrieves stingray record by brand name
     *
     * @param  String   $mfr_name   The pistol brand name
     * @return Object               The Stingray record
     */
    public static function getStingrayRecordByBrand($mfr_name)
    {

        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM stingrays
                    WHERE mfr_name = :mfr_name";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':mfr_name' => $mfr_name
            ];
            $stmt->execute($parameters);
            $stingray = $stmt->fetch(PDO::FETCH_OBJ);

            return $stingray;
        }
        catch(PDOException $e)
        {
            echo $e->message();
            exit();
        }
    }



    /**
     * retrieves laser model record
     *
     * @param  INT  $id   The stingray ID
     *
     * @return Object     Laser model details
     */
    public static function getModelDetails($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM stingrays
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $model = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Stingray Controller
            return $model;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }










    /**
     * retrieves laser by series, laser beam color (does not use pistollaser lookup table)
     * @param  string   $series   The laser series
     * @param  string   $color    The laser beam color
     * @param  string   $orderby  The sort order instructions
     * @param  integer  $limit    The max number of records
     * @return object             The laser records
     */
    public static function getLasersByCriteria($series, $color, $orderby, $limit)
    {
        if ($series != '') {
            $series = $series;
        } else {
            $series = '';
        }

        if ($orderby != '') {
            $orderby = 'ORDER BY ' . $orderby;
        } else {
            $orderby = '';
        }

        if ($limit != '') {
            $limit = 'LIMIT ' . $limit;
        } else {
            $limit = '';
        }

        if ($color != '') {
            $color = $color;
        } else {
            $color = '';
        }

        // test
        // echo $series  . '<br>';
        // echo $orderby . '<br>';
        // echo $limit   . '<br>';
        // echo $color   . '<br>';
        //exit();

        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM lasers
                    WHERE laser_series = $series
                    $color
                    $orderby
                    $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $models = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $models;
        }
        catch(PDOException $e)
        {
            echo $e->message();
            exit();
        }
    }










    /**
     * retrieves pistol-laser matches
     *
     * @param  Int $laser_id The laser ID
     * @return object        The matches
     */
    public static function getPistolLaserMatches($laser_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT pistols.id, pistols.pistol_brand_name,
                    pistols.pistol_model_name,
                    lasers.laser_model, lasers.laser_color, lasers.id
                    FROM pistols
                    INNER JOIN pistollaser
                    ON pistols.id = pistolid
                    INNER JOIN lasers
                    ON lasers.id = laserid
                    WHERE lasers.id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $laser_id
            ];
            $stmt->execute($parameters);
            $matches = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Lasers Controller
            return $matches;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves all lasers
     *
     * @return object All lasers in lasers table
     */
    public static function getAllLasers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM lasers";
            $stmt = $db->query($sql);
            $lasers = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Main Controller
            return $lasers;
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
    public static function getLaser($laser_model)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM lasers
                    WHERE laser_model LIKE '$laser_model%'";
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



    /**
     * posts new laser to lasers table
     *
     * @return boolean
     */
    public static function postNewLaser()
    {
        $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
        $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
        $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
        $laser_model = ( isset($_REQUEST['laser_model']) ) ? filter_var($_REQUEST['laser_model']): '';
        $laser_color = ( isset($_REQUEST['laser_color']) ) ? filter_var($_REQUEST['laser_color']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $patent_pend = ( isset($_REQUEST['patent_pend']) ) ? filter_var($_REQUEST['patent_pend']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
        $review_url = ( isset($_REQUEST['review_url']) ) ? filter_var($_REQUEST['review_url']): '';
        $review_count = ( isset($_REQUEST['review_count']) ) ? filter_var($_REQUEST['review_count']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';
        $battery = ( isset($_REQUEST['battery']) ) ? filter_var($_REQUEST['battery']): '';
        $tool_kit = ( isset($_REQUEST['tool_kit']) ) ? filter_var($_REQUEST['tool_kit']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO lasers SET
                    image           = :image,
                    image_full_size = :image_full_size,
                    laser_series    = :laser_series,
                    laser_model     = :laser_model,
                    laser_color     = :laser_color,
                    price           = :price,
                    ad_blurb        = :ad_blurb,
                    feature01       = :feature01,
                    feature02       = :feature02,
                    feature03       = :feature03,
                    patent_pend     = :patent_pend,
                    upc             = :upc,
                    buy_link        = :buy_link,
                    review_url      = :review_url,
                    review_count    = :review_count,
                    special_message = :special_message,
                    battery         = :battery,
                    tool_kit        = :tool_kit";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':image'           => $image,
                ':image_full_size' => $image_full_size,
                ':laser_series'    => $laser_series,
                ':laser_model'     => $laser_model,
                ':laser_color'     => $laser_color,
                ':price'           => $price,
                ':ad_blurb'        => $ad_blurb,
                ':feature01'       => $feature01,
                ':feature02'       => $feature02,
                ':feature03'       => $feature03,
                ':patent_pend'     => $patent_pend,
                ':upc'             => $upc,
                ':buy_link'        => $buy_link,
                ':review_url'      => $review_url,
                ':review_count'    => $review_count,
                ':special_message' => $special_message,
                ':battery'         => $battery,
                ':tool_kit'        => $tool_kit
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
     * deletes laser base on ID
     *
     * @param  Int $id  The laser ID
     *
     * @return boolean
     */
    public static function deleteLaser($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM lasers
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
     * retrieves laser by ID
     *
     * @param  Int $id  The laser's ID
     *
     * @return Object   The laser data
     */
    public static function getLaserById($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM lasers
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $laser = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Main Controller
            return $laser;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }

    }



    /**
     * updates laser table record
     *
     * @return boolean
     */
    public static function updateLaser($id)
    {
        //  retrieve form data
        $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
        $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
        $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
        $laser_model = ( isset($_REQUEST['laser_model']) ) ? filter_var($_REQUEST['laser_model']): '';
        $laser_color = ( isset($_REQUEST['laser_color']) ) ? filter_var($_REQUEST['laser_color']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $patent_pend = ( isset($_REQUEST['patent_pend']) ) ? filter_var($_REQUEST['patent_pend']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
        $review_url = ( isset($_REQUEST['review_url']) ) ? filter_var($_REQUEST['review_url']): '';
        $review_count = ( isset($_REQUEST['review_count']) ) ? filter_var($_REQUEST['review_count']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';
        $battery = ( isset($_REQUEST['battery']) ) ? filter_var($_REQUEST['battery']): '';
        $tool_kit = ( isset($_REQUEST['tool_kit']) ) ? filter_var($_REQUEST['tool_kit']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE lasers SET
                    image           = :image,
                    image_full_size = :image_full_size,
                    laser_series    = :laser_series,
                    laser_model     = :laser_model,
                    laser_color     = :laser_color,
                    price           = :price,
                    ad_blurb        = :ad_blurb,
                    feature01       = :feature01,
                    feature02       = :feature02,
                    feature03       = :feature03,
                    patent_pend     = :patent_pend,
                    upc             = :upc,
                    buy_link        = :buy_link,
                    review_url      = :review_url,
                    review_count    = :review_count,
                    special_message = :special_message,
                    battery         = :battery,
                    tool_kit        = :tool_kit
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'              => $id,
                ':image'           => $image,
                ':image_full_size' => $image_full_size,
                ':laser_series'    => $laser_series,
                ':laser_model'     => $laser_model,
                ':laser_color'     => $laser_color,
                ':price'           => $price,
                ':ad_blurb'        => $ad_blurb,
                ':feature01'       => $feature01,
                ':feature02'       => $feature02,
                ':feature03'       => $feature03,
                ':patent_pend'     => $patent_pend,
                ':upc'             => $upc,
                ':buy_link'        => $buy_link,
                ':review_url'      => $review_url,
                ':review_count'    => $review_count,
                ':special_message' => $special_message,
                ':battery'         => $battery,
                ':tool_kit'        => $tool_kit
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
