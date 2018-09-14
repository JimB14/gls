<?php

namespace App\Models;

use PDO;
use \Core\View;
use \App\Config;

/**
 * Comment Model
 *
 * PHP version 7.0
 */
class Comment extends \Core\Model
{
    /**
     * Retrieves comments by order ID
     * @param  Integer  $id     The order ID
     * @return Object           The comments
     */
    public static function getComments($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM comments
                    WHERE order_id = :order_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':order_id' => $id
            ];
            $stmt->execute($parameters);

            $comments = $stmt->fetchALL(PDO::FETCH_OBJ);

            // return to Controller
            return $comments;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * Inserts comment into `comments`
     * @param  Object   $order   The order
     * @param  Integer  $user    The staff persons' record
     * @return Boolean
     */
    public static function insertComment($order, $user, $comment, $customer_id, $customer_type)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO comments SET
                    customer_id = :customer_id,
                    customer_type = :customer_type,
                    order_id = :order_id,
                    shipped = :shipped,
                    staff = :staff,
                    comment = :comment";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':customer_id'   => $customer_id,
                ':customer_type' => $customer_type,
                ':order_id'      => $order->id,
                ':shipped'       => $order->oshippeddate,
                ':staff'         => $user->first_name . ' ' . $user->last_name,
                ':comment'       => $comment
            ];
            $result = $stmt->execute($parameters);

            // return to Controller
            return $order;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




}
