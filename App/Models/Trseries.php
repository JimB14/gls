<?php

namespace App\Models;

use PDO;


/**
 * Trseries Model
 *
 * PHP version 7.0
 */
class Trseries extends \Core\Model
{
    /**
     * retrieves brand specific products
     *
     * @param  INT      $id         The pistol brand ID
     *
     * @return Object
     */
    public static function getLasersByBrand($id)
    {
        // echo "Connected to getLasersByBrand() in Trseries Model!"; exit();

        // if glock is selected
        if ($id == 9 || $id == 17)
        {
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql =  "SELECT DISTINCT
                    	   trseries.id, trseries.series, trseries.model, trseries.beam,
                           pistols.slug,
                           pistol_brands.name AS pistolMfr,
                           trseries_images.thumb,
                           GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                           GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                        FROM trseries
                        INNER JOIN pistol_trseries_lookup
                        	ON trseries.id = pistol_trseries_lookup.trseriesid
                        INNER JOIN pistols
                        	ON pistol_trseries_lookup.pistolid = pistols.id
                        INNER JOIN pistol_brands
                        	ON pistol_brands.id = pistols.brand_id
                        INNER JOIN trseries_images
                        	ON trseries_images.trseries_id = trseries.id
                        WHERE pistols.brand_id = $id
                        GROUP BY trseries.model
                        ORDER BY trseries.id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id' => $id
                ];
                $stmt->execute($parameters);
                $trseries = $stmt->fetchAll(PDO::FETCH_OBJ);

                // return to Controller
                return $trseries;
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

                $sql =  "SELECT DISTINCT
                    	   trseries.id, trseries.series, trseries.model, trseries.beam,
                           pistols.slug,
                           pistol_brands.name AS pistolMfr,
                           trseries_images.thumb,
                           GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '<br>') AS pistol_models,
                           GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                        FROM trseries
                        INNER JOIN pistol_trseries_lookup
                        	ON trseries.id = pistol_trseries_lookup.trseriesid
                        INNER JOIN pistols
                        	ON pistol_trseries_lookup.pistolid = pistols.id
                        INNER JOIN pistol_brands
                        	ON pistol_brands.id = pistols.brand_id
                        INNER JOIN trseries_images
                        	ON trseries_images.trseries_id = trseries.id
                        WHERE pistols.brand_id = $id
                        GROUP BY trseries.model
                        ORDER BY trseries.id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id' => $id
                ];
                $stmt->execute($parameters);
                $trseries = $stmt->fetchAll(PDO::FETCH_OBJ);

                // return to Controller
                return $trseries;
            }
            catch(PDOException $e)
            {
                echo $e->getMesssage();
                exit();
            }
        }

    }



    /**
     * retrieves all TR Series lasers
     *
     * @return object  All lasers in trseries table
     */
    public static function getLasers()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // $sql = "SELECT * FROM trseries";

            $sql =  "SELECT DISTINCT
                    	trseries.id, trseries.mvc_model, trseries.name, trseries.series,
                        trseries.model,trseries.price, trseries.price_dealer,
                        trseries.price_partner, trseries.beam, trseries_images.thumb as img_thumb,
                        trseries.upc,
                        pistol_brands.name as pistolMfr,
                        pistols.slug,
                    GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                    GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                    FROM trseries
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
     * retrieves laser by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getTrseriesLaser($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistols.id AS pistol_id,
                        pistol_brands.name AS pistolMfr,
                        pistols.slug,
                        trseries.*,
                        trseries_dealer_links.trseries_id,
                        trseries_dealer_links.opticsplanet, trseries_dealer_links.amazon,
                        trseries_dealer_links.midwayusa, trseries_dealer_links.buds,
                        trseries_images.id AS images_id, trseries_images.fullsize,
                        trseries_images.med, trseries_images.small, trseries_images.thumb,
                        trseries_images.alt_color_thumb,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                    FROM pistols
                    INNER JOIN pistol_trseries_lookup
                    	ON pistol_trseries_lookup.pistolid = pistols.id
                    INNER JOIN trseries
                    	ON trseries.id = pistol_trseries_lookup.trseriesid
                    -- Must use LEFT JOIN if every row in trseries_dealer_links
                    -- does not have a value for every row in trseries
                    LEFT JOIN trseries_dealer_links
                    	ON trseries.id = trseries_dealer_links.trseries_id
                    INNER JOIN trseries_images
                    	ON trseries_images.trseries_id = trseries.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE trseries.id = $id
                    GROUP BY trseries.model";
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
     * retrieves product links by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getTrseriesLinks($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT
                        batteries.image_thumb as battery_image_thumb,
                        toolkits.image_thumb as toolkit_image_thumb,
                        trseries_product_links.*
                    FROM trseries_product_links
                    INNER JOIN batteries
                        ON batteries.id = trseries_product_links.battery_id
                    INNER JOIN toolkits
                        ON toolkits.id = trseries_product_links.toolkit_id
                    WHERE trseries_product_links.trseries_id = :id";
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
    public static function getTrseriesDetailsForCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        trseries.id, trseries.name, trseries.series, trseries.model,
                        trseries.beam, trseries.price, trseries.price_dealer, trseries.weight,
                        trseries_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_trseries_lookup
                    	ON pistol_trseries_lookup.pistolid = pistols.id
                    INNER JOIN trseries
                    	ON trseries.id = pistol_trseries_lookup.trseriesid
                    INNER JOIN trseries_images
                    	ON trseries_images.trseries_id = trseries.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE trseries.id = :id
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




    /**
     * retrieves laser field items for Dealer shopping cart by ID
     *
     * @param  string  $laserId     The laser model ID
     *
     * @return object               The laser record
     */
    public static function getTrseriesDetailsForDealerCart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                        trseries.id, trseries.name, trseries.series, trseries.model,
                        trseries.beam, trseries.price_dealer, trseries.price_partner, trseries.weight,
                        trseries_images.thumb,
                        pistol_brands.name AS pistolMfr,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models
                    FROM pistols
                    INNER JOIN pistol_trseries_lookup
                    	ON pistol_trseries_lookup.pistolid = pistols.id
                    INNER JOIN trseries
                    	ON trseries.id = pistol_trseries_lookup.trseriesid
                    INNER JOIN trseries_images
                    	ON trseries_images.trseries_id = trseries.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE trseries.id = :id
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
                    	trseries.id, trseries.mvc_model, trseries.name, trseries.series,
                        trseries.model,trseries.price, trseries.price_dealer,
                        trseries.price_partner, trseries.beam, trseries_images.thumb as img_thumb,
                        trseries.upc,
                        pistol_brands.name as pistolMfr,
                        pistols.slug,
                    GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                    GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                    FROM trseries
                    INNER JOIN pistol_trseries_lookup
                    	ON trseries.id = pistol_trseries_lookup.trseriesid
                    INNER JOIN pistols
                    	ON pistol_trseries_lookup.pistolid = pistols.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    INNER JOIN trseries_images
                    	ON trseries_images.trseries_id = trseries.id
                    WHERE trseries.model LIKE '$laser_model%'
                    GROUP BY trseries.model
                    ORDER BY trseries.id";
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

            $sql = "SELECT trseries.*,
                    trseries_images.id as imageId, trseries_images.raw, trseries_images.fullsize,
                    trseries_images.med, trseries_images.small, trseries_images.thumb,
                    trseries_images.alt_color_thumb
                    FROM trseries
                    INNER JOIN trseries_images
                        ON trseries_images.trseries_id = trseries.id";
            $stmt = $db->query($sql);
            $trseries = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $trseries;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }







    /**
     * retrieves laser by laser ID
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

            $sql = "SELECT trseries.*,
                    trseries_images.id as imageId, trseries_images.raw, trseries_images.fullsize,
                    trseries_images.med, trseries_images.small, trseries_images.thumb,
                    trseries_images.alt_color_thumb
                    FROM trseries
                    INNER JOIN trseries_images
                        ON trseries_images.trseries_id = trseries.id
                    WHERE trseries.id = :id";
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
     * Retrieves lasers for warranty registration drop-down
     * @return Object   The trseries lasers records
     */
    public static function getLasersForWarrantyRegistrationDropdown()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT id, name, beam
                    FROM trseries
                    ORDER BY id";
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









    public static function byPistolId($id)
    {
        // echo "Connected."; exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT
                    	pistols.id AS pistol_id,
                        pistol_brands.name AS pistolMfr,
                        pistols.slug,
                        trseries.*,
                        trseries_dealer_links.trseries_id,
                        trseries_dealer_links.opticsplanet, trseries_dealer_links.amazon,
                        trseries_dealer_links.midwayusa,
                        trseries_images.id AS images_id, trseries_images.fullsize,
                        trseries_images.med, trseries_images.small, trseries_images.thumb,
                        trseries_images.alt_color_thumb,
                    	GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR ', ') AS pistol_models,
                        GROUP_CONCAT(DISTINCT pistols.model ORDER BY pistols.model ASC SEPARATOR '-') AS pistol_models_href
                    FROM pistols
                    INNER JOIN pistol_trseries_lookup
                    	ON pistol_trseries_lookup.pistolid = pistols.id
                    INNER JOIN trseries
                    	ON trseries.id = pistol_trseries_lookup.trseriesid
                    -- Must use LEFT JOIN if every row in trseries_dealer_links
                    -- does not have a value for every row in trseries
                    LEFT JOIN trseries_dealer_links
                    	ON trseries.id = trseries_dealer_links.trseries_id
                    INNER JOIN trseries_images
                    	ON trseries_images.trseries_id = trseries.id
                    INNER JOIN pistol_brands
                    	ON pistol_brands.id = pistols.brand_id
                    WHERE pistols.id = $id
                    GROUP BY trseries.model";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $laser = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $laser;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }






    // - - - - - ADMIN  - - - - - - - - - - - - - - - - - - - - - - - - - - - //

    /**
     * Updates record in trseries table
     *
     * @param  INTEGER   $id    The id of the record
     * @return Boolean          Success or failure
     */
    public static function updateLaser($id)
    {
        //  retrieve form data
        $name = ( isset($_REQUEST['name']) ) ? filter_var($_REQUEST['name']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $patent_pend = ( isset($_REQUEST['patent_pend']) ) ? filter_var($_REQUEST['patent_pend']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $review_url = ( isset($_REQUEST['review_url']) ) ? filter_var($_REQUEST['review_url']): '';
        $review_count = ( isset($_REQUEST['review_count']) ) ? filter_var($_REQUEST['review_count']): '';
        $special_note = ( isset($_REQUEST['special_note']) ) ? filter_var($_REQUEST['special_note']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE trseries SET
                    name            = :name,
                    price           = :price,
                    ad_blurb        = :ad_blurb,
                    feature01       = :feature01,
                    feature02       = :feature02,
                    feature03       = :feature03,
                    patent_pend     = :patent_pend,
                    upc             = :upc,
                    review_url      = :review_url,
                    review_count    = :review_count,
                    special_note    = :special_note,
                    special_message = :special_message
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'              => $id,
                ':name'            => $name,
                ':price'           => $price,
                ':ad_blurb'        => $ad_blurb,
                ':feature01'       => $feature01,
                ':feature02'       => $feature02,
                ':feature03'       => $feature03,
                ':patent_pend'     => $patent_pend,
                ':upc'             => $upc,
                ':review_url'      => $review_url,
                ':review_count'    => $review_count,
                ':special_note'    => $special_note,
                ':special_message' => $special_message
            ];
            $result = $stmt->execute($parameters);

            // return to Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }






//  = = = = refactored updates above = = = = = = = = = = = = = = = = = = = = //






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
    public static function getLasersApi()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT id, laser_series, laser_model, laser_color, price,
                     image_full_size, image_med, image_small, image_thumb,
                     ad_blurb, feature01, feature02,feature03, special_message,
                     mfr_name
                     -- , mfr_model_match
                     FROM lasers
                     ORDER BY laser_series, laser_model";
            $stmt = $db->query($sql);
            $lasers = $stmt->fetchALL(PDO::FETCH_ASSOC);

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
   * retrieves all lasers for Admin
   *
   * @return object All lasers in lasers table
   */
   public static function getLaserModelsByColorAndSeriesAdmin()
   {
      // retrieve data from Ajax post
      $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series'], FILTER_SANITIZE_STRING) : '';
      $color = ( isset($_REQUEST['color']) ) ? filter_var($_REQUEST['color'], FILTER_SANITIZE_STRING) : '';

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT laser_model FROM lasers
                  WHERE laser_color = :laser_color
                  AND laser_series  = :laser_series
                  ORDER BY alpha_name";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':laser_series' => $laser_series,
            ':laser_color'  => $color
         ];
         $stmt->execute($parameters);

         $laser_models = $stmt->fetchALL(PDO::FETCH_OBJ);

         // return to Controller
         return $laser_models;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }







   public static function getMfrNameByLaserId($laser_id)
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT laser_model, laser_color, mfr_name FROM lasers
                 WHERE id = :id";
         $stmt = $db->prepare($sql);
         $parameters =  [
            ':id' => $laser_id
         ];
         $stmt->execute($parameters);

         $results = $stmt->fetch(PDO::FETCH_OBJ);

         // return to Holsters controller
         return $results;
      }
      catch (PDOException $e)
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
    * retrieves lasers by pistol ID
    *
    * @param  Int $id  The laser's ID
    *
    * @return Object   The laser data
    */
  public static function getLaserByPistolId($id)
  {
      try
      {
           // establish db connection
           $db = static::getDB();

           $sql = "SELECT lasers.id as laserId, lasers.image_thumb as laser_image_thumb,
                   lasers.laser_series, lasers.laser_model, lasers.mfr_name,
                   lasers.laser_color, lasers.mfr_model_match FROM lasers
                   INNER JOIN pistollaser ON
                   pistollaser.laserid = lasers.id
                   INNER JOIN pistols ON
                   pistollaser.pistolid = pistols.id
                   WHERE pistollaser.pistolid = :id";
           $stmt = $db->prepare($sql);
           $parameters = [
               ':id' => $id
           ];
           $stmt->execute($parameters);

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




}
