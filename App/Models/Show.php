<?php

namespace App\Models;

use PDO;


class Show extends \Core\Model
{
    public static function getAllShows()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM shows
                    WHERE end >= CURDATE()
                    AND display = 1
                    ORDER BY start ASC";
            $stmt = $db->query($sql);

            $shows = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Shows Controller
            return $shows;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getShows()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT DISTINCT id, name, location, city, state
                    FROM shows
                    WHERE display = 1
                    ORDER BY state, city, location, name";
            $stmt = $db->query($sql);

            $shows = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $shows;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getAllShowsAdmin()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM shows
                    ORDER BY start DESC";
            $stmt = $db->query($sql);

            $shows = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Shows Controller
            return $shows;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getCities()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT id, city, state FROM shows
                    WHERE start >= CURDATE()
                    AND display = 1
                    ORDER BY city";
            $stmt = $db->query($sql);

            $cities = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Shows Controller
            return $cities;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getShowsByCity($city)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM shows
                    -- WHERE start >= CURDATE()
                    WHERE city LIKE '$city%'
                    AND display = 1
                    ORDER BY start";
            $stmt = $db->prepare($sql);

            $stmt->execute();
            $shows = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Controller
            return $shows;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getShowsByState($state)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM shows
                    WHERE start >= CURDATE()
                    AND state = '$state'
                    AND display = 1
                    ORDER BY start";
            $stmt = $db->prepare($sql);

            $stmt->execute();
            $shows = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Controller
            return $shows;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




   public static function postGunShow()
   {
      // echo '<pre>';
      // print_r($_REQUEST);
      // echo '</pre>';
      // exit();

      // retrieve form data
      $start = ( isset($_REQUEST['start']) ) ? filter_var($_REQUEST['start']): '';
      $end = ( isset($_REQUEST['end']) ) ? filter_var($_REQUEST['end']): '';

      $name = ( isset($_REQUEST['name']) ) ? filter_var($_REQUEST['name']): '';
      if (!isset($name) || $name == '') {
         $name = ( isset($_REQUEST['new_name']) ) ? filter_var($_REQUEST['new_name']): '';
      }
      $location = ( isset($_REQUEST['location']) ) ? filter_var($_REQUEST['location']): '';
      if (!isset($location) || $location == '') {
         $new_location = ( isset($_REQUEST['new_location']) ) ? filter_var($_REQUEST['new_location']): '';
      }
      $city = ( isset($_REQUEST['city']) ) ? filter_var($_REQUEST['city']): '';
      if (!isset($city) || $city == '') {
         $city = ( isset($_REQUEST['new_city']) ) ? filter_var($_REQUEST['new_city']): '';
      }
      $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state']): '';
      $rep = ( isset($_REQUEST['rep']) ) ? filter_var($_REQUEST['rep']): '';
      if (!isset($rep) || $rep == '') {
         $rep = ( isset($_REQUEST['new_rep']) ) ? filter_var($_REQUEST['new_rep']): '';
      }
      $show_url = ( isset($_REQUEST['show_url']) ) ? filter_var($_REQUEST['show_url']): '';
      $show_producer = ( isset($_REQUEST['show_producer']) ) ? filter_var($_REQUEST['show_producer']): '';
      if (!isset($show_producer) || $show_producer == '') {
         $show_producer = ( isset($_REQUEST['new_show_producer']) ) ? filter_var($_REQUEST['new_show_producer']): '';
      }
      $producer_url = ( isset($_REQUEST['producer_url']) ) ? filter_var($_REQUEST['producer_url']): '';
      if (!isset($producer_url) || $producer_url == '') {
         $producer_url = ( isset($_REQUEST['new_producer_url']) ) ? filter_var($_REQUEST['new_producer_url']): '';
      }
      $map = ( isset($_REQUEST['map']) ) ? filter_var($_REQUEST['map']): '';

      // append hours:min:sec to date
      $end = $end . ' 23:59:59';

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "INSERT INTO shows SET
            start           = :start,
            end             = :end,
            name            = :name,
            location        = :location,
            city            = :city,
            state           = :state,
            rep             = :rep,
            show_url        = :show_url,
            show_producer   = :show_producer,
            producer_url    = :producer_url,
            map             = :map";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':start'            => $start,
            ':end'              => $end,
            ':name'             => $name,
            ':location'         => $location,
            ':city'             => $city,
            ':state'            => $state,
            ':rep'              => $rep,
            ':show_url'         => $show_url,
            ':show_producer'    => $show_producer,
            ':producer_url'     => $producer_url,
            ':map'              => $map
         ];
         $result = $stmt->execute($parameters);

