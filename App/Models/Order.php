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
class Order extends \Core\Model
{

    /**
     * Insert new order data into `orders` and `orders_content` tables
     *
     * @param  Object  $buyer               Buyer type
     * @param  Object  $customer            Buyer data
     * @param  Array   $cart                Shopping cart content
     * @param  Decimal $totalAmt            Order total
     * @param  String  $pnref               PayPal transaction ID
     * @param  Array   $sales_tax_data      Sales tax data (state & county breakdown)
     * @return Boolean
     */
    public function insertOrder($buyer, $customer, $cart, $totalAmt, $pnref, $sales_tax_data)
    {
        // echo "Connected to insertOrder() in Order model."; exit();
        $user_ip = $_SERVER['REMOTE_ADDR'];

        // test
        // echo $user_ip . '<br>';
        // echo "PNREF: $pnref <br>";
        // echo 'Total amt: ' . $totalAmt . '<br>';
        // echo 'Ship method: ' . $_SESSION['shipping_method'] . '<br>';
        // if ($_SESSION['coupon']) {
        //     echo 'Coupon used: ' . $_SESSION['coupon'];
        // }
        // echo '<pre>';
        // print_r($cart);
        // echo '</pre>';
        // echo '<pre>';
        // print_r($customer);
        // echo '</pre>';
        // exit();

        // set shipping cost
        switch($_SESSION['shipping_method'])
        {
            CASE 'USPS Priority Mail':
                $oshipcost = Config::PRIORITY;
                break;
            CASE 'UPS Ground':
                $oshipcost = Config::UPSGROUND;
                break;
            CASE 'UPS 3 Day Select':
                $oshipcost = Config::UPS3DAYSELECT;
                break;
            CASE 'UPS 2nd Day Air':
                $oshipcost = Config::UPS2NDDAYAIR;
                break;
            default:
                $oshipcost = 0;
        }

        // store coupon in variable
        if (isset($_SESSION['coupon']) && $_SESSION['coupon'] != '')
        {
            $coupon = $_SESSION['coupon'];

            // identify coupon name from code & store in variable
            $results = Order::getDiscountProgramName($coupon);

            // store program and discount percentage in variable
            $coupon = $results['program'];
            $coupondiscount = $results['discountPercentage'];
        }
        else
        {
            $coupon = '';
            $coupondiscount = '';
        }

        // buyer is customer (registered)
        if ($buyer == 'customer')
        {
            $results = $this->insertCustomerOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data);

            return $results;
        }
        else if ($buyer == 'guest')
        {
            $results = $this->insertGuestOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data);

