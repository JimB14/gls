<?php

namespace App\Models;

use PDO;
use \Core\View;
use \App\Config;

/**
 * Order Model
 *
 * PHP version 7.0
 */
class Order_content extends \Core\Model
{

    /**
     * Insert new order data into `orders` and `orders_content` tables
     *
     * @param  Object  $id          The order ID
     * @param  Array   $rma         The RMA number
     * @param  Decimal $itemid      The item ID
     * @param  String  $reason      Reason for return
     * @param  String  $status      New status
     * @return Boolean
     */
    public static function updateAddRma($id, $itemid, $rma, $reason, $status)
    {

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders_content SET
                    rma_number    = :rma_number,
                    rma_reason    = :rma_reason,
                    rma_date      = :rma_date,
                    status        = :status
                    WHERE orderid = :orderid
                    AND itemid    = :itemid";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':rma_number' => $rma,
                ':rma_reason' => $reason,
                ':rma_date'   => date("Y-m-d"),
                ':orderid'    => $id,
                ':itemid'     => $itemid,
                ':status'     => $status
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function updateItemStatus($orderid, $itemid, $status) 
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE orders_content SET
                    status = :status
                    WHERE orderid = :orderid
                    AND itemid = :itemid"; 
            $stmt = $db->prepare($sql);
            $parameters = [
                ':status'  => $status,
                ':orderid' => $orderid,
                ':itemid'  => $itemid
            ]; 
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo 'Error updating item status: ' . $e->getMessage();
            exit();
        }
    }



    /**
     * updates `status` of record where `rma_number` has a value
     */
    public static function updateReturnedItemStatus($orderid, $status) 
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE orders_content SET
                    status = :status
                    WHERE orderid = :orderid
                    AND rma_number <> ''"; 
            $stmt = $db->prepare($sql);
            $parameters = [
                ':status'  => $status,
                ':orderid' => $orderid
            ]; 
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo 'Error updating item status: ' . $e->getMessage();
            exit();
        }
    }



    /**
     * updates the status of each item in order to shipped (called if shipping label 
     * is successfully created)
     */
    public static function updateStatusOfAllItems($orderid, $status) 
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE orders_content SET
                    status = :status
                    WHERE orderid = :orderid"; 
            $stmt = $db->prepare($sql);
            $parameters = [
                ':status'  => $status,
                ':orderid' => $orderid
            ]; 
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo 'Error updating item status: ' . $e->getMessage();
            exit();
        }
    }



}
