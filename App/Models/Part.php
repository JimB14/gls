<?php

namespace App\Models;

use PDO;


class Part extends \Core\Model
{

    public static function getPart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve data
            $sql = "SELECT * FROM parts
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



    public static function getparts()
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT * FROM parts";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $parts = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $parts;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



//  = = = = =  refactored updates above  = = = = = = = = = = = = = = = = = = //

// = = =  admin functionality (not completed and tested as of 9/17/18) = = = //
    /**
     * posts new part to parts table
     *
     * @return boolean
     */
    public static function addNewPart()
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

            $sql = "INSERT INTO parts SET
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

            // return result to Admin/parts Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves part by ID
     *
     * @param  Int $id  The part's ID
     *
     * @return Object   The part data
     */
    public static function getPartById($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM parts
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $part = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Admin/parts Controller
            return $part;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * updates part record by ID
     *
     * @return boolean
     */
    public static function updatepart()
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

            $sql = "UPDATE parts SET
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

            // return result to Admin/parts Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * deletes part based on ID
     *
     * @param  Int $id  The part ID
     *
     * @return boolean
     */
    public static function deletepart($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM parts
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            // return to Admin/parts Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