            return $results;
        }
        else if ($buyer == 'caller')
        {
            $results = $this->insertCallerOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data);

            return $results;
        }
        else if ($buyer == 'dealer')
        {
            $results = $this->insertDealerOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data);

            return $results;
        }
        else if ($buyer == 'partner')
        {
            $results = $this->insertPartnerOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data);

            return $results;
        }
    }




    /**
     * admin function that returns orders and order content
     *
     * @return Object     The orders
     */
    public static function getOrders($status)
    {
        // set WHERE clause based on value of order status
        switch($status)
        {
            CASE 'all':
                $WHERE = '';
                $LIMIT = 'LIMIT 50';
                break;
            CASE 'pending':
                $WHERE = 'WHERE orders.order_status = "pending"';
                $LIMIT = '';
                break;
            CASE 'shipped':
                $WHERE = 'WHERE orders.order_status = "shipped"';
                $LIMIT = 'LIMIT 20';
                break;
            CASE 'return-pending':
                $WHERE = 'WHERE orders.order_status = "return-pending"';
                $LIMIT = '';
                break;
            CASE 'returned':
                $WHERE = 'WHERE orders.order_status = "returned"';
                $LIMIT = '';
                break;
            CASE 'refunded':
                $WHERE = 'WHERE orders.refund_issued = 1';
                $LIMIT = '';
                break;
            default :
                $WHERE = 'error';
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT orders.id, orders.pnref, orders.odate, orders.customerid,
                    orders.guestid, orders.callerid, orders.dealerid, orders.partnerid,
                    orders.orderamount, orders.oshippeddate, orders.oshipmethod,
                    orders.trackingcode, orders.order_status, orders.return_label_created,
                    orders.return_label_date, orders.return_label_url,
                    customers.billing_firstname,
                    customers.billing_lastname, customers.email,
                    customers.billing_company AS customer_company,
                    guests.billing_firstname AS guest_firstname,
                    guests.billing_lastname AS guest_lastname,
                    guests.email AS guest_email,
                    guests.billing_company AS guest_company,
                    callers.billing_firstname AS caller_firstname,
                    callers.billing_lastname AS caller_lastname,
                    callers.email AS caller_email,
                    callers.billing_company AS caller_company,
                    dealers.first_name as dealer_firstname,
                    dealers.last_name as dealer_lastname,
                    dealers.email AS dealer_email,
                    dealers.company AS dealer_company,
                    partners.first_name as partner_firstname,
                    partners.last_name as partner_lastname,
                    partners.email AS partner_email,
                    partners.company AS partner_company
                    FROM orders
                    LEFT JOIN customers
                        ON orders.customerid = customers.id
                    LEFT JOIN guests
                        ON guests.id = orders.guestid
                    LEFT JOIN callers
                        ON callers.id = orders.callerid
                    LEFT JOIN dealers
                        ON dealers.id = orders.dealerid
                    LEFT JOIN partners
                        ON partners.id = orders.partnerid
                    $WHERE
                    ORDER BY orders.odate DESC
                    $LIMIT";
            $stmt  = $db->prepare($sql);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $orders;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * admin function that returns orders and order content
     *
     * @return Object     The orders
     */
    public static function get2013Orders($status)
    {
        // set WHERE clause based on value of order status
        switch($status)
        {
            CASE 'all':
                $WHERE = '';
                $LIMIT = 'LIMIT 50';
                break;
            CASE 'pending':
                $WHERE = 'WHERE orders_2013.order_status = "pending"';
                $LIMIT = '';
                break;
            CASE 'shipped':
                $WHERE = 'WHERE orders_2013.order_status = "shipped"';
                $LIMIT = 'LIMIT 20';
                break;
            CASE 'return-pending':
                $WHERE = 'WHERE orders_2013.order_status = "return-pending"';
                $LIMIT = '';
                break;
            CASE 'returned':
                $WHERE = 'WHERE orders_2013.order_status = "returned"';
                $LIMIT = '';
                break;
            default :
                $WHERE = 'error';
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT orders_2013.id, orders_2013.pnref, orders_2013.odate, orders_2013.customerid,
                    orders_2013.guestid, orders_2013.callerid, orders_2013.dealerid, orders_2013.partnerid,
                    orders_2013.orderamount, orders_2013.oshippeddate, orders_2013.oshipmethod,
                    orders_2013.trackingcode, orders_2013.order_status, orders_2013.return_label_created,
                    orders_2013.return_label_date, orders_2013.return_label_url,
                    customers.billing_firstname,
                    customers.billing_lastname, customers.email,
                    customers.billing_company AS customer_company,
                    guests.billing_firstname AS guest_firstname,
                    guests.billing_lastname AS guest_lastname,
                    guests.email AS guest_email,
                    guests.billing_company AS guest_company,
                    callers.billing_firstname AS caller_firstname,
                    callers.billing_lastname AS caller_lastname,
                    callers.email AS caller_email,
                    callers.billing_company AS caller_company,
                    dealers.first_name as dealer_firstname,
                    dealers.last_name as dealer_lastname,
                    dealers.email AS dealer_email,
                    dealers.company AS dealer_company,
                    partners.first_name as partner_firstname,
                    partners.last_name as partner_lastname,
                    partners.email AS partner_email,
                    partners.company AS partner_company
                    FROM orders_2013
                    LEFT JOIN customers
                        ON orders_2013.customerid = customers.id
                    LEFT JOIN guests
                        ON guests.id = orders_2013.guestid
                    LEFT JOIN callers
                        ON callers.id = orders_2013.callerid
                    LEFT JOIN dealers
                        ON dealers.id = orders_2013.dealerid
                    LEFT JOIN partners
                        ON partners.id = orders_2013.partnerid
                    $WHERE
                    ORDER BY orders_2013.odate DESC
                    $LIMIT";
            $stmt  = $db->prepare($sql);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $orders;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * Retrieves orders from `orders` by customer ID
     *
     * @param  Integer  $id     The customer ID
     * @return Object           The orders
     */
    public static function getCustomerOrders($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT *
                    FROM orders
                    WHERE customerid = :customerid";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':customerid' => $id
            ];
            $stmt->execute($parameters);
            $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $orders;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Retrieves orders from `orders` by dealer ID
     *
     * @param  Integer  $id     The dealer ID
     * @return Object           The orders
     */
    public static function getDealerOrders($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT *
                    FROM orders
                    WHERE dealerid = :dealerid";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':dealerid' => $id
            ];
            $stmt->execute($parameters);
            $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $orders;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Retrieves orders from `orders` by partner ID
     *
     * @param  Integer  $id     The partner ID
     * @return Object           The orders
     */
    public static function getPartnerOrders($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT *
                    FROM orders
                    WHERE partnerid = :partnerid";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':partnerid' => $id
            ];
            $stmt->execute($parameters);
            $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $orders;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getOrderBuyerTypeAndId($id)
    {
        try
        {
            $db = static::getDB();

            $sql = "SELECT orders.customerid, orders.guestid, orders.callerid,
                    orders.dealerid, orders.partnerid
                    FROM orders
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);
            $order = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo '<pre>';
            // print_r($order);
            // echo '</pre>';
            // exit();

            // buyer type cannot = 0
            foreach ($order as $key => $value) {
                if ($value != 0) {
                    $type = $key;
                    $id = $value;
                }
            }

            // create array
            $results = [
                'type'       => $type,
                'customerid' => $id
            ];

            // return to Controller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * Retrieves order content by order ID
     *
     * @param  Integer  $id     The order record ID
     * @return Object           The order record
     */
    public static function getOrderContent($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM orders_content
                    WHERE orderid = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            // order records have multiple rows (use fetchAll not fetch)
            $order_content = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $order_content;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * Retrieves item record by the order ID and item ID
     *
     * @param  Integer  $order     The order ID
     * @param  Integer  $itemid    The item ID
     * @return Object              The item record
     */
    public static function getOrderItem($orderid, $itemid)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM orders_content
                    WHERE orderid = :orderid 
                    AND itemid = :itemid";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':orderid' => $orderid,
                ':itemid'  => $itemid
            ];
            $stmt->execute($parameters);

            // fetch single row
            $order_content = $stmt->fetch(PDO::FETCH_OBJ);

            return $order_content;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getOrderData($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM orders
                    WHERE orders.id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $order = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $order;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getGuestOrder($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT orders.*, orders_content.*, guests.*
                    FROM orders
                    LEFT JOIN orders_content
                        ON orders.id = orders_content.orderid
                    LEFT JOIN guests
                        ON orders.guestid = guests.id
                    WHERE orders.id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $order = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $order;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * Updates order in `orders` with new data
     *
     * @param  Int      $id                 The order ID
     * @param  String   $trackingNumber     USPS tracking number
     * @param  Decimal  $finalPostage       Actual postage charge
     * @param  String   $transactionId      Transaction ID
     * @return Boolean                      Success or failure
     */
    public static function updateOrderAfterLabelCreation($id, $trackingNumber, $finalPostage, $transactionId, $transactionDateTime)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders SET
                    order_status   = :order_status,
                    oshippeddate   = :oshippeddate,
                    trackingcode   = :trackingcode,
                    final_shipping = :final_shipping,
                    transaction_id = :transaction_id,
                    transaction_timestamp = :transaction_timestamp
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id,
                ':order_status' => 'shipped',
                ':oshippeddate' => date('Y-m-d'),
                ':trackingcode' => $trackingNumber,
                ':final_shipping' => $finalPostage,
                ':transaction_id' => $transactionId,
                ':transaction_timestamp' => $transactionDateTime
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




    public static function getOrderByTrackingNumber($trackingNumber)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM orders
                    WHERE trackingcode = :trackingcode";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':trackingcode' => $trackingNumber
            ];
            $stmt->execute($parameters);

            $order = $stmt->fetch(PDO::FETCH_OBJ);

            // return to Controller
            return $order;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * Updates order record's order status in `orders`
     *
     * @param  Integer  $id     The ID of the record
     * @return Boolean
     */
    public static function updateAsShipped($id, $date)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders SET
                    oshippeddate = :oshippeddate,
                    order_status = :order_status
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id,
                ':oshippeddate' => $date,
                ':order_status' => 'shipped'
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





    /**
     * Updates order record's order status in `orders` to return-pending
     *
     * @param  Integer  $id     The ID of the record
     * @return Boolean
     */
    public static function updateAsReturnPending($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders SET
                    order_status = :order_status,
                    rma = :rma
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id,
                ':order_status' => 'return-pending',
                ':rma'  => 1
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



    /**
     * Store RMA data in orders_content table
     */
    public static function setRMA() 
    {
        // form data
        $id = ( isset($_REQUEST['return_orderid'])  ) ? filter_var($_REQUEST['return_orderid'], FILTER_SANITIZE_NUMBER_INT): '';
        $itemid = ( isset($_REQUEST['return_itemid'])  ) ? filter_var($_REQUEST['return_itemid'], FILTER_SANITIZE_NUMBER_INT): '';
        $rma = ( isset($_REQUEST['rma_number'])  ) ? $_REQUEST['rma_number'] : '';
        $reason = ( isset($_REQUEST['return_reason'])  ) ? $_REQUEST['return_reason'] : '';

        // test
        echo '<pre>';
        print_r($_REQUEST);
        echo '</pre>';
        echo '$id: ' . $id . '<br>';
        echo '$itemid: ' . $itemid . '<br>';
        echo '$rma: ' . $rma . '<br>';
        echo '$reason: ' . $reason;
        exit();

        // validate
        if ($rma == '' || $reason == '')
        {
            $this->setError("<h3>Data missing: RMA and Reason are required fields.</h3>");
            exit();
        }

        // update record in `orders_content`
        $result = Order_content::updateAddRma($id, $itemid, $rma, $reason);

        // success
        if ($result)
        {
            // update order status in `orders`
            $result = Order::updateAsReturnPending($id);

            // success
            if ($result)
            {
                // success message
                echo '<script>';
                echo 'alert("RMA added. Order status updated to return-pending")';
                echo '</script>';

                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/orders/get-order?id='.$id.'"';
                echo '</script>';
                exit();
            }
            // failure
            else
            {
                echo "Error updating order status in orders table.";
                // email webmaster
                exit();
            }
        }
        // failure
        else
        {
            echo "Error updating Orders content table.";
            // email webmaster
            exit();
        }
    }




    /**
     * Updates order status to returned
     *
     * @param  Integer  $id         The ID of the record
     * @return Boolean
     */
    public static function updateOrderStatusToReturned($id)
    {

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders SET
                    order_status = :order_status
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id,
                ':order_status' => 'returned'
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
     * Updates order status and PayPal response data
     *
     * @param  Integer  $id         The ID of the record
     * @param  Array    $response   PayPal response data re refund
     * @return Boolean
     */
    public static function updateAsReturned($id, $amount, $response)
    {

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders SET
                    order_status = :order_status,
                    refund_issued = :refund_issued,
                    refund_amt = :refund_amt,
                    refund_pnref = :refund_pnref,
                    refund_ppref = :refund_ppref,
                    refund_correlation_id = :refund_correlation_id,
                    refund_transtime = :refund_transtime
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id,
                ':order_status' => 'returned',
                ':refund_issued' => 1,
                ':refund_amt' => $amount,
                ':refund_pnref' => $response['PNREF'],
                ':refund_ppref' => $response['PPREF'],
                ':refund_correlation_id' => $response['CORRELATIONID'],
                ':refund_transtime' => $response['TRANSTIME']
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



    /**
     * updates order record after label voided
     *
     * @param  Integer  $order_id   The record ID
     * @return Boolean
     */
    public static function updateAfterLabelVoid($order_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders SET
                    order_status = :order_status,
                    trackingcode = :trackingcode,
                    final_shipping = :final_shipping,
                    transaction_id = :transaction_id,
                    transaction_timestamp = :transaction_timestamp
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $order_id,
                ':order_status' => 'pending',
                ':trackingcode' => '',
                ':final_shipping' => '',
                ':transaction_id' => '',
                ':transaction_timestamp' => ''
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



    /**
     * Updates `orders` table after return label created
     *
     * @param  Integer  $order_id   The order_id
     * @return Boolean
     */
    public static function updateAfterReturnLabelCreated($order_id, $url)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE orders SET
                    return_label_created = :return_label_created,
                    return_label_url = :return_label_url,
                    WHERE id = :id";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $order_id,
                ':return_label_created' => 1,
                ':return_label_url' => $url
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




    public static function getOrderByLastName($last_name, $order_status)
    {
        // set WHERE clause based on value of order status
        switch($order_status)
        {
            CASE '':
                $WHERE = 'WHERE';
                break;
            CASE 'all':
                $WHERE = 'WHERE';
                break;
            CASE 'pending':
                $WHERE = 'WHERE orders.order_status = "pending" AND';
                break;
            CASE 'label-created':
                $WHERE = 'WHERE orders.order_status = "label-created" AND';
                break;
            CASE 'shipped':
                $WHERE = 'WHERE orders.order_status = "shipped" AND';
                break;
            CASE 'return-pending':
                $WHERE = 'WHERE orders.order_status = "return-pending" AND';
                break;
            CASE 'returned':
                $WHERE = 'WHERE orders.order_status = "returned" AND';
                break;
            default :
                $WHERE = 'error';
        }
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT orders.id, orders.pnref, orders.odate, orders.customerid,
                    orders.guestid, orders.callerid, orders.dealerid, orders.partnerid,
                    orders.oshiplastname, orders.orderamount, orders.oshippeddate,
                    orders.oshipmethod, orders.trackingcode, orders.order_status,
                    orders.return_label_created, orders.return_label_date,
                    orders.return_label_url,
                    customers.billing_firstname,
                    customers.billing_lastname, customers.email,
                    guests.billing_firstname AS guest_firstname,
                    guests.billing_lastname AS guest_lastname,
                    guests.email AS guest_email,
                    callers.billing_firstname AS caller_firstname,
                    callers.billing_lastname AS caller_lastname,
                    callers.email AS caller_email,
                    dealers.first_name as dealer_firstname,
                    dealers.last_name as dealer_lastname,
                    dealers.email AS dealer_email,
                    partners.first_name as partner_firstname,
                    partners.last_name as partner_lastname,
                    partners.email AS partner_email
                    FROM orders
                    LEFT JOIN customers
                        ON orders.customerid = customers.id
                    LEFT JOIN guests
                        ON guests.id = orders.guestid
                    LEFT JOIN callers
                        ON callers.id = orders.callerid
                    LEFT JOIN dealers
                        ON dealers.id = orders.dealerid
                    LEFT JOIN partners
                        ON partners.id = orders.partnerid
                    $WHERE
                    orders.oshiplastname LIKE '$last_name%'
                    ORDER BY orders.odate DESC";
            $stmt  = $db->prepare($sql);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $orders;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * admin function that returns all orders for customer by type (customer, dealer, partner)
     *
     * @return Object     The orders
     */
    public static function getMyOrders($type, $id)
    {
        // create field name from $type value
        $field = $type.'id';

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM orders
                    WHERE $field = :id
                    ORDER BY odate DESC";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            $orders = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $orders;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * Retrieves order content by order ID
     *
     * @param  Integer  $id         The customer ID
     * @param  String   $orderIds   Order IDs
     * @return Object               The orders
     */
    public static function getContentOfOrders($id, $idString)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT *
                    FROM orders_content
                    WHERE customerid = :customerid
                    AND orderid IN ($idString)";
            $stmt  = $db->prepare($sql);
            $parameters = [
                ':customerid' => $id
            ];
            $stmt->execute($parameters);

            // order records have multiple rows (use fetchAll not fetch)
            $order_content = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $order_content;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * updates orders_content with serial numbers
     *
     * @param  Integer  $orderid    The order ID
     * @return Boolean
     */
    public static function registerLaserWarranty($orderid)
    {

        // get form data
        $lasers = ( isset($_REQUEST['lasers'])  ) ? $_REQUEST['lasers'] : '';
        $serial_numbers = ( isset($_REQUEST['serial_numbers'])  ) ? $_REQUEST['serial_numbers'] : '';

        // test
        // echo $orderid . '<br>';
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // echo '<pre>';
        // print_r($lasers);
        // echo '</pre>';
        // echo '<pre>';
        // print_r($serial_numbers);
        // echo '</pre>';
        // exit();


        // check for identical values in serial_numbers[]
        if (count($serial_numbers) > 1) // more than one laser purchased
        {
            $result = Order::arrayValuesUnique($serial_numbers);

            // error - duplicate found
            if (!$result)
            {
                echo "The serial numbers entered are not unique. Please check and try again.";
                exit();
            }
        }

        // check `orders_content` table for idential serial number
        $result = Order::serialNumbersExist($serial_numbers);

        // error - duplicate found in `orders_content`
        if ($result)
        {
            echo "The serial number or numbers submitted already exist. Please check and try again.";
            exit();
        }

        // declare array
        $laser_serial = [];

        // combine lasers and serial numbers in new array
        foreach ($lasers as $key => $laser)
        {
            $laser_serial[$key]['serial'] = $serial_numbers[$key];
            $laser_serial[$key]['laser'] = $laser;
        }

        // test
        // echo '<pre>';
        // print_r($laser_serial);
        // echo '</pre>';
        // exit();

        // test
        // foreach($laser_serial as $value) {
        //     echo $value['serial']. '<br>';
        //     echo $value['laser']. '<br>';
        // }
        // exit();

        // update order record in `orders_content`
        try
        {
            $db = static::getDB();

            // update table from array
            $sql = "UPDATE orders_content SET
                    serial_number = :serial_number
                    WHERE orderid = $orderid
                    AND name = :name";
            $stmt  = $db->prepare($sql);
            foreach($laser_serial as $value)
            {
                $stmt->bindParam(':serial_number', $value['serial']);
                $stmt->bindParam(':name', $value['laser']);
                $result = $stmt->execute();
            }

            // return to Controller
            return $result;

        }
        catch (PDOException $e)
        {
            echo "Error adding serial number to orders content table: $e->getMessage()";
            // email webmaster
            exit();
        }

    }





    // - - - -  class functions  - - - - - - - - - - - - - - - - //

    /**
     * Inserts customer order into orders & orders_content tables
     *
     * @param  Obj      $customer           The customer
     * @param  Array    $cart               Shopping cart content
     * @param  Int      $totalAmt           Total order amount
     * @param  String   $pnref              PayPal transaction ID
     * @param  Decimal  $oshipcost          Shipping cost
     * @param  String   $coupon             Name of coupon
     * @param  String   $coupondiscount     Percentage discount
     * @param  String   $user_ip            Buyer's IP Address
     * @param  Array    $sales_tax_data     Sales tax data
     * @return Array                        Boolean & shopping cart content
     */
    private function insertCustomerOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data)
    {
        // insert into `orders` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO orders SET
                    pnref           = :pnref,
                    customerid      = :customerid,
                    guestid         = :guestid,
                    callerid        = :callerid,
                    dealerid        = :dealerid,
                    partnerid       = :partnerid,
                    orderamount     = :orderamount,
                    otax_total      = :otax_total,
                    otax_state      = :otax_state,
                    otax_state_amt  = :otax_state_amt,
                    otax_county     = :otax_county,
                    otax_county_amt = :otax_county_amt,
                    itemamount      = :itemamount,
                    oshipmethod     = :oshipmethod,
                    oshipcost       = :oshipcost,
                    oshipfirstname  = :oshipfirstname,
                    oshiplastname   = :oshiplastname,
                    oshipcompany    = :oshipcompany,
                    oshipemail      = :oshipemail,
                    oshipaddress    = :oshipaddress,
                    oshipaddress2   = :oshipaddress2,
                    oshipcity       = :oshipcity,
                    oshipstate      = :oshipstate,
                    oshipzip        = :oshipzip,
                    oshipphone      = :oshipphone,
                    coupon          = :coupon,
                    coupondiscount  = :coupondiscount,
                    order_status    = :order_status,
                    ip              = :ip,
                    numitems        = :numitems";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':pnref'           => $pnref,
                ':customerid'      => $customer->id,
                ':guestid'         => '',
                ':callerid'        => '',
                ':dealerid'        => '',
                ':partnerid'       => '',
                ':orderamount'     => $totalAmt,
                ':otax_total'      => $sales_tax_data['otax_total'],
                ':otax_state'      => $sales_tax_data['otax_state'],
                ':otax_state_amt'  => $sales_tax_data['otax_state_amt'],
                ':otax_county'     => $sales_tax_data['otax_county'],
                ':otax_county_amt' => $sales_tax_data['otax_county_amt'],
                ':itemamount'      => $_SESSION['pretaxTotal'],
                ':oshipmethod'     => $_SESSION['shipping_method'],
                ':oshipcost'       => number_format($oshipcost, 2, '.', ''),
                ':oshipfirstname'  => $customer->shipping_firstname,
                ':oshiplastname'   => $customer->shipping_lastname,
                ':oshipcompany'    => $customer->shipping_company,
                ':oshipemail'      => $customer->email,
                ':oshipaddress'    => $customer->shipping_address,
                ':oshipaddress2'   => $customer->shipping_address2,
                ':oshipcity'       => $customer->shipping_city,
                ':oshipstate'      => $customer->shipping_state,
                ':oshipzip'        => $customer->shipping_zip,
                ':oshipphone'      => $customer->shipping_phone,
                ':coupon'          => $coupon,
                ':coupondiscount'  => $coupondiscount,
                ':order_status'    => 'pending',
                ':ip'              => $user_ip,
                ':numitems'        => $_SESSION['numberOfItems']
            ];
            $result = $stmt->execute($parameters);

            // success
            if ($result)
            {
                // get order ID & store in variable
                $id = $db->lastInsertId();

                // test - display $cart
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // exit();

                // rename keys in $cart array using array_map
                // resource: http://php.net/manual/en/function.array-map.php
                // resource: https://stackoverflow.com/questions/9605143/how-to-rename-array-keys-in-php
                $cart = array_map(function($tag) {
                    return [
                        'itemid'     => $tag['id'],
                        'name'       => $tag['name'],
                        'qty'        => $tag['quantity'],
                        'unitprice'  => $tag['price'],
                    ];
                }, $cart);

                // test - display new $cart array
                // echo '<h4>New cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // add missing fields to $cart array (using pass by reference '&' prefixed to $item variable)
                // resource: https://stackoverflow.com/questions/12286272/adding-columns-to-existing-php-arrays
                foreach ($cart as &$item)
                {
                    $item['orderid'] = $id;
                    $item['customerid'] = $customer->id;
                    $item['itemtotal'] = $item['qty'] * $item['unitprice'];
                }

                // test
                // echo '<h4>New cart with all fields:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // store updated cart in variable
                $updatedCart = $cart;

                // test
                // echo '<h4>Updated cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // echo '=================<br>';

                // insert order content into orders_content table
                try
                {
                    // establish db connection
                    $db = static::getDB();

                    $sql = "INSERT INTO orders_content SET
                            orderid    = :orderid,
                            customerid = :customerid,
                            itemid     = :itemid,
                            name       = :name,
                            qty        = :qty,
                            unitprice  = :unitprice,
                            itemtotal  = :itemtotal";
                    $stmt = $db->prepare($sql);

                    // iterate over array (note: single loop for multidimensional array)
                    // ** array keys in $updatedCart are identical to table fields **
                    // resource: https://gist.github.com/odan/0c3f80eec13ac493ed64fadd0bb1a66e#insert-multiple-rows
                    $i = 0;
                    $len = count($updatedCart);
                    foreach ($updatedCart as $arr)
                    {
                        $stmt->execute($arr);
                        $i++;
                    }

                    // echo '$i: ' . $i . '<br>';
                    // echo '$len: ' . $len . '<br>';
                    // exit();

                    // confirm foreach loop was successful (iterates over entire length of $updatedCart array)
                    // failure - loop failed
                    if ($i != $len)
                    {
                        // empty $cart & $updatedCart arrays ($_SESSION['cart'] still holds shopping cart data)
                        if( !empty($cart) )  {$cart = [];}
                        if( !empty($updatedCart) ) {$updatedCart = [];}

                        $results = ['result' => false];

                        return $results;
                        exit();
                    }
                    // success
                    else
                    {
                        $results = [
                            'result' => true,
                            'updatedCart' => $updatedCart
                        ];

                        return $results;
                        exit();
                    }
                }
                catch (PDOException $e)
                {
                    echo 'Unable to insert order contents in orders_content table: ' . $e->getMessage();
                    // email webmaster
                    exit();
                }
            }
            else
            {
                echo "Unable to insert order into orders table.";
                // email webmaster
                exit();
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Inserts caller order into orders & orders_content tables
     *
     * @param  Obj      $customer           The caller
     * @param  Array    $cart               Shopping cart content
     * @param  Int      $totalAmt           Total order amount
     * @param  String   $pnref              PayPal transaction ID
     * @param  Decimal  $oshipcost          Shipping cost
     * @param  String   $coupon             Name of coupon
     * @param  String   $coupondiscount     Percentage discount
     * @param  String   $user_ip            Buyer's IP Address
     * @param  Array    $sales_tax_data     Sales tax data
     *
     * @return Array                        Boolean & shopping cart content
     */
    private function insertCallerOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data)
    {
        // insert into `orders` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO orders SET
                    pnref           = :pnref,
                    customerid      = :customerid,
                    guestid         = :guestid,
                    callerid        = :callerid,
                    dealerid        = :dealerid,
                    partnerid       = :partnerid,
                    orderamount     = :orderamount,
                    otax_total      = :otax_total,
                    otax_state      = :otax_state,
                    otax_state_amt  = :otax_state_amt,
                    otax_county     = :otax_county,
                    otax_county_amt = :otax_county_amt,
                    itemamount      = :itemamount,
                    oshipmethod     = :oshipmethod,
                    oshipcost       = :oshipcost,
                    oshipfirstname  = :oshipfirstname,
                    oshiplastname   = :oshiplastname,
                    oshipcompany    = :oshipcompany,
                    oshipemail      = :oshipemail,
                    oshipaddress    = :oshipaddress,
                    oshipaddress2   = :oshipaddress2,
                    oshipcity       = :oshipcity,
                    oshipstate      = :oshipstate,
                    oshipzip        = :oshipzip,
                    oshipphone      = :oshipphone,
                    coupon          = :coupon,
                    coupondiscount  = :coupondiscount,
                    order_status    = :order_status,
                    ip              = :ip,
                    numitems        = :numitems";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':pnref'           => $pnref,
                ':customerid'      => '',
                ':guestid'         => '',
                ':callerid'        => $customer->id,
                ':dealerid'        => '',
                ':partnerid'       => '',
                ':orderamount'     => $totalAmt,
                ':otax_total'      => $sales_tax_data['otax_total'],
                ':otax_state'      => $sales_tax_data['otax_state'],
                ':otax_state_amt'  => $sales_tax_data['otax_state_amt'],
                ':otax_county'     => $sales_tax_data['otax_county'],
                ':otax_county_amt' => $sales_tax_data['otax_county_amt'],
                ':itemamount'      => $_SESSION['pretaxTotal'],
                ':oshipmethod'     => $_SESSION['shipping_method'],
                ':oshipcost'       => number_format($oshipcost, 2, '.', ''),
                ':oshipfirstname'  => $customer->shipping_firstname,
                ':oshiplastname'   => $customer->shipping_lastname,
                ':oshipcompany'    => $customer->shipping_company,
                ':oshipemail'      => $customer->email,
                ':oshipaddress'    => $customer->shipping_address,
                ':oshipaddress2'   => $customer->shipping_address2,
                ':oshipcity'       => $customer->shipping_city,
                ':oshipstate'      => $customer->shipping_state,
                ':oshipzip'        => $customer->shipping_zip,
                ':oshipphone'      => $customer->shipping_phone,
                ':coupon'          => $coupon,
                ':coupondiscount'  => $coupondiscount,
                ':order_status'    => 'pending',
                ':ip'              => $user_ip,
                ':numitems'        => $_SESSION['numberOfItems']
            ];
            $result = $stmt->execute($parameters);

            // success
            if ($result)
            {
                // get order ID & store in variable
                $id = $db->lastInsertId();

                // test - display $cart
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // exit();

                // rename keys in $cart array using array_map
                // resource: http://php.net/manual/en/function.array-map.php
                // resource: https://stackoverflow.com/questions/9605143/how-to-rename-array-keys-in-php
                $cart = array_map(function($tag) {
                    return [
                        'itemid'     => $tag['id'],
                        'name'       => $tag['name'],
                        'qty'        => $tag['quantity'],
                        'unitprice'  => $tag['price'],
                    ];
                }, $cart);

                // test - display new $cart array
                // echo '<h4>New cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // add missing fields to $cart array (using pass by reference '&' prefixed to $item variable)
                // resource: https://stackoverflow.com/questions/12286272/adding-columns-to-existing-php-arrays
                foreach ($cart as &$item) {
                    $item['orderid'] = $id;
                    $item['customerid'] = $customer->id;
                    $item['itemtotal'] = $item['qty'] * $item['unitprice'];
                }

                // test
                // echo '<h4>New cart with all fields:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // store updated cart in variable
                $updatedCart = $cart;

                // test
                // echo '<h4>Updated cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // echo '=================<br>';

                // insert order content into orders_content table
                try
                {
                    // establish db connection
                    $db = static::getDB();

                    $sql = "INSERT INTO orders_content SET
                            orderid    = :orderid,
                            customerid = :customerid,
                            itemid     = :itemid,
                            name       = :name,
                            qty        = :qty,
                            unitprice  = :unitprice,
                            itemtotal  = :itemtotal";
                    $stmt = $db->prepare($sql);

                    // iterate over array (note: single loop for multidimensional array)
                    // ** array keys in $updatedCart are identical to table fields **
                    // resource: https://gist.github.com/odan/0c3f80eec13ac493ed64fadd0bb1a66e#insert-multiple-rows
                    $i = 0;
                    $len = count($updatedCart);
                    foreach ($updatedCart as $arr)
                    {
                        $stmt->execute($arr);
                        $i++;
                    }

                    // echo '$i: ' . $i . '<br>';
                    // echo '$len: ' . $len . '<br>';
                    // exit();

                    // confirm foreach loop was successful (iterates over entire length of $updatedCart array)
                    // failure - loop failed
                    if ($i != $len)
                    {
                        // empty $cart & $updatedCart arrays ($_SESSION['cart'] still holds shopping cart data)
                        if( !empty($cart) )  {$cart = [];}
                        if( !empty($updatedCart) ) {$updatedCart = [];}

                        $results = ['result' => false];

                        return $results;
                        exit();
                    }
                    // success
                    else
                    {
                        $results = [
                            'result' => true,
                            'updatedCart' => $updatedCart
                        ];

                        return $results;
                        exit();
                    }
                }
                catch (PDOException $e)
                {
                    echo 'Unable to insert order contents in orders_content table: ' . $e->getMessage();
                    // email webmaster
                    exit();
                }
            }
            else
            {
                echo "Unable to insert order into orders table.";
                // email webmaster
                exit();
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Inserts guest order into orders & orders_content tables
     *
     * @param  Obj      $customer           The guest
     * @param  Array    $cart               Shopping cart content
     * @param  Int      $totalAmt           Total order amount
     * @param  String   $pnref              PayPal transaction ID
     * @param  Decimal  $oshipcost          Shipping cost
     * @param  String   $coupon             Name of coupon
     * @param  String   $coupondiscount     Percentage discount
     * @param  String   $user_ip            Buyer's IP Address
     * @param  Array    $sales_tax_data     Sales tax data
     *
     * @return Array                        Boolean & shopping cart content
     */
    private function insertGuestOrder($customer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data)
    {
        // insert into `orders` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO orders SET
                    pnref           = :pnref,
                    customerid      = :customerid,
                    guestid         = :guestid,
                    callerid        = :callerid,
                    dealerid        = :dealerid,
                    partnerid       = :partnerid,
                    orderamount     = :orderamount,
                    otax_total      = :otax_total,
                    otax_state      = :otax_state,
                    otax_state_amt  = :otax_state_amt,
                    otax_county     = :otax_county,
                    otax_county_amt = :otax_county_amt,
                    itemamount      = :itemamount,
                    oshipmethod     = :oshipmethod,
                    oshipcost       = :oshipcost,
                    oshipfirstname  = :oshipfirstname,
                    oshiplastname   = :oshiplastname,
                    oshipcompany    = :oshipcompany,
                    oshipemail      = :oshipemail,
                    oshipaddress    = :oshipaddress,
                    oshipaddress2   = :oshipaddress2,
                    oshipcity       = :oshipcity,
                    oshipstate      = :oshipstate,
                    oshipzip        = :oshipzip,
                    oshipphone      = :oshipphone,
                    coupon          = :coupon,
                    coupondiscount  = :coupondiscount,
                    order_status    = :order_status,
                    ip              = :ip,
                    numitems        = :numitems";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':pnref'           => $pnref,
                ':customerid'      => '',
                ':guestid'         => $customer->id,
                ':callerid'        => '',
                ':dealerid'        => '',
                ':partnerid'       => '',
                ':orderamount'     => $totalAmt,
                ':otax_total'      => $sales_tax_data['otax_total'],
                ':otax_state'      => $sales_tax_data['otax_state'],
                ':otax_state_amt'  => $sales_tax_data['otax_state_amt'],
                ':otax_county'     => $sales_tax_data['otax_county'],
                ':otax_county_amt' => $sales_tax_data['otax_county_amt'],
                ':itemamount'      => $_SESSION['pretaxTotal'],
                ':oshipmethod'     => $_SESSION['shipping_method'],
                ':oshipcost'       => number_format($oshipcost, 2, '.', ''),
                ':oshipfirstname'  => $customer->shipping_firstname,
                ':oshiplastname'   => $customer->shipping_lastname,
                ':oshipcompany'    => $customer->shipping_company,
                ':oshipemail'      => $customer->email,
                ':oshipaddress'    => $customer->shipping_address,
                ':oshipaddress2'   => $customer->shipping_address2,
                ':oshipcity'       => $customer->shipping_city,
                ':oshipstate'      => $customer->shipping_state,
                ':oshipzip'        => $customer->shipping_zip,
                ':oshipphone'      => $customer->shipping_phone,
                ':coupon'          => $coupon,
                ':coupondiscount'  => $coupondiscount,
                ':order_status'    => 'pending',
                ':ip'              => $user_ip,
                ':numitems'        => $_SESSION['numberOfItems']
            ];
            $result = $stmt->execute($parameters);

            // success
            if ($result)
            {
                // get order ID & store in variable
                $id = $db->lastInsertId();

                // test - display $cart
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // exit();

                // rename keys in $cart array using array_map
                // resource: http://php.net/manual/en/function.array-map.php
                // resource: https://stackoverflow.com/questions/9605143/how-to-rename-array-keys-in-php
                $cart = array_map(function($tag) {
                    return [
                        'itemid'     => $tag['id'],
                        'name'       => $tag['name'],
                        'qty'        => $tag['quantity'],
                        'unitprice'  => $tag['price'],
                    ];
                }, $cart);

                // test - display new $cart array
                // echo '<h4>New cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // add missing fields to $cart array (using pass by reference '&' prefixed to $item variable)
                // resource: https://stackoverflow.com/questions/12286272/adding-columns-to-existing-php-arrays
                foreach ($cart as &$item) {
                    $item['orderid'] = $id;
                    $item['customerid'] = $customer->id;
                    $item['itemtotal'] = $item['qty'] * $item['unitprice'];
                }

                // test
                // echo '<h4>New cart with all fields:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // store updated cart in variable
                $updatedCart = $cart;

                // test
                // echo '<h4>Updated cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // echo '=================<br>';

                // insert order content into orders_content table
                try
                {
                    // establish db connection
                    $db = static::getDB();

                    $sql = "INSERT INTO orders_content SET
                            orderid    = :orderid,
                            customerid = :customerid,
                            itemid     = :itemid,
                            name       = :name,
                            qty        = :qty,
                            unitprice  = :unitprice,
                            itemtotal  = :itemtotal";
                    $stmt = $db->prepare($sql);

                    // iterate over array (note: single loop for multidimensional array)
                    // ** array keys in $updatedCart are identical to table fields **
                    // resource: https://gist.github.com/odan/0c3f80eec13ac493ed64fadd0bb1a66e#insert-multiple-rows
                    $i = 0;
                    $len = count($updatedCart);
                    foreach ($updatedCart as $arr)
                    {
                        $stmt->execute($arr);
                        $i++;
                    }

                    // echo '$i: ' . $i . '<br>';
                    // echo '$len: ' . $len . '<br>';
                    // exit();

                    // confirm foreach loop was successful (iterates over entire length of $updatedCart array)
                    // failure - loop failed
                    if ($i != $len)
                    {
                        // empty $cart & $updatedCart arrays ($_SESSION['cart'] still holds shopping cart data)
                        if( !empty($cart) )  {$cart = [];}
                        if( !empty($updatedCart) ) {$updatedCart = [];}

                        $results = ['result' => false];

                        return $results;
                        exit();
                    }
                    // success
                    else
                    {
                        $results = [
                            'result' => true,
                            'updatedCart' => $updatedCart
                        ];

                        return $results;
                        exit();
                    }
                }
                catch (PDOException $e)
                {
                    echo 'Unable to insert order contents in orders_content table: ' . $e->getMessage();
                    // email webmaster
                    exit();
                }
            }
            else
            {
                echo "Unable to insert order into orders table.";
                // email webmaster
                exit();
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Inserts Dealer order into orders & orders_content tables
     *
     * @param  Obj      $customer           The customer
     * @param  Array    $cart               Shopping cart content
     * @param  Int      $totalAmt           Total order amount
     * @param  String   $pnref              PayPal transaction ID
     * @param  Decimal  $oshipcost          Shipping cost
     * @param  String   $coupon             Name of coupon
     * @param  String   $coupondiscount     Percentage discount
     * @param  String   $user_ip            Buyer's IP Address
     * @param  Array    $sales_tax_data     Sales tax data
     * @return Array                        Boolean & shopping cart content
     */
    private function insertDealerOrder($dealer, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data)
    {
        // insert into `orders` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO orders SET
                    pnref           = :pnref,
                    customerid      = :customerid,
                    guestid         = :guestid,
                    callerid        = :callerid,
                    dealerid        = :dealerid,
                    partnerid       = :partnerid,
                    orderamount     = :orderamount,
                    otax_total      = :otax_total,
                    otax_state      = :otax_state,
                    otax_state_amt  = :otax_state_amt,
                    otax_county     = :otax_county,
                    otax_county_amt = :otax_county_amt,
                    itemamount      = :itemamount,
                    oshipmethod     = :oshipmethod,
                    oshipcost       = :oshipcost,
                    oshipfirstname  = :oshipfirstname,
                    oshiplastname   = :oshiplastname,
                    oshipcompany    = :oshipcompany,
                    oshipemail      = :oshipemail,
                    oshipaddress    = :oshipaddress,
                    oshipcity       = :oshipcity,
                    oshipstate      = :oshipstate,
                    oshipzip        = :oshipzip,
                    oshipphone      = :oshipphone,
                    coupon          = :coupon,
                    coupondiscount  = :coupondiscount,
                    order_status    = :order_status,
                    ip              = :ip,
                    numitems        = :numitems";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':pnref'           => $pnref,
                ':customerid'      => '',
                ':guestid'         => '',
                ':callerid'        => '',
                ':dealerid'        => $dealer->id,
                ':partnerid'       => '',
                ':orderamount'     => $totalAmt,
                ':otax_total'      => $sales_tax_data['otax_total'],
                ':otax_state'      => $sales_tax_data['otax_state'],
                ':otax_state_amt'  => $sales_tax_data['otax_state_amt'],
                ':otax_county'     => $sales_tax_data['otax_county'],
                ':otax_county_amt' => $sales_tax_data['otax_county_amt'],
                ':itemamount'      => $_SESSION['pretaxTotal'],
                ':oshipmethod'     => $_SESSION['shipping_method'],
                ':oshipcost'       => number_format($oshipcost, 2, '.', ''),
                ':oshipfirstname'  => $dealer->first_name,
                ':oshiplastname'   => $dealer->last_name,
                ':oshipcompany'    => $dealer->company,
                ':oshipemail'      => $dealer->email,
                ':oshipaddress'    => $dealer->address,
                ':oshipcity'       => $dealer->city,
                ':oshipstate'      => $dealer->state,
                ':oshipzip'        => $dealer->zip,
                ':oshipphone'      => $dealer->telephone,
                ':coupon'          => $coupon,
                ':coupondiscount'  => $coupondiscount,
                ':order_status'    => 'pending',
                ':ip'              => $user_ip,
                ':numitems'        => $_SESSION['numberOfItems']
            ];
            $result = $stmt->execute($parameters);

            // success
            if ($result)
            {
                // get order ID & store in variable
                $id = $db->lastInsertId();

                // test - display $cart
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // exit();

                // rename keys in $cart array using array_map
                // resource: http://php.net/manual/en/function.array-map.php
                // resource: https://stackoverflow.com/questions/9605143/how-to-rename-array-keys-in-php
                $cart = array_map(function($tag) {
                    return [
                        'itemid'     => $tag['id'],
                        'name'       => $tag['name'],
                        'qty'        => $tag['quantity'],
                        'unitprice'  => $tag['price'],
                    ];
                }, $cart);

                // test - display new $cart array
                // echo '<h4>New cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // add missing fields to $cart array (using pass by reference '&' prefixed to $item variable)
                // resource: https://stackoverflow.com/questions/12286272/adding-columns-to-existing-php-arrays
                foreach ($cart as &$item)
                {
                    $item['orderid'] = $id;
                    $item['customerid'] = $dealer->id;
                    $item['itemtotal'] = $item['qty'] * $item['unitprice'];
                }

                // test
                // echo '<h4>New cart with all fields:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // store updated cart in variable
                $updatedCart = $cart;

                // test
                // echo '<h4>Updated cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // echo '=================<br>';

                // insert order content into orders_content table
                try
                {
                    // establish db connection
                    $db = static::getDB();

                    $sql = "INSERT INTO orders_content SET
                            orderid    = :orderid,
                            customerid = :customerid,
                            itemid     = :itemid,
                            name       = :name,
                            qty        = :qty,
                            unitprice  = :unitprice,
                            itemtotal  = :itemtotal";
                    $stmt = $db->prepare($sql);

                    // iterate over array (note: single loop for multidimensional array)
                    // ** array keys in $updatedCart are identical to table fields **
                    // resource: https://gist.github.com/odan/0c3f80eec13ac493ed64fadd0bb1a66e#insert-multiple-rows
                    $i = 0;
                    $len = count($updatedCart);
                    foreach ($updatedCart as $arr)
                    {
                        $stmt->execute($arr);
                        $i++;
                    }

                    // echo '$i: ' . $i . '<br>';
                    // echo '$len: ' . $len . '<br>';
                    // exit();

                    // confirm foreach loop was successful (iterates over entire length of $updatedCart array)
                    // failure - loop failed
                    if ($i != $len)
                    {
                        // empty $cart & $updatedCart arrays ($_SESSION['cart'] still holds shopping cart data)
                        if( !empty($cart) )  {$cart = [];}
                        if( !empty($updatedCart) ) {$updatedCart = [];}

                        $results = ['result' => false];

                        return $results;
                        exit();
                    }
                    // success
                    else
                    {
                        $results = [
                            'result' => true,
                            'updatedCart' => $updatedCart
                        ];

                        return $results;
                        exit();
                    }
                }
                catch (PDOException $e)
                {
                    echo 'Unable to insert order contents in orders_content table: ' . $e->getMessage();
                    // email webmaster
                    exit();
                }
            }
            else
            {
                echo "Unable to insert order into orders table.";
                // email webmaster
                exit();
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * Inserts Partner order into orders & orders_content tables
     *
     * @param  Obj      $customer           The customer
     * @param  Array    $cart               Shopping cart content
     * @param  Int      $totalAmt           Total order amount
     * @param  String   $pnref              PayPal transaction ID
     * @param  Decimal  $oshipcost          Shipping cost
     * @param  String   $coupon             Name of coupon
     * @param  String   $coupondiscount     Percentage discount
     * @param  String   $user_ip            Buyer's IP Address
     * @param  Array    $sales_tax_data     Sales tax data
     * @return Array                        Boolean & shopping cart content
     */
    private function insertPartnerOrder($partner, $cart, $totalAmt, $pnref, $oshipcost, $coupon, $coupondiscount, $user_ip, $sales_tax_data)
    {
        // insert into `orders` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO orders SET
                    pnref           = :pnref,
                    customerid      = :customerid,
                    guestid         = :guestid,
                    callerid        = :callerid,
                    dealerid        = :dealerid,
                    partnerid       = :partnerid,
                    orderamount     = :orderamount,
                    otax_total      = :otax_total,
                    otax_state      = :otax_state,
                    otax_state_amt  = :otax_state_amt,
                    otax_county     = :otax_county,
                    otax_county_amt = :otax_county_amt,
                    itemamount      = :itemamount,
                    oshipmethod     = :oshipmethod,
                    oshipcost       = :oshipcost,
                    oshipfirstname  = :oshipfirstname,
                    oshiplastname   = :oshiplastname,
                    oshipcompany    = :oshipcompany,
                    oshipemail      = :oshipemail,
                    oshipaddress    = :oshipaddress,
                    oshipcity       = :oshipcity,
                    oshipstate      = :oshipstate,
                    oshipzip        = :oshipzip,
                    oshipphone      = :oshipphone,
                    coupon          = :coupon,
                    coupondiscount  = :coupondiscount,
                    order_status    = :order_status,
                    ip              = :ip,
                    numitems        = :numitems";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':pnref'           => $pnref,
                ':customerid'      => '',
                ':guestid'         => '',
                ':callerid'        => '',
                ':dealerid'        => '',
                ':partnerid'       => $partner->id,
                ':orderamount'     => $totalAmt,
                ':otax_total'      => $sales_tax_data['otax_total'],
                ':otax_state'      => $sales_tax_data['otax_state'],
                ':otax_state_amt'  => $sales_tax_data['otax_state_amt'],
                ':otax_county'     => $sales_tax_data['otax_county'],
                ':otax_county_amt' => $sales_tax_data['otax_county_amt'],
                ':itemamount'      => $_SESSION['pretaxTotal'],
                ':oshipmethod'     => $_SESSION['shipping_method'],
                ':oshipcost'       => number_format($oshipcost, 2, '.', ''),
                ':oshipfirstname'  => $partner->first_name,
                ':oshiplastname'   => $partner->last_name,
                ':oshipcompany'    => $partner->company,
                ':oshipemail'      => $partner->email,
                ':oshipaddress'    => $partner->address,
                ':oshipcity'       => $partner->city,
                ':oshipstate'      => $partner->state,
                ':oshipzip'        => $partner->zip,
                ':oshipphone'      => $partner->telephone,
                ':coupon'          => $coupon,
                ':coupondiscount'  => $coupondiscount,
                ':order_status'    => 'pending',
                ':ip'              => $user_ip,
                ':numitems'        => $_SESSION['numberOfItems']
            ];
            $result = $stmt->execute($parameters);

            // success
            if ($result)
            {
                // get order ID & store in variable
                $id = $db->lastInsertId();

                // test - display $cart
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // exit();

                // rename keys in $cart array using array_map
                // resource: http://php.net/manual/en/function.array-map.php
                // resource: https://stackoverflow.com/questions/9605143/how-to-rename-array-keys-in-php
                $cart = array_map(function($tag) {
                    return [
                        'itemid'     => $tag['id'],
                        'name'       => $tag['name'],
                        'qty'        => $tag['quantity'],
                        'unitprice'  => $tag['price'],
                    ];
                }, $cart);

                // test - display new $cart array
                // echo '<h4>New cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // add missing fields to $cart array (using pass by reference '&' prefixed to $item variable)
                // resource: https://stackoverflow.com/questions/12286272/adding-columns-to-existing-php-arrays
                foreach ($cart as &$item)
                {
                    $item['orderid'] = $id;
                    $item['customerid'] = $partner->id;
                    $item['itemtotal'] = $item['qty'] * $item['unitprice'];
                }

                // test
                // echo '<h4>New cart with all fields:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // exit();

                // store updated cart in variable
                $updatedCart = $cart;

                // test
                // echo '<h4>Updated cart:</h4>';
                // echo '<pre>';
                // print_r($cart);
                // echo '</pre>';
                // echo '=================<br>';

                // insert order content into orders_content table
                try
                {
                    // establish db connection
                    $db = static::getDB();

                    $sql = "INSERT INTO orders_content SET
                            orderid    = :orderid,
                            customerid = :customerid,
                            itemid     = :itemid,
                            name       = :name,
                            qty        = :qty,
                            unitprice  = :unitprice,
                            itemtotal  = :itemtotal";
                    $stmt = $db->prepare($sql);

                    // iterate over array (note: single loop for multidimensional array)
                    // ** array keys in $updatedCart are identical to table fields **
                    // resource: https://gist.github.com/odan/0c3f80eec13ac493ed64fadd0bb1a66e#insert-multiple-rows
                    $i = 0;
                    $len = count($updatedCart);
                    foreach ($updatedCart as $arr)
                    {
                        $stmt->execute($arr);
                        $i++;
                    }

                    // echo '$i: ' . $i . '<br>';
                    // echo '$len: ' . $len . '<br>';
                    // exit();

                    // confirm foreach loop was successful (iterates over entire length of $updatedCart array)
                    // failure - loop failed
                    if ($i != $len)
                    {
                        // empty $cart & $updatedCart arrays ($_SESSION['cart'] still holds shopping cart data)
                        if( !empty($cart) )  {$cart = [];}
                        if( !empty($updatedCart) ) {$updatedCart = [];}

                        $results = ['result' => false];

                        return $results;
                        exit();
                    }
                    // success
                    else
                    {
                        $results = [
                            'result' => true,
                            'updatedCart' => $updatedCart
                        ];

                        return $results;
                        exit();
                    }
                }
                catch (PDOException $e)
                {
                    echo 'Unable to insert order contents in orders_content table: ' . $e->getMessage();
                    // email webmaster
                    exit();
                }
            }
            else
            {
                echo "Unable to insert order into orders table.";
                // email webmaster
                exit();
            }
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    private static function getDiscountProgramName($coupon)
    {
        // set value of discount coupon
        switch ($coupon)
        {
            // Bersa
            CASE 'rgh196':
                $discountPercentage = 20;
                $program = 'Bersa';
                break;
            // SCCY Code 5
            CASE 'ljh677':
                $discountPercentage = 20;
                $program = 'SCCY Code 5';
                break;
            // SCCY Code 4
            CASE 'ta84blk':
                $discountPercentage = 20;
                $program = 'SCCY Code 4';
                break;
            // SCCY Code 3
            CASE '488gtb':
                $discountPercentage = 20;
                $program = 'SCCY Code 3';
                break;
            // SCCY Code 2
            CASE 'aqw455':
                $discountPercentage = 20;
                $program = 'SCCY Code 2';
                break;
            // SCCY
            CASE 'at48klb':
                $discountPercentage = 20;
                $program = 'SCCY';
                break;
            // Military
            CASE 'service':
                $discountPercentage = 15;
                $program = 'Military';
                break;
            // AGAG18
            CASE 'agag18':
                $discountPercentage = 100;
                $program = 'AGAG18';
                break;
            // NRA Insructor - 69754810
            CASE '69754810':
                $discountPercentage = 20;
                $program = 'NRA Instructor - 69754810';
                break;
            // NRA Insructor - 17220450
            CASE '17220450':
                $discountPercentage = 20;
                $program = 'NRA Instructor - 17220450';
                break;
            // NRA Insructor - 61872495
            CASE '61872495':
                $discountPercentage = 20;
                $program = 'NRA Instructor - 61872495';
                break;
            // TWAW Promotion
            CASE 'perme18':
                $discountPercentage = 10;
                $program = 'TWAW Promotion';
                break;
            // Satisfaction
            CASE 'satisfaction':
                $discountPercentage = 10;
                $program = 'Satisfaction';
                break;
            // SHOT Show 2018
            CASE 'alss18':
                $discountPercentage = 40;
                $program = 'SHOT Show 2018';
                break;
            default:
                $results = [
                    'discountPercentage' => 'error',
                    'program'            => 'error'
                ];
        }
            $results = [
                'discountPercentage' => $discountPercentage,
                'program'            => $program
            ];

        return $results;
    }




    /**
     * Checks if all array values are unique (no duplicates)
     *
     * @param  Array    $serial_numbers     The array of serial numbers
     *
     * @return Boolean
     */
    private static function arrayValuesUnique($serial_numbers)
    {
        // resource: https://stackoverflow.com/questions/3145607/php-check-if-an-array-has-duplicates?noredirect=1&lq=1
        if ( count($serial_numbers) == count(array_unique($serial_numbers)) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }



    /**
     * Checks if any serial numbers in array exist in `orders_content`
     *
     * @param  Array    $serial_numbers     The serial number(s) being added
     * @return Boolean
     */
    private static function serialNumbersExist($serial_numbers)
    {
        // convert array to string
        $serialNumbersString = implode(',', $serial_numbers);

        // echo $serialNumbersString; exit();

        try
        {
            $db = static::getDB();

            // itemid range: TR Series 1000-1999, GTO/FLX 2000-2999, Stingray 3000-3999
            $sql = "SELECT * FROM orders_content
                    WHERE serial_number IN ($serialNumbersString)
                    AND itemid BETWEEN 1000 AND 3999";
            $stmt  = $db->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (count($result) > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch (PDOException $e)
        {
            echo "Error adding serial number to orders content table: $e->getMessage()";
            // email webmaster
            exit();
        }
    }


}
