<?php

namespace App\Models;

use PDO;


/**
 * Laser Model
 *
 * PHP version 7.0
 */
class Laser extends \Core\Model
{




//  = = = = = refactored updates above  = = = = = = = = = = = = = = = = = = = //


    /**
     * retrieves lasers by series, laser beam color & matching pistols
     * (use only if every pistol-laser combo has an image)
     *
     * @param  string $orderby The query result order
     * @return object
     */
    public static function getLasers($series, $color, $orderby, $limit)
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
            $sql = "SELECT lasers.*, pistols.id as pistol_id, pistols.pistol_brand_id,
                    pistols.pistol_brand_name, pistols.pistol_model_name
                    FROM  pistols
                    INNER JOIN pistollaser
                    ON pistols.id = pistolid
                    INNER JOIN lasers
                    ON lasers.id = laserid
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



   public static function getLaserNew($laserId)
   {
      // retrieve data
      try
      {
         // establish db connection
         $db = static::getDB();

         //retrieve data
         $sql = "SELECT lasersnew.*, pistolmfrsnew.name as pistolMfrName, pistolmfrsnew.id as pistolMfrId,
                  GROUP_CONCAT(DISTINCT pistolsnew.model SEPARATOR ', ') AS model_matches
                  FROM lasersnew
                  INNER JOIN pistollaser ON
                  lasersnew.id = pistollaser.laserid
                  INNER JOIN pistolsnew ON
                  pistolsnew.id = pistollaser.pistolid
                  INNER JOIN pistolmfrsnew ON
                  pistolmfrsnew.id = pistolsnew.mfrid
                  WHERE lasersnew.id = :laserId";
         $stmt = $db->prepare($sql);
         $parameters = [
            'laserId' => $laserId
         ];
         $stmt->execute($parameters);
         $laser = $stmt->fetch(PDO::FETCH_OBJ);

         // return to Controller
         return $laser;
      }
      catch(PDOException $e)
      {
         echo $e->getMesssage();
         exit();
      }
   }



    /**
     * retrieves laser model details
     *
     * @param  INT   $id   The laser model ID
     *
     * @return object     Laser model details
     */
    public static function getModelDetails($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM lasers
                    INNER JOIN lasers_product_links
                    ON lasers.id = lasers_product_links.laser_id
                    WHERE lasers.id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $model = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Lasers Controller
            return $model;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves brand specific products
     *
     * @param  INT      $id         The pistol brand ID
     * @param  String   $series     The laser series
     * @param  String   $color      The laser color
     * @param  String   $orderby    The table field/column
     * @param  INT      $limit      Number of results to display
     *
     * @return Object
     */
    public static function getLasersByBrand($id, $series, $color, $orderby, $limit)
    {
        if ($series != null) {
            $series = $series;
        } else {
            $series = '';
        }

        if ($color != null) {
            $color = 'AND laser_color = ' . $color;
        } else {
            $color = '';
        }

        if ($orderby != null) {
            $orderby = 'ORDER BY ' . $orderby;
        } else {
            $orderby = '';
        }

        if ($limit != null) {
            $limit = 'LIMIT ' . $orderby;
        } else {
            $limit = '';
        }

        // retrieve data
        try
        {
            // establish db connection
            $db = static::getDB();

            //retrieve data

            $sql = "SELECT * FROM lasers
                    WHERE laser_series = $series
                    AND mfr_id = $id
                    $color
                    $orderby
                    $limit";

            // $sql = "SELECT pistols.id as pistol_id, pistol_brand_name,
            //         pistol_model_name, laser_model, lasers.id as laser_id,
            //         lasers.image
            //         FROM lasers
            //         INNER JOIN pistollaser
            //         ON lasers.id = laserid
            //         INNER JOIN pistols
            //         ON pistols.id = pistolid
            //         WHERE laser_series = $series
            //         AND pistol_brand_id = $id
            //         $orderby
            //         $limit";

            $stmt = $db->query($sql);
            $lasers = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Controller
            return $lasers;
        }
        catch(PDOException $e)
        {
            echo $e->getMesssage();
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