         // return result to Admin/Shows Controller
         return $result;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }




    public static function getShowById()
    {
        // retrieve id from string query
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM shows
                    WHERE id = :id
                    AND display = 1";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $show = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Main Controller
            return $show;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getShowByIdAdmin()
    {
        // retrieve id from string query
        $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id']): '';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM shows
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $show = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Main Controller
            return $show;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function updateGunShow($id)
    {
      // retrieve form data
      $display = ( isset($_REQUEST['display']) ) ? filter_var($_REQUEST['display']): '';
      $start = ( isset($_REQUEST['start']) ) ? filter_var($_REQUEST['start']): '';
      $end = ( isset($_REQUEST['end']) ) ? filter_var($_REQUEST['end']): '';

      $name = ( isset($_REQUEST['name']) ) ? filter_var($_REQUEST['name']): '';
      if (!isset($name) || $name == '') {
         $name = ( isset($_REQUEST['new_name']) ) ? filter_var($_REQUEST['new_name']): '';
      }
      $location = ( isset($_REQUEST['location']) ) ? filter_var($_REQUEST['location']): '';
      if (!isset($location) || $location == '') {
         $location = ( isset($_REQUEST['new_location']) ) ? filter_var($_REQUEST['new_location']): '';
      }
      $city = ( isset($_REQUEST['city']) ) ? filter_var($_REQUEST['city']): '';
      if (!isset($city) || $city == '') {
         $city = ( isset($_REQUEST['new_city']) ) ? filter_var($_REQUEST['new_city']): '';
      }
      $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state']): '';
      $rep = ( isset($_REQUEST['rep']) ) ? filter_var($_REQUEST['rep']): '';
      if (!isset($rep) || $rep == '') {
         $rep = ( isset($_REQUEST['new_rep']) ) ? filter_var($_REQUEST['new_rep']): '';
      }
      $show_url = ( isset($_REQUEST['show_url']) ) ? filter_var($_REQUEST['show_url']): '';
      $show_producer = ( isset($_REQUEST['show_producer']) ) ? filter_var($_REQUEST['show_producer']): '';
      if (!isset($show_producer) || $show_producer == '') {
         $show_producer = ( isset($_REQUEST['new_show_producer']) ) ? filter_var($_REQUEST['new_show_producer']): '';
      }
      $producer_url = ( isset($_REQUEST['producer_url']) ) ? filter_var($_REQUEST['producer_url']): '';
      if (!isset($producer_url) || $producer_url == '') {
         $producer_url = ( isset($_REQUEST['new_producer_url']) ) ? filter_var($_REQUEST['new_producer_url']): '';
      }
      $map = ( isset($_REQUEST['map']) ) ? filter_var($_REQUEST['map']): '';

      // trim end date
      $trimmed = trim($end);
      // if saved without change strlen=19
      if (strlen($trimmed) > 10) {
         $end = $trimmed;
      } else {
         // if changed to date only, append time
         $end = $trimmed . ' 23:59:59';
      }

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "UPDATE shows SET
           display         = :display,
           start           = :start,
           end             = :end,
           name            = :name,
           location        = :location,
           city            = :city,
           state           = :state,
           rep             = :rep,
           show_url        = :show_url,
           show_producer   = :show_producer,
           producer_url    = :producer_url,
           map             = :map
           WHERE id        = :id";
         $stmt = $db->prepare($sql);
         $parameters = [
           ':id'            => $id,
           ':display'       => $display,
           ':start'         => $start,
           ':end'           => $end,
           ':name'          => $name,
           ':location'      => $location,
           ':city'          => $city,
           ':state'         => $state,
           ':rep'           => $rep,
           ':show_url'      => $show_url,
           ':show_producer' => $show_producer,
           ':producer_url'  => $producer_url,
           ':map'           => $map
         ];
         $result = $stmt->execute($parameters);

         // return result to Admin/Shows Controller
         return $result;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
    }




    public static function deleteShow($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM shows
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


   public static function getCitiesAdmin()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT DISTINCT city FROM shows
               ORDER BY city";
         $stmt = $db->query($sql);

         $cities = $stmt->fetchAll(PDO::FETCH_OBJ);

         // return to Controller
         return $cities;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   /**
    * returns count of 0 or 1
    *
    * @return String    Count of records found
    */
   public static function doesNameExistAdmin()
   {
      // retrieve Ajax post data
      $name = ( isset($_REQUEST['name']) ) ? filter_var($_REQUEST['name'], FILTER_SANITIZE_STRING) : '';

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT * FROM shows
                  WHERE name = :name
                  LIMIT 1";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':name' => $name
         ];
         $stmt->execute($parameters);

         // get row count
         $count = $stmt->rowCount();

         // return to controller
         return $count;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   /**
    * returns count of 0 or 1
    *
    * @return String    Count of records found
    */
   public static function doesLocationExistAdmin()
   {
      // retrieve Ajax post data
      $location = ( isset($_REQUEST['location']) ) ? filter_var($_REQUEST['location'], FILTER_SANITIZE_STRING) : '';

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT * FROM shows
                  WHERE location = :location
                  LIMIT 1";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':location' => $location
         ];
         $stmt->execute($parameters);

         // get row count
         $count = $stmt->rowCount();

         // return to controller
         return $count;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }


   /**
    * returns count of 0 or 1
    *
    * @return String    Count of records found
    */
   public static function doesCityExistAdmin()
   {
      // retrieve Ajax post data
      $city = ( isset($_REQUEST['city']) ) ? filter_var($_REQUEST['city'], FILTER_SANITIZE_STRING) : '';

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT * FROM shows
                  WHERE city = :city
                  LIMIT 1";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':city' => $city
         ];
         $stmt->execute($parameters);

         // get row count
         $count = $stmt->rowCount();

         // return to controller
         return $count;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }


   /**
    * returns count of 0 or 1
    *
    * @return String    Count of records found
    */
   public static function doesRepExistAdmin()
   {
      // retrieve Ajax post data
      $rep = ( isset($_REQUEST['rep']) ) ? filter_var($_REQUEST['rep'], FILTER_SANITIZE_STRING) : '';

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT * FROM shows
                  WHERE rep = :rep
                  LIMIT 1";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':rep' => $rep
         ];
         $stmt->execute($parameters);

         // get row count
         $count = $stmt->rowCount();

         // return to controller
         return $count;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   /**
    * returns count of 0 or 1
    *
    * @return String    Count of records found
    */
   public static function doesShowProducerExistAdmin()
   {
      // retrieve Ajax post data
      $show_producer = ( isset($_REQUEST['show_producer']) ) ? filter_var($_REQUEST['show_producer'], FILTER_SANITIZE_STRING) : '';

      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT * FROM shows
                  WHERE show_producer = :show_producer
                  LIMIT 1";
         $stmt = $db->prepare($sql);
         $parameters = [
            ':show_producer' => $show_producer
         ];
         $stmt->execute($parameters);

         // get row count
         $count = $stmt->rowCount();

         // return to controller
         return $count;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   public static function getEventNamesAdmin()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT DISTINCT name, city, state FROM shows
                  ORDER BY name";
         $stmt = $db->query($sql);
         $stmt->execute();

         $names = $stmt->fetchAll(PDO::FETCH_OBJ);

         return $names;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   public static function getRepNamesAdmin()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT DISTINCT rep FROM shows ORDER BY rep";
         $stmt = $db->query($sql);
         $stmt->execute();

         $reps = $stmt->fetchAll(PDO::FETCH_OBJ);

         return $reps;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   public static function getShowProductionCompaniesAdmin()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT DISTINCT show_producer FROM shows
                  ORDER BY show_producer";
         $stmt = $db->query($sql);
         $stmt->execute();

         $producers = $stmt->fetchAll(PDO::FETCH_OBJ);

         return $producers;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   public static function getShowProductionCompanyUrlsAdmin()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT DISTINCT show_producer, producer_url FROM shows
                  ORDER BY show_producer";
         $stmt = $db->query($sql);
         $stmt->execute();

         $urls = $stmt->fetchAll(PDO::FETCH_OBJ);

         return $urls;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   public static function getMapUrlsForEventLocationsAdmin()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT DISTINCT location, city, state, map FROM shows
                  ORDER BY state, city, location";
         $stmt = $db->query($sql);
         $stmt->execute();

         $map_urls = $stmt->fetchAll(PDO::FETCH_OBJ);

         return $map_urls;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }



   public static function getLocationsAdmin()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT DISTINCT location, city, state FROM shows
                  ORDER BY state, city, location";
         $stmt = $db->query($sql);
         $stmt->execute();

         $locations = $stmt->fetchAll(PDO::FETCH_OBJ);

         return $locations;
      }
      catch (PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }

}
