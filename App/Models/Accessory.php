<?php

namespace App\Models;

use PDO;


class Accessory extends \Core\Model
{

    public static function getAccessory($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM accessories
                    WHERE id = :id";
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
     * get targets by known IDs
     *
     * @return object  The targets
     */
    public static function getTargets()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM accessories
                    WHERE id IN (6001, 6002)";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $targets = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Targets Controller
            return $targets;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getAccessories()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM accessories";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $accessories = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $accessories

            ;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



//  = = = = = refactored updates above  = = = = = = = = = = = = = = = = = = =









    /**
     * retrieves accessories based on name field
     *
     * @param  string $name The accessory name
     *
     * @return object       The accessories
     */
    public static function getAccessoryByName($name)
    {
        // echo $name; exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM accessories
                    WHERE name LIKE '$name%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $accessories = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Controller
            return $accessories;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * posts new accessory to accessories table
     *
     * @return boolean
     */
    public static function postNewAccessory()
    {
        $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
        $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
        $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
        $name = ( isset($_REQUEST['name']) ) ? filter_var($_REQUEST['name']): '';
        $description = ( isset($_REQUEST['description']) ) ? filter_var($_REQUEST['description']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO accessories SET
                    image           = :image,
                    image_full_size = :image_full_size,
                    laser_series    = :laser_series,
                    name            = :name,
                    price           = :price,
                    ad_blurb        = :ad_blurb,
                    feature01       = :feature01,
                    feature02       = :feature02,
                    feature03       = :feature03,
                    upc             = :upc,
                    buy_link        = :buy_link,
                    special_message = :special_message";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':image'           => $image,
                ':image_full_size' => $image_full_size,
                ':laser_series'    => $laser_series,
                ':name'            => $name,
                ':price'           => $price,
                ':ad_blurb'        => $ad_blurb,
                ':feature01'       => $feature01,
                ':feature02'       => $feature02,
                ':feature03'       => $feature03,
                ':upc'             => $upc,
                ':buy_link'        => $buy_link,
                ':special_message' => $special_message
            ];
            $result = $stmt->execute($parameters);

            // return result to Admin/Accessories Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves accessory by ID
     *
     * @param  Int $id  The accessory's ID
     *
     * @return Object   The accessory data
     */
    public static function getAccessoryById($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM accessories
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $accessory = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Admin/Accessories Controller
            return $accessory;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * updates accessory record by ID
     *
     * @return boolean
     */
    public static function updateAccessory()
    {
        // retrieve form data
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id']): '';
        $image = ( isset($_REQUEST['image']) ) ? filter_var($_REQUEST['image']): '';
        $image_full_size = ( isset($_REQUEST['image_full_size']) ) ? filter_var($_REQUEST['image_full_size']): '';
        $laser_series = ( isset($_REQUEST['laser_series']) ) ? filter_var($_REQUEST['laser_series']): '';
        $name = ( isset($_REQUEST['name']) ) ? filter_var($_REQUEST['name']): '';
        $description = ( isset($_REQUEST['description']) ) ? filter_var($_REQUEST['description']): '';
        $price = ( isset($_REQUEST['price']) ) ? filter_var($_REQUEST['price']): '';
        $ad_blurb = ( isset($_REQUEST['ad_blurb']) ) ? filter_var($_REQUEST['ad_blurb']): '';
        $feature01 = ( isset($_REQUEST['feature01']) ) ? filter_var($_REQUEST['feature01']): '';
        $feature02 = ( isset($_REQUEST['feature02']) ) ? filter_var($_REQUEST['feature02']): '';
        $feature03 = ( isset($_REQUEST['feature03']) ) ? filter_var($_REQUEST['feature03']): '';
        $upc = ( isset($_REQUEST['upc']) ) ? filter_var($_REQUEST['upc']): '';
        $buy_link = ( isset($_REQUEST['buy_link']) ) ? filter_var($_REQUEST['buy_link']): '';
        $special_message = ( isset($_REQUEST['special_message']) ) ? filter_var($_REQUEST['special_message']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE accessories SET
                    image           = :image,
                    image_full_size = :image_full_size,
                    laser_series    = :laser_series,
                    name            = :name,
                    price           = :price,
                    ad_blurb        = :ad_blurb,
                    feature01       = :feature01,
                    feature02       = :feature02,
                    feature03       = :feature03,
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
                ':name'            => $name,
                ':price'           => $price,
                ':ad_blurb'        => $ad_blurb,
                ':feature01'       => $feature01,
                ':feature02'       => $feature02,
                ':feature03'       => $feature03,
                ':upc'             => $upc,
                ':buy_link'        => $buy_link,
                ':special_message' => $special_message
            ];
            $result = $stmt->execute($parameters);

            // return result to Admin/Accessories Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * deletes accessory based on ID
     *
     * @param  Int $id  The accessory ID
     *
     * @return boolean
     */
    public static function deleteAccessory($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM accessories
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


}
