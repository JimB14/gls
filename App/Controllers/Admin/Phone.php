<?php

namespace App\Controllers\Admin;

use \App\Models\Trseries;
use \App\Models\Gtoflx;
use \App\Models\Stingray;
use \App\Models\Holster;
use \App\Models\Battery;
use \App\Models\Toolkit;
use \App\Models\Accessory;
use \App\Models\Flx;
use \Core\View;
use \App\Models\State;
use \App\Models\Customer;
use \App\Models\Order;
use \App\Models\Product;
use \App\Models\Coupon;
use \App\Mail;


class Phone extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // redirect user not logged in to root
        if(!isset($_SESSION['user']))
        {
            header("Location: /");
            exit();
        }

        // redirect logged in user that is not supervisor or admin
        if (isset($_SESSION['user']) && $_SESSION['userType'] == 'dealer'
            || $_SESSION['userType'] == 'partner')
        {
            header("Location: /");
            exit();

        }
    }


    /**
     *  Retrieves customers & products & displays phone order page
     */
    public function orderAction()
    {
        // echo "Connected to phoneOrders()";

        // get states
        $states = State::getStates();

        // get customers
        $customers = Customer::getCustomersByType($type='customer');

        // get customer count
        $customer_count = Customer::getCount();

        // echo '<pre>'; print_r($trseries); echo '</pre>';

        View::renderTemplate('Admin/Armalaser/Show/Phone/order.html', [
            'pagetitle'      => 'Phone Order',
            'states'         => $states,
            'customers'      => $customers,
            'customer_count' => $customer_count,
            'activetab'      => 'customer',
        ]);
    }




    /**
     *  @return Array   The customers
     */
    public function searchAction()
    {
        // retrieve form data
        $searchword = (isset($_REQUEST['searchword'])) ? filter_var($_REQUEST['searchword'], FILTER_SANITIZE_STRING) : '';

        // trim data
        $searchword = str_replace(' ', '', $searchword);

        // get customers
        $customers = Customer::searchCustomers($searchword);

        View::renderTemplate('Admin/Armalaser/Show/Phone/order.html', [
            'pagetitle'  => 'Phone Order',
            'customers'  => $customers,
            'searchword' => $searchword
        ]);
    }



    public function orderStepTwoAction()
    {
        // echo "Connected.";

        $id = $this->route_params['id'];

        // get customer
        $customer = Customer::getCustomer($id);

        // store customer ID in SESSION variable
        $_SESSION['phone_cust_id'] = $customer->id;

        // echo $_SESSION['phone_cust_id']; echo '<pre>'; print_r($customer); echo '</pre>';

        View::renderTemplate('Admin/Armalaser/Show/Phone/order-step-two.html', [
            'pagetitle' => 'Phone Order',
            'customer'  => $customer,
            'activetab' => 'customer'
        ]);
    }



    /**
     *  Retrieves all products from all product tables
     *
     * @return View
     */
    public function itemsAction()
    {
        // echo 'Connected to items() in Phone controller!';

        if (isset($_SESSION['phone_cust_id']))
        {
            // get customer
            $customer = Customer::getCustomer($_SESSION['phone_cust_id']);
        }
        else
        {
            $customer = '';
        }

        // echo '<pre>'; print_r($customer); echo '</pre>'; exit();

        // get all products
        $products = Product::get();

        $count = count($products);

        // echo '<pre>'; print_r($products); echo '</pre>'; exit();



// ============= TESTING ====================================================================== //

        // $coupon = Coupon::getCoupon(36);

        // // check if item or items in cart matches items in promotion
        // $itemIds = $this->itemsInPromotion($coupon, $_SESSION['cart']);

        // echo '<pre>';
        // echo '<h3>$itemIds array:</h3>';
        // print_r($itemIds);
        // echo '</pre>';
        // echo '<pre>';
        // print_r($_SESSION['cart']);
        // echo '</pre>';
        // // exit();

        // // create array of qualifying items & quantity of each
        // $newItemIds = [];
        // foreach ($_SESSION['cart'] as $item) {
        //     foreach ($itemIds as $value) {
        //         if ($item['id'] == $value) {
        //             $newItemIds[$item['id']]['quantity'] = $item['quantity'];
        //         }
        //     }
        // }

        // echo '<pre>';
        // echo '<h3>$newItemIds array:</h3>';
        // print_r($newItemIds);
        // echo '</pre>';
        // exit();

// ========================================================================================== //


        View::renderTemplate('Admin/Armalaser/Show/Phone/items.html', [
            'pagetitle' => 'Phone Order',
            'products'  => $products,
            'count'     => $count,
            'customer'  => $customer,
            'selectedtab' => 'items'
        ]);
    }



    public function addItemAction()
    {
        // retrieve query string data
        $customerid = (isset($_REQUEST['customerid'])) ? filter_var($_REQUEST['customerid'], FILTER_SANITIZE_STRING) : '';
        $productid  = (isset($_REQUEST['productid'])) ? filter_var($_REQUEST['productid'], FILTER_SANITIZE_STRING) : '';
        $mvc_model  = (isset($_REQUEST['model'])) ? filter_var($_REQUEST['model'], FILTER_SANITIZE_STRING) : '';

        // throw error if customer not selected
        if ($customerid == '') {
            echo '<script>alert("No customer selected.")</script>';
            echo '<script>history.back(-1)</script>';
            exit();
        }

        // add item to shopping cart
        $result = $this->addToCart($productid, $mvc_model);

    }



    public function searchItemsAction()
    {
        // retrive searchword from form
        $searchword = (isset($_REQUEST['searchword'])) ? filter_var($_REQUEST['searchword'], FILTER_SANITIZE_STRING) : '';

        // get products
        $products = Product::find($searchword);

        if (isset($_SESSION['phone_cust_id']))
        {
            // get customer
            $customer = Customer::getCustomer($_SESSION['phone_cust_id']);
        }
        else
        {
            $customer = '';
        }

        $count = count($products);

        // echo '<pre>'; print_r($products); echo '</pre>'; exit();

        View::renderTemplate('Admin/Armalaser/Show/Phone/items.html', [
            'pagetitle'  => 'Phone Order',
            'products'   => $products,
            'count'      => $count,
            'customer'   => $customer,
            'searchword' => $searchword,
            'activetab'  => 'items'
        ]);
    }



    /**
     * Deletes item from $_SESSION['cart']
     *
     * @return
     */
    public function deleteItemAction()
    {
        $refererURL = $_SERVER['HTTP_REFERER'];

        // retrieve form data
        $id = (isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '');

        // remove item from array
        unset($_SESSION['cart'][$id]);

        // unset item from dealer/partner cart
        if (isset($_SESSION['ids']))
        {
            // find position of deleted ID in $_SESSION['ids']
            $pos = array_search($id, $_SESSION['ids']);

            // remove from array
            unset($_SESSION['ids'][$pos]);
        }

        // unset SESSION variables if cart empty
        if(empty($_SESSION['cart']))
        {
            unset($_SESSION['total_discount']);
            unset($_SESSION['promo_id']);
            unset($_SESSION['promo_name']);
        }

        // update session cart total
        $_SESSION['cart_total'] = $this->getCartTotal();

        // view updated cart
        header("Location: $refererURL");
        exit();
    }



    /**
     * Updates item quantity in $_SESSION['cart']
     *
     * @return View
     */
    public function updateUnitPriceAction()
    {
        $refererURL = $_SERVER['HTTP_REFERER'];

        // retrieve form data
        $newPrice = (isset($_REQUEST['new_price']) ? filter_var($_REQUEST['new_price']) : '');
        $id = (isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '');

        // format price
        $newPrice = number_format($newPrice, 2, '.', '');

        // test
            // echo '<h4>Price change:</h4>';
            // echo '<pre>';
            // print_r($_REQUEST);
            // echo '</pre>';
            // echo $newPrice . '<br>';
            // // echo $customerid . '<br>';
            // // echo $type . '<br>';
            // // exit();
            // echo '<h4>SESSION cart array BEFORE change.</h4>';
            // echo '<pre>';
            // print_r($_SESSION['cart']);
            // echo '</pre>';
            // exit();

        // loop thru array to find item being updated
        foreach($_SESSION['cart'] as $item)
        {
            if ($item['id'] == $id) {
                // update price
                $_SESSION['cart'][$id]['price'] = $newPrice;
            }
        }

        // test
            // echo '<h4>SESSION cart array AFTER change.</h4>';
            // echo '<pre>';
            // print_r($_SESSION['cart']);
            // echo '</pre>';
            // exit();

        // get cart total
        $_SESSION['cart_total'] = $this->getCartTotal();

        // return to same route
        header("Location: $refererURL");
        exit();
    }



    /**
     * Updates item quantity in $_SESSION['cart']
     *
     * @return View
     */
    public function updateQuantityAction()
    {
        $refererURL = $_SERVER['HTTP_REFERER'];

        // retrieve form data
        $newQty = (isset($_REQUEST['new_quantity']) ? filter_var($_REQUEST['new_quantity'], FILTER_SANITIZE_NUMBER_INT) : '');
        $id = (isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '');

        // test
            // echo '<h4>Quantity change:</h4>';
            // echo '<pre>';
            // print_r($_REQUEST);
            // echo '</pre>';
            // echo '<h4>SESSION cart array before change.</h4>';
            // echo '<pre>';
            // print_r($_SESSION['cart']);
            // echo '</pre>';
            // exit();

        // loop thru array to find item being updated
        foreach($_SESSION['cart'] as $item)
        {
            if ($item['id'] == $id) {
                // update quantity
                $_SESSION['cart'][$id]['quantity'] = $newQty;
            }
        }

        // test
            // echo '<h4>Updated SESSION cart array.</h4>';
            // echo '<pre>';
            // print_r($_SESSION['cart']);
            // echo '</pre>';
            // exit();

        // get cart total
        $_SESSION['cart_total'] = $this->getCartTotal();

        // return to same route
        header("Location: $refererURL");
        exit();
    }



    /**
     *  Checks if coupon code is valid
     *
     * @return Array        The response data
     *
     */
    public function ajaxCheckCoupon()
    {
        // echo "Connected to ajaxCheckCoupon() in Phone controller.";

        $coupon = (isset($_REQUEST['phone_coupon'])) ? filter_var($_REQUEST['phone_coupon'], FILTER_SANITIZE_STRING) : '';
        $customer_id = (isset($_REQUEST['customer_id'])) ? filter_var($_REQUEST['customer_id'], FILTER_SANITIZE_STRING) : '';

        // trim & convert to lowercase
        $coupon = trim(strtolower($coupon));

        // get coupon
        $coupon = Coupon::getByCode($coupon);

        // get customer
        $customer = Customer::getCustomer($customer_id);

        // test
            // echo json_encode($coupon); exit();
            // echo json_encode($this->getCartTotal()); exit();
            // echo json_encode($customer); exit();

        // coupon found
        if (!empty($coupon))
        {
            // declare array
            $results = [];

            // check if enabled
            if ($coupon->enabled != 1)
            {
                $results = [
                    'status' => false,
                    'reason' => 'Coupon is not enabled.'
                ];
                echo json_encode($results);
                exit();
            }

            // test
                // echo $coupon->promo_end; exit();
                // echo $_SESSION['nowMySQL']; exit();

            // check if expired
            if ($_SESSION['nowMySQL'] > $coupon->promo_end)
            {
                $results = [
                    'status' => false,
                    'reason' => 'Coupon has expired.'
                ];
                echo json_encode($results);
                exit();
            }

            // check if under max_uses setting
            if ($coupon->max_uses - $coupon->uses_count < 1)
            {
                $results = [
                    'status' => false,
                    'reason' => 'Coupon usage has reached its limit.'
                ];
                echo json_encode($results);
                exit();
            }

            // check if customer has uses remaining (boolean)
            $result = $this->isCustomerEligibleToUseCoupon($coupon, $customer);

            if (!$result)
            {
                $results = [
                    'status' => false,
                    'reason' => 'Coupon use per customer limit has been reached.'
                ];
                echo json_encode($results);
                exit();
            }

            // check if item or items in cart matches items in promotion
            $itemIds = $this->itemsInPromotion($coupon, $_SESSION['cart']);

            // echo json_encode($itemIds); exit();

            // no items qualify for promotion discount
            if (!$itemIds)
            {
                $results = [
                    'status' => false,
                    'reason' => 'Items in cart do not match items in the ' . $coupon->promo_name . ' promotion.'
                ];
                echo json_encode($results);
                exit();
            }

            // create array of qualifying items & quantity of each
                // $newItemIds = [];
                // foreach ($_SESSION['cart'] as $item) {
                //     foreach ($itemIds as $value) {
                //         if ($item['id'] == $value) {
                //             $newItemIds[$item['id']]['id'] = $value;
                //             $newItemIds[$item['id']]['name'] = $item['name'];
                //             $newItemIds[$item['id']]['price'] = $item['price'];
                //             $newItemIds[$item['id']]['quantity'] = $item['quantity'];
                //         }
                //     }
                // }

            // echo json_encode($newItemIds); exit();

            // get product info for items where discount will apply
            $products = $this->getProductDetails($itemIds);

            // echo json_encode($products); exit();

            // add quantities to item or items in $products
                // foreach($products as &$product) {
                //     foreach($newItemIds as $key => $value) {
                //         if ($product['id'] == $value['id']) {
                //             $product['quantity'] = $value['quantity'];
                //         }
                //     }
                // }

                // array_replace_recursive — Replaces elements from passed arrays into the first array recursively
                // $products_with_quantity = array_replace_recursive($products, $newItemIds);

            // echo json_encode($products_with_quantity); exit();


            // coupon is a percentage discount
            if ($coupon->discount_type == 'percentage')
            {
                $results = [
                    'status'        => true,
                    'promo_name'    => ucfirst($coupon->promo_name),
                    'discount_type' => $coupon->discount_type,
                    'discount'      => $coupon->discount,
                    'products'      => $products
                    // 'newProducts'   => $products_with_quantity
                ];

                // store coupon name in global variable
                $_SESSION['this_coupon'] = $coupon->promo_name;

                echo json_encode($results);
                exit();
            }
            // coupon is dollar amount off item
            else if ($coupon->discount_type == 'item')
            {
                // future development for different type, e.g. dollar amount
            }
        }
        // no coupon found
        {
            $results = [
                'status' => false,
                'reason' => 'Coupon not found.'
            ];
            echo json_encode($results);
            exit();
        }
    }


    /**
     *  Applies discount to shopping cart content
     */
    public function applyCouponAction()
    {
        // hidden inputs
        $coupon = (isset($_REQUEST['coupon_code'])) ? filter_var($_REQUEST['coupon_code'], FILTER_SANITIZE_STRING) : '';
        $customer_id = (isset($_REQUEST['customer_id'])) ? filter_var($_REQUEST['customer_id'], FILTER_SANITIZE_STRING) : '';
        $coupon = trim(strtolower($coupon));

        $refererURL = $_SERVER['HTTP_REFERER'];

        $coupon = Coupon::getByCode($coupon);

        // check if item or items in cart matches items in promotion
        $itemIds = $this->itemsInPromotion($coupon, $_SESSION['cart']);

        // echo '<pre>'; print_r($itemIds); exit();

        // store values in global variable
        $_SESSION['promo_name'] = ucfirst($coupon->promo_name);
        $_SESSION['promo_id']  = $coupon->id;

        // echo '<pre>';print_r($coupon); echo '</pre>'; $this->getCartTotal();

        // coupon is a percentage discount
        if ($coupon->discount_type == 'percentage')
        {
            // add new elements so number of tokens will match for all items to
            // be inserted into `orders_content`
            foreach ($_SESSION['cart'] as &$item) {
                foreach ($item as $key => $value) {
                    $item['discount_applied'] = '';
                    $item['promo_name'] = '';
                    $item['promo_id'] = '';
                }
            }

            // test
                // echo '<h3>Cart BEFORE coupon applied.</h3>';
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // exit();

            /* *************************************************************************************
             * apply discount to each item in cart                                                 *
             * NOTE: in foreach loops, values are assigned to copies. Therefore, to change the     *
             * actual value of the array, you must add '&' before the iteration variable:          *
             * $item becomes &$item.                                                               *
             * Resource:  http://php.net/manual/en/control-structures.foreach.php                  *
             ***************************************************************************************/
            // modify price of eligible items in cart by the amount of the discount and change in $_SESSION['cart]
            foreach($_SESSION['cart'] as &$item) {
                foreach($itemIds as $key => $value) {

                    if ($item['id'] == $value) {

                        // calculate & store total discount in new element before applying it
                        $item['discount_applied'] = number_format($item['price'] * ($coupon->discount / 100), 2);

                        // apply discount to item
                        $item['price'] = number_format( ($item['price']) * ((100 - $coupon->discount) / 100), 2);

                        // add new elements to session array
                        $item['promo_name'] = $coupon->promo_name;
                        $item['promo_id'] = $coupon->id;
                    }
                }
            }

            // test
                // echo '<h3>Cart AFTER coupon applied: </h3>';
                // echo '<pre>';
                // print_r($_SESSION['cart']);
                // echo '</pre>';
                // echo 'Cart total: ' . $this->getCartTotal();
                // exit();

            // initialize new $_SESSION variable
            $_SESSION['total_discount'] = 0;

            // store sum of discounts for order
            foreach($_SESSION['cart'] as &$item)
            {
                if(isset($item['discount_applied'])) {
                    // echo '<br>*discount_applied: ' . $item['discount_applied'] . ': item qty: ' . $item['quantity'] . '*';
                    $_SESSION['total_discount'] += ($item['discount_applied']) ? $item['discount_applied'] * $item['quantity'] : 0;
                }
            }

            // store current total in SESSION variable
            $_SESSION['cart_total'] = $this->getCartTotal();

            // echo '<br>Total discount: ' . $_SESSION['total_discount']; exit();

            header("Location: $refererURL");
            exit();
        }
        // coupon is dollar amount off item
        else if ($coupon->discount_type == 'item')
        {
           // future development for discount applied to item or purchase
        }
    }




    public function ajaxUpdateCartTotal()
    {
        $result = $_REQUEST['res'];

        // echo '<pre>'; print_r($result);

        // update SESSION['cart_total]
        $_SESSION['cart_total'] = $result['new_total'];

        echo 'success';
    }




    // - - - - - - Class functions - - - - - - - - - - - - - - - - - - - //

    /**
     *  Retrieves product data by ID
     *  @param $itemIds     Array       The IDs
     *  @return Object
     */
    private function getProductDetails($itemIds)
    {
        $itemStr = implode(',', $itemIds);

        $products = Product::getProductsById($itemStr);

        return $products;
    }

    /**
     *  @param $coupon      The coupon object
     *  @param $cart        The shopping cart
     *  @return Boolean
     */
    private function itemsInPromotion($coupon, $cart)
    {
        // test & notes
            //echo json_encode($coupon); exit();
            // echo json_encode($cart); exit();

            // get IDs of items in promotion
        $promotionIds = Coupon::getPromotionItemIds($coupon->id);

        // test & notes
            // echo json_encode($promotionIds); exit();
            // check ids against cart content (pass numeric $ids array & $cart array)
        $validIds = $this->itemsInCartThatMatchPromotionIds($promotionIds, $cart);

        // test
            // echo json_encode($validIds); exit();

        if ($validIds)
        {
            return $validIds;
        }
        else
        {
            return false;
        }
    }



    /**
     *  Retrieves items in shopping cart that match items in promotion
     *
     *  @param $ids         IDs of items in promotion
     *  @param $cart        Shopping cart content
     *  @return
     */
    private function itemsInCartThatMatchPromotionIds($promotionIds, $cart)
    {
        // test
            // echo 'Inside itemsInCartThatMatchPromotionIds() of Phone controller.'; exit();
            // echo json_encode($promotionIds); exit();
            // echo json_encode($cart); exit();

        $validIds = [];
        foreach($cart as $key => $value) {
            foreach($promotionIds as $key2 => $value2) {
                if($key == $value2[0]) {
                    $validIds[] = $value2[0];
                }
            }
        }
        if (!empty($validIds))
        {
            return $validIds;
        }
        else
        {
            return false;
        }
    }


    /**
     *  Determines if customer is eligible for coupon entered
     *
     *  @param $coupon       The coupon object
     *  @param $customer     The customer object
     *  @return Boolean
     */
    private function isCustomerEligibleToUseCoupon($coupon, $customer)
    {
        //  test
            // echo json_encode($coupon); exit();
            // echo json_encode($customer); exit();

        $rowCount = Coupon::getTimesUsed($coupon->id, $customer->id);

        // echo json_encode('The rowCount: ' . $rowCount); exit();

        if ($rowCount == 0 || $rowCount < $coupon->uses_per_customer)
        {
            return true;
        }
        else
        {
            return false;
        }
    }


    private function isCouponValid($coupon)
    {
        if ($coupon->promo_end > $_SESSION['nowMySQL'])
        {
           return true;
        }
        else
        {
            return false;
        }
    }


    private function getCartTotal()
    {
        // calculate total in cart
        $total = 0;
        foreach($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }


    /**
     * adds item to shopping cart
     *
     * @return
     */
    private function addToCart($productid, $mvc_model)
    {
        // test
            // echo 'Connected to AddToCart() method in Cart controller';
            // echo '<pre>';
            // print_r($_SERVER);
            // echo '</pre>';
            // exit();

        // store referer URL in variable
        $refererURL = $_SERVER['HTTP_REFERER'];

        // test
            // echo $refererURL . '<br>';
            // echo 'Model: ' . $mvc_model . '<br>';
            // echo $id . '<br>';
            // echo $pistolMfr . '<br>';
            // echo $trseries_model . '<br>';
            // echo $_SESSION['cart_count'] . '<br>';
            // exit();

        // determine query by value of $mvc_model
        switch ($mvc_model) {
            case 'trseries':
                $this->item = Trseries::getTrseriesDetailsForCart($productid);
                break;
            case 'gtoflx':
                $this->item = Gtoflx::getGtoflxDetailsForCart($productid);
                break;
            case 'stingray':
                $this->item = Stingray::getStingrayDetailsForCart($productid, $pistolMfr = '');
                break;
            case 'holster':
                $this->item = Holster::getHolsterDetailsForAdminCart($productid);
                break;
            case 'battery':
                $this->item = Battery::getBattery($productid);
                break;
            case 'toolkit':
                $this->item = Toolkit::getToolkit($productid);
                break;
            case 'accessory':
                $this->item = Accessory::getAccessory($productid);
                break;
            case 'flx':
                $this->item = Flx::getFlx($productid);
                break;
            default:
                echo "Error with value of table in switch statement.";
        }

        // test
            // echo "Data from query";
            // echo '<pre>';
            // print_r($this->item);
            // echo '</pre>';
            // exit();

        // set starting quanity value
        $quantity = 1;

        /* - - - - - - - lasers  - - - - - - - - */
        if ($mvc_model == 'trseries' || $mvc_model == 'gtoflx' || $mvc_model == 'stingray')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->series) . ' ' . strtoupper($this->item->model).')
                    already in cart. You can modify quantity from inside the
                    <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            else
            {
                // store items in array
                $this->newItem = [
                    'id'            => $this->item->id,
                    'name'          => $this->item->name,
                    'thumb'         => $this->item->thumb,
                    'series'        => $this->item->series,
                    'model'         => $this->item->model,
                    'beam'          => $this->item->beam,
                    'quantity'      => $quantity,
                    'price'         => $this->item->price,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model,
                    'weight'        => $this->item->weight
                ];
            }
        }

        /* - - - - - - - holsters  - - - - - - - - */
        if ($mvc_model == 'holster')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->holsterMfr) . ' ' . strtoupper($this->item->holster_model).') already in cart. You can modify
                    quantity from inside the <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            if ($mvc_model == 'holster')
            {
                // store items in array
                $this->newItem = [
                    'id'            => $this->item->id,
                    'name'          => $this->item->name,
                    'holsterMfr'    => $this->item->holsterMfr,
                    'model'         => $this->item->holster_model,
                    'waist'         => $this->item->waist,
                    'hand'          => $this->item->hand,
                    'quantity'      => $quantity,
                    'price'         => $this->item->price,
                    'thumb'         => $this->item->thumb,
                    'trseriesModel' => $this->item->trseries_model,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model
                    // 'weight'        => $this->item->weight
                ];
            }
        }


        /* - - - - - - - batteries  - - - - - - - - */
        if ($mvc_model == 'battery' || $mvc_model == 'toolkit' || $mvc_model == 'accessory')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->name) .') already in cart. You can modify
                    quantity from inside the <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            if ($mvc_model == 'battery' || $mvc_model == 'toolkit' || $mvc_model == 'accessory')
            {
                // store items in array
                $this->newItem = [
                    'id'          => $this->item->id,
                    'name'        => $this->item->name,
                    'description' => $this->item->description,
                    'quantity'    => $quantity,
                    'price'       => $this->item->price,
                    'thumb'       => $this->item->image_thumb,
                    'mvc_model'   => $mvc_model,
                    'weight'      => $this->item->weight
                ];
            }
        }


        /* - - - - - - - flx  - - - - - - - - */
        if ($mvc_model == 'flx')
        {
            if(isset($_SESSION['cart'][$this->item->id]))
            {
                View::renderTemplate('Error/index.html', [
                    'errorMessage' => 'Item ('.strtoupper($this->item->flx_model) . ') already in cart. You can modify
                    quantity from inside the <a style="color:blue;" href="/cart/view/shopping-cart">cart</a>'
                ]);
                exit();
            }
            else
            {
                // store items in array
                $this->newItem = [
                    'id'            => $this->item->id,
                    'name'          => $this->item->name,
                    'thumb'         => $this->item->thumb,
                    'model'         => $this->item->flx_model,
                    'quantity'      => $quantity,
                    'price'         => $this->item->price,
                    'pistolMfr'     => $this->item->pistolMfr,
                    'pistol_models' => $this->item->pistol_models,
                    'mvc_model'     => $mvc_model,
                    'weight'        => $this->item->weight
                ];
            }
        }

        // test
            // echo '<h4>newitem array</h4>';
            // echo '<pre>';
            // print_r($this->newItem);
            // echo '</pre>';
            // exit();

        // add new item to SESSION cart
        $_SESSION['cart'][$this->item->id] = $this->newItem;

        // test
            // echo 'SESSION cart array.';
            // echo '<pre>';
            // print_r($_SESSION['cart']);
            // echo '</pre>';
            // exit();

        // get count (best to use TWIG for count, e.g. {{ session.cart|length }})
        $_SESSION['cart_count'] = count($_SESSION['cart']);

        // store cart total in session variable
        $_SESSION['cart_total'] = $this->getCartTotal();

        // test
            // echo 'Cart count: ' . $_SESSION['cart_count'] . '<br>';
            // echo 'SESSION array.';
            // echo '<pre>';
            // print_r($_SESSION['cart']);
            // echo '</pre>';
            // exit();

        // add query string to referer URL
        // $refererURL = $refererURL . '?id=itemadded';

        header("Location: $refererURL");
        exit();
    }




}
