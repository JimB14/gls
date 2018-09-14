<?php

namespace App\Models;

use PDO;


class Specification extends \Core\Model
{
    public static function getSpecs($series, $beam)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM specifications
                    WHERE series = :series
                    AND beam = :beam";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':series' => $series,
                ':beam'   => $beam
            ];
            $stmt->execute($parameters);
            $specs = $stmt->fetch(PDO::FETCH_OBJ);

            return $specs;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

 // = = = = refactored updates above  = = = = = = = = = = = = = = = = = = = = =



    public static function getStingraySpecs($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM specifications
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $specs = $stmt->fetch(PDO::FETCH_OBJ);

            return $specs;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



   public static function getLaserSpecsApi()
   {
      try
      {
         // establish db connection
         $db = static::getDB();

         $sql = "SELECT laser_series, laser_color, attachment_style, dot_visibility,
                  battery_life, sensor_location, material, platform, battery_type,
                  laser_adjustment, output, master_on_off_switch, warranty,
                  beam_modes, fda_warning
                  FROM specifications
                  ORDER BY laser_series";
         $stmt = $db->prepare($sql);
         $stmt->execute();
         $specs = $stmt->fetchAll(PDO::FETCH_OBJ);

         return $specs;
      }
      catch(PDOException $e)
      {
         echo $e->getMessage();
         exit();
      }
   }
}
