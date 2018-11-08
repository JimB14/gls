<?php

namespace App\Models;

use PDO;


class Coupon extends \Core\Model
{

    /**
     *  Retrieves all records in `coupons`
     *
     * @return Object
     */
    public static function getCoupons()
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT * FROM coupons
            ORDER BY promo_name";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $coupons = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $coupons;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     *  Retrieves coupon by coupon ID
     *
     * @return Object
     */
    public static function getCoupon($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM coupons
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $coupon = $stmt->fetch(PDO::FETCH_OBJ);

            return $coupon;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    /**
     *  Retrieves coupon by coupon code
     *
     * @return Object
     */
    public static function getByCode($coupon_code)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM coupons
                    WHERE coupon_code = :coupon_code";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':coupon_code' => $coupon_code
            ];
            $stmt->execute($parameters);
            $coupon = $stmt->fetch(PDO::FETCH_OBJ);

            return $coupon;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     *  Changes value of enabled field to true
     *
     * @return Boolean
     */
    public static function enableCoupon($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE coupons SET
                    enabled = :enabled
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'      => $id,
                ':enabled' => 1
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



    /**
     *  Changes value of enabled field to false
     *
     * @return Boolean
     */
    public static function disableCoupon($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE coupons SET
                    enabled = :enabled
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'      => $id,
                ':enabled' => 0
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


    /**
     *  Deletes coupon by ID
     *
     * @return Boolean
     */
    public static function deleteCoupon($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "DELETE FROM coupons
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
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


    /**
     *  Updates coupon record
     *
     * @return Boolean
     */
    public static function updateCoupon($id)
    {
        $promo_name = (isset($_REQUEST['promo_name'])) ? filter_var($_REQUEST['promo_name'], FILTER_SANITIZE_STRING) : '';
        $enabled = (isset($_REQUEST['enabled'])) ? filter_var($_REQUEST['enabled'], FILTER_SANITIZE_NUMBER_INT) : '';
        $promo_description = (isset($_REQUEST['promo_description'])) ? filter_var($_REQUEST['promo_description'], FILTER_SANITIZE_STRING) : '';
        $promo_start = (isset($_REQUEST['promo_start'])) ? filter_var($_REQUEST['promo_start'], FILTER_SANITIZE_STRING) : '';
        $promo_end = (isset($_REQUEST['promo_end'])) ? filter_var($_REQUEST['promo_end'], FILTER_SANITIZE_STRING) : '';
        $max_uses = (isset($_REQUEST['max_uses'])) ? filter_var($_REQUEST['max_uses'], FILTER_SANITIZE_NUMBER_INT) : '';
        $uses_per_customer = (isset($_REQUEST['uses_per_customer'])) ? filter_var($_REQUEST['uses_per_customer'], FILTER_SANITIZE_NUMBER_INT) : '';
        $discount = (isset($_REQUEST['discount'])) ? filter_var($_REQUEST['discount'], FILTER_SANITIZE_STRING) : '';
        $discount_type = (isset($_REQUEST['discount_type'])) ? filter_var($_REQUEST['discount_type'], FILTER_SANITIZE_STRING) : '';
        $coupon_code = (isset($_REQUEST['coupon_code'])) ? filter_var($_REQUEST['coupon_code'], FILTER_SANITIZE_STRING) : '';
        $uses_count = (isset($_REQUEST['uses_count'])) ? filter_var($_REQUEST['uses_count'], FILTER_SANITIZE_STRING) : '';

        $trseries = (isset($_REQUEST['promo_trseries'])) ? filter_var($_REQUEST['promo_trseries'], FILTER_SANITIZE_STRING) : '';
        $gtoflx = (isset($_REQUEST['promo_gtoflx'])) ? filter_var($_REQUEST['promo_gtoflx'], FILTER_SANITIZE_STRING) : '';
        $stingray = (isset($_REQUEST['promo_stingray'])) ? filter_var($_REQUEST['promo_stingray'], FILTER_SANITIZE_STRING) : '';
        $fls = (isset($_REQUEST['promo_flx'])) ? filter_var($_REQUEST['promo_flx'], FILTER_SANITIZE_STRING) : '';
        $holsters = (isset($_REQUEST['promo_holsters'])) ? filter_var($_REQUEST['promo_holsters'], FILTER_SANITIZE_STRING) : '';
        $accessories = (isset($_REQUEST['promo_accessories'])) ? filter_var($_REQUEST['promo_accessories'], FILTER_SANITIZE_STRING) : '';
        $batteries = (isset($_REQUEST['promo_batteries'])) ? filter_var($_REQUEST['promo_batteries'], FILTER_SANITIZE_STRING) : '';
        $toolkits = (isset($_REQUEST['promo_toolkits'])) ? filter_var($_REQUEST['promo_toolkits'], FILTER_SANITIZE_STRING) : '';

        try
        {
            $db = static::getDB();

            $sql = "UPDATE coupons SET
                    promo_name = :promo_name,
                    enabled = :enabled,
                    promo_description = :promo_description,
                    promo_start = :promo_start,
                    promo_end = :promo_end,
                    max_uses = :max_uses,
                    uses_per_customer = :uses_per_customer,
                    discount = :discount,
                    discount_type = :discount_type,
                    coupon_code = :coupon_code,
                    uses_count = :uses_count
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'      => $id,
                ':promo_name' => $promo_name,
                ':enabled' => $enabled,
                ':promo_description' => $promo_description,
                ':promo_start' => $promo_start,
                ':promo_end' => $promo_end,
                ':max_uses' => $max_uses,
                ':uses_per_customer' => $uses_per_customer,
                ':discount' => $discount,
                ':discount_type' => $discount_type,
                ':coupon_code' => $coupon_code,
                ':uses_count' => $uses_count
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



    public static function searchCouponsByName($searchword)
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT * FROM coupons
                    WHERE promo_name LIKE '%$searchword%'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $coupons = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $coupons;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function createCoupon()
    {
        $promo_name = (isset($_REQUEST['promo_name'])) ? filter_var($_REQUEST['promo_name'], FILTER_SANITIZE_STRING) : '';
        $enabled = (isset($_REQUEST['enabled'])) ? filter_var($_REQUEST['enabled'], FILTER_SANITIZE_NUMBER_INT) : '';
        $promo_description = (isset($_REQUEST['promo_description'])) ? filter_var($_REQUEST['promo_description'], FILTER_SANITIZE_STRING) : '';
        $promo_start = (isset($_REQUEST['promo_start'])) ? filter_var($_REQUEST['promo_start'], FILTER_SANITIZE_STRING) : '';
        $promo_end = (isset($_REQUEST['promo_end'])) ? filter_var($_REQUEST['promo_end'], FILTER_SANITIZE_STRING) : '';
        $max_uses = (isset($_REQUEST['max_uses'])) ? filter_var($_REQUEST['max_uses'], FILTER_SANITIZE_NUMBER_INT) : '';
        $uses_per_customer = (isset($_REQUEST['uses_per_customer'])) ? filter_var($_REQUEST['uses_per_customer'], FILTER_SANITIZE_NUMBER_INT) : '';
        $discount = (isset($_REQUEST['discount'])) ? filter_var($_REQUEST['discount'], FILTER_SANITIZE_STRING) : '';
        $discount_type = (isset($_REQUEST['discount_type'])) ? filter_var($_REQUEST['discount_type'], FILTER_SANITIZE_STRING) : '';
        $coupon_code = (isset($_REQUEST['coupon_code'])) ? filter_var($_REQUEST['coupon_code'], FILTER_SANITIZE_STRING) : '';
        $uses_count = (isset($_REQUEST['uses_count'])) ? filter_var($_REQUEST['uses_count'], FILTER_SANITIZE_STRING) : '';

        // IDs of items added
        $trseries[]    = (isset($_REQUEST['promo_trseries'])) ? $_REQUEST['promo_trseries'] : '';
        $gtoflx[]      = (isset($_REQUEST['promo_gtoflx'])) ? $_REQUEST['promo_gtoflx'] : '';
        $stingray[]    = (isset($_REQUEST['promo_stingray'])) ? $_REQUEST['promo_stingray'] : '';
        $flx[]         = (isset($_REQUEST['promo_flx'])) ? $_REQUEST['promo_flx'] : '';
        $holsters[]    = (isset($_REQUEST['promo_holsters'])) ? $_REQUEST['promo_holsters'] : '';
        $accessories[] = (isset($_REQUEST['promo_accessories'])) ? $_REQUEST['promo_accessories'] : '';
        $batteries[]   = (isset($_REQUEST['promo_batteries'])) ? $_REQUEST['promo_batteries'] : '';
        $toolkits[]    = (isset($_REQUEST['promo_toolkits'])) ? $_REQUEST['promo_toolkits'] : '';

        // echo '<pre>'; print_r($_REQUEST); print_r($trseries); exit();

        // join arrays into new array
        $products = array_merge_recursive($trseries, $gtoflx, $stingray, $flx, $holsters, $accessories, $batteries, $toolkits);

        // resource: https://stackoverflow.com/questions/52956819/iterating-over-multidimensional-array-where-some-arrays-might-have-no-elements
        $idArr = [];
        foreach($products as $value) {
            if (!$value) continue;   // continue to next iteration without executing the inner loop
            foreach ($value as $id) {
                $idArr[] = $id;
            }
        }

        // echo '<h3>$idArr:</h3>'; echo '<pre>'; print_r($idArr); exit();

        // set value if checked
        if (isset($enabled)) { $enabled = 1; } else { $enabled = $enabled; }

        try
        {
            $db = static::getDB();

            $sql = "INSERT INTO coupons SET
                    promo_name = :promo_name,
                    enabled = :enabled,
                    promo_description = :promo_description,
                    promo_start = :promo_start,
                    promo_end = :promo_end,
                    max_uses = :max_uses,
                    uses_per_customer = :uses_per_customer,
                    discount = :discount,
                    discount_type = :discount_type,
                    coupon_code = :coupon_code,
                    uses_count = :uses_count";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':promo_name' => $promo_name,
                ':enabled' => $enabled,
                ':promo_description' => $promo_description,
                ':promo_start' => $promo_start,
                ':promo_end' => $promo_end,
                ':max_uses' => $max_uses,
                ':uses_per_customer' => $uses_per_customer,
                ':discount' => $discount,
                ':discount_type' => $discount_type,
                ':coupon_code' => $coupon_code,
                ':uses_count' => $uses_count
            ];
            $result = $stmt->execute($parameters);

            // promotion added successfully
            if ($result)
            {
                // get new coupon ID
                $id = $db->lastInsertId();

                // insert into `coupon_product_lookup`
                try
                {
                    $sql = "INSERT INTO coupon_product_lookup SET
                            couponid  = :couponid,
                            productid = :productid";
                    $stmt = $db->prepare($sql);

                    foreach ($idArr as $productid)
                    {
                        $stmt->bindParam(':couponid', $id);
                        $stmt->bindParam(':productid', $productid);
                        $result = $stmt->execute();
                    }

                    return $result;
                }
                catch(PDOException $e)
                {
                    $stmt->rollback();
                    echo 'Error inserting data into lookup table.' . $e->getMessage();
                    exit();
                }

            }
            //failure
            {
                echo "Error occurred creating new promotion.";
                exit();
            }
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getTimesUsed($coupon_id, $customer_id)
    {
        // test
            // echo 'Inside getTimesUsed() in Coupon model!'; exit();
            // echo $coupon_id; exit();
            // echo $customer_id; exit();

        try
        {
            $db = static::getDB();

            $sql = "SELECT *
                    FROM coupon_customer_lookup
                    WHERE couponid = :couponid
                    AND customerid = :customerid";
            $stmt = $db->prepare($sql);
            $parameters =[
                ':couponid'   => $coupon_id,
                ':customerid' => $customer_id
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            // echo json_encode($stmt->rowCount()); exit();

            return $stmt->rowCount();
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     *  Retrieve IDs from Promotion
     *
     *  @param $coupon_id       The promotion ID
     *  @return Object
     */
    public static function getPromotionItemIds($coupon_id)
    {
        // test echo $coupon_id; exit();

        try
        {
            $db = static::getDB();

            $sql = "SELECT productid
                    FROM coupon_product_lookup
                    WHERE couponid = :couponid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':couponid' => $coupon_id
            ];
            $stmt->execute($parameters);
            $ids = $stmt->fetchAll(PDO::FETCH_NUM); // NUM required to have different keys in $ids array

            return $ids;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     *  Increments coupons.uses_count
     * @param  INT      $promo_id       The promotion ID
     * @return Boolean
     */
    public static function updateUsesCount($promo_id)
    {
        try
        {
            $db = static::getDB();

            $sql = "UPDATE coupons SET
                    uses_count = uses_count + 1
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $promo_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     *  Inserts data into `coupon_customer_lookup`
     * @param  INT      $couponid    The promotion/coupon ID
     * @param  INT      $customerid  The customer ID
     * @return Boolean
     */
    public static function updateCustomerCouponUse($couponid, $customerid)
    {
        try
        {
            $db = static::getDB();

            $sql = "INSERT INTO coupon_customer_lookup SET
                    couponid   = :couponid,
                    customerid = :customerid";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':couponid'   => $couponid,
                ':customerid' => $customerid,
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function logCouponUse($promo_id, $promo_name, $customer_id, $order_id, $total_discount)
    {
        try
        {
            $db = static::getDB();

            $sql = "INSERT INTO coupon_log SET
                    couponid    = :couponid,
                    promo_name  = :promo_name,
                    customer_id = :customer_id,
                    orderid     = :orderid,
                    amount      = :amount";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':couponid'    => $promo_id,
                ':promo_name'  => $promo_name,
                ':customer_id' => $customer_id,
                ':orderid'     => $order_id,
                ':amount'      => $total_discount
            ];
            $result = $stmt->execute($parameters);;

            return $result;
        }
        catch(PDOException $e)
        {
            $stmt->rollback();
            echo 'Error inserting data into lookup table.' . $e->getMessage();
            exit();
        }
    }




}
