<?php
/**
 * Front controller
 *
 * PHP  version 7.0
 */

/**
 * Composer
 */
// loads class files; eliminates need to require files to use the class
require '../vendor/autoload.php';

// must come AFTER autoloader for classes to be known to SESSION variable
session_start();

// resource: http://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes
// destroy session after 45 minutes of inactivity
// check if user logged in
if ( isset($_SESSION['user_id']) )
{
    // check user inactivity against limit
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 3600)) /* 3600 */
    {
        // last request was more than 60 minutes ago
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage

        // clear $cartContent array
        $cartContent = null;
        unset($cartContent);

        // clear $cartMetaData array
        $cartMetaData = null;
        unset($cartMetaData);

        header("Location: /admin/timedout");
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
}

// check if chat user logged in
if ( isset($_SESSION['chatuser_id']) )
{
    // check user inactivity against limit
    if (isset($_SESSION['LAST_CHAT_USER_ACTIVITY']) && (time() - $_SESSION['LAST_CHAT_USER_ACTIVITY'] > 7200)) /* 2700 */
    {
        // last request was more than 30 minutes ago
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage

        header("Location: /chatlogin?timeout=timeout");
        exit();

    }
    $_SESSION['LAST_CHAT_USER_ACTIVITY'] = time(); // update last activity time stamp
}

/* get date & time data  */
// get current date and time in MySQL DATETIME format
$timezone =  new \DateTimeZone("America/New_York");
$date = new \DateTime("now", $timezone);
$now = $date->format("m-d-Y"); // matches MySQL format
$current_hour = $date->format('H');
$today = $date->format('D');
$current_date = $date->format('d');
$current_month = $date->format('M');
$current_year = $date->format('Y');


$_SESSION['thisMonth'] = $current_month;
$_SESSION['thisDay']   = $today;
$_SESSION['thisDate']  = $current_date;
$_SESSION['thisMonth'] = $current_month;
$_SESSION['thisYear']  = $current_year;
$_SESSION['thisHour']  = $current_hour;
$_SESSION['now'] = $now;

// remove YEAR to make reusable regardless of year (e.g. 12-25-2017 becomes 12-25)
$_SESSION['nowMMDD'] = substr($now, 0, 5);


/* - - - - - -  shoppping cart - - - - - - - - - */
// Check if cart exists; if not, initialize SESSION cart array
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// If cart has content, count elements to display in navbar
if (isset($_SESSION['cart'])) {
    $_SESSION['cart_count'] = count($_SESSION['cart']);
}


/**
 * Twig
 */
Twig_Autoloader::register();


/**
 * Error and Exception handling
 */
error_reporting(E_ALL); // — Sets which PHP errors are reported
// set_error_handler — Sets a user-defined error handler function
// call static errorHandler() method in Core/Error class
set_error_handler('Core\Error::errorHandler');
// set_exception_handler — Sets a user-defined exception handler function
// call static exceptionHandler() method in Core/Error class
set_exception_handler('Core\Error::exceptionHandler');

// test - display query string
// echo '$_SERVER[\'QUERY_STRING\'] = ' . $_SERVER['QUERY_STRING'] . '<br><br>';
// exit();


/**
 * Routing
 */
// create new Router object
$router = new Core\Router();

// test
// echo '<pre>';
// print_r($router);
// echo '</pre>';
// exit();

// $router->add('products/{mfrId:[0-9]+}', ['controller' => 'Products', 'action' => 'show']);
// $router->add('beta-holsters', ['controller' => 'Beta',  'action' => 'index']);

// Add routes (argument 1: route, argument 2: parameters (controller & action))
$router->add('', ['controller' => 'Home',  'action' => 'index']);
$router->add('home', ['controller' => 'Home',  'action' => 'index']);

// = = = = = =  new routes = = = = = = = = = = = //

// products by pistol manufacturer route (https://armalaser.com/glock)
$router->add('{manufacturer:[A-za-z -]+}', ['controller' => 'Products', 'action' => 'showProductsByPistolMfr']);

// tr-series show laser route (https://armalaser.com/glock/tr-series/1/tr1)
$router->add('{pistolMfr:[A-za-z -]+}/{pistolModel:[a-zA-z0-9 -]+}/tr-series/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}', ['controller' => 'TrseriesController', 'action' => 'showTrseries']);

// gto/flx-series show laser route (https://armalaser.com/glock/gto-flx/1/gto-flx1)
$router->add('{pistolMfr:[A-za-z -]+}/{pistolModel:[a-zA-z0-9 -]+}/gto-flx/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}', ['controller' => 'GtoflxController', 'action' => 'showGtoflx']);

// stingray show route (https://armalaser.com/products/lasers/stingray/glock/1/sr1)
$router->add('products/lasers/stingray/{pistolMfr:[A-za-z -]+}/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}', ['controller' => 'StingrayController', 'action' => 'showStingray']);

$router->add('add-to-cart/{mvcModel:[a-z -]+}/{id:[0-9 -]+}/{pistolMfr:[a-z0-9 -]+}/{laserModel:[a-z0-9 -]+}', ['controller' => 'Cart', 'action' => 'addToCart']);
$router->add('admin/dealer/add-to-cart/{mvcModel:[a-z -]+}/{id:[0-9 -]+}/{pistolMfr:[a-z0-9 -]+}/{laserModel:[a-z0-9 -]+}', ['controller' => 'Admin\Dealercart', 'action' => 'addToCart']);
$router->add('admin/partner/add-to-cart/{mvcModel:[a-z -]+}/{id:[0-9 -]+}/{pistolMfr:[a-z0-9 -]+}/{laserModel:[a-z0-9 -]+}', ['controller' => 'Admin\Partnercart', 'action' => 'addToCart']);

// view shopping cart
$router->add('cart/view/shopping-cart', ['controller' => 'Cart', 'action' => 'viewCart']);

// click checkout button
$router->add('cart/checkout', ['controller' => 'Cart', 'action' => 'checkoutOptions']);
$router->add('cart/checkout/guest', ['controller' => 'Cart', 'action' => 'guestCheckout']);
$router->add('cart/checkout/internal', ['controller' => 'Cart', 'action' => 'internalCheckout']);

$router->add('cart/checkout/order-summary', ['controller' => 'Cart', 'action' => 'checkoutCalculate']);
$router->add('cart/checkout/dealer-order-summary', ['controller' => 'Cart', 'action' => 'checkoutDealerCalculate']);
$router->add('cart/checkout/partner-order-summary', ['controller' => 'Cart', 'action' => 'checkoutPartnerCalculate']);
$router->add('cart/checkout/process-discount', ['controller'=>'Cart', 'action'=> 'processDiscount']);
$router->add('cart/checkout/process-payment', ['controller'=>'Cart', 'action'=> 'processPayment']);



$router->add('lasers/tr-series', ['controller' => 'TrseriesController', 'action' => 'trseries']);
$router->add('lasers/gto-flx-series', ['controller' => 'GtoflxController', 'action' => 'gtoflx']);
$router->add('lasers/stingray-classic', ['controller' => 'StingrayController', 'action' => 'stingray']);

$router->add('products/accessories', ['controller' => 'Accessories', 'action' => 'index']);
$router->add('products/holsters', ['controller' => 'Holsters', 'action' => 'index']);
$router->add('products/laserglow-targets', ['controller' => 'Targets', 'action' => 'index']);
$router->add('products/flx', ['controller' => 'Flxs', 'action' => 'index']);

// show flx
$router->add('flx/{pistolMfr:[a-zA-Z0-9 -]+}/{pistolModel:[a-zA-z0-9 -]+}/{id:[0-9 -]+}', ['controller' => 'Flxs', 'action' => 'showFlx']);

$router->add('chat/chat', ['controller' => 'Chats', 'action' => 'chat']);
$router->add('chat/livechat', ['controller' => 'Chats', 'action' => 'index']);
$router->add('chat/login', ['controller' => 'Chats', 'action' => 'login']);
$router->add('chat/login-user', ['controller' => 'Chats', 'action' => 'login-user']);
$router->add('chat/logout', ['controller' => 'Logout', 'action' => 'chat']);


$router->add('help/contact', ['controller' => 'Contact', 'action' => 'index']);
$router->add('help/testimonials', ['controller' => 'Testimonials', 'action' => 'index']);
$router->add('help/frequently-asked-questions', ['controller' => 'FAQ', 'action' => 'index']);

// = = = = = Customer routes = = = = = = = = = = = = //

// serve customer registration form
$router->add('admin/customer/register', ['controller' => 'Register', 'action' => 'index']);
// serve `customer` login page
$router->add('admin/customer/login', ['controller' => 'Login', 'action' => 'index']);
$router->add('admin/customer/get-new-password',  ['controller' => 'Admin\Customers', 'action' => 'forgotPassword']); // note path to controller in different namespace
// process request for new password
$router->add('admin/customer/get-password', ['controller' => 'Admin\Customers', 'action' => 'getPassword']); // note path to controller in different namespace

// = = = = = Admin routes = = = = = = = = = = = = //

// serves Admin login page
$router->add('admin/user/login', ['controller' => 'Login', 'action' => 'adminLogIn']);
// serve get admin user new password
// $router->add('admin/user/get-new-password', ['controller' => 'Login', 'action' => 'forgotPassword']);


// = = = = = Partner routes = = = = = = = = = = = = //

// serve `partner` registration page
$router->add('admin/partner/register', ['controller' => 'Register', 'action' => 'partnerRegister']);
// serve `partner` login page
$router->add('admin/partner/login', ['controller' => 'Login', 'action' => 'partnerLogIn']);
// process `partner` login
$router->add('admin/partner/process-login', ['controller' => 'Login', 'action' => 'loginPartner']);
// serve get partner new password page
$router->add('admin/partner/get-new-password', ['controller' => 'Admin\Partners', 'action' => 'forgotPassword']); // note path to controller in different namespace
// process request for new password
$router->add('admin/partner/get-password', ['controller' => 'Admin\Partners', 'action' => 'getPassword']); // note path to controller in different namespace


// = = = = = Dealer routes = = = = = = = = = = = = //

// serve `dealer` registration page
$router->add('admin/dealer/register', ['controller' => 'Register', 'action' => 'dealerRegister']);
// serve `dealer` login page
$router->add('admin/dealer/login', ['controller' => 'Login', 'action' => 'dealerLogIn']);
// process `dealer` login
$router->add('admin/dealer/process-login', ['controller' => 'Login', 'action' => 'loginDealer']);
// serve get `dealer` new password page
$router->add('admin/dealer/get-new-password', ['controller' => 'Admin\Dealers', 'action' => 'forgotPassword']); // note path to controller in different namespace
// process request for new password
$router->add('admin/dealer/get-password', ['controller' => 'Admin\Dealers', 'action' => 'getPassword']); // note path to controller in different namespace


// admin logout
$router->add('admin/logout', ['controller' => 'Logout', 'action' => 'index']);
$router->add('admin/timedout', ['controller' => 'Logout', 'action' => 'timedout']);

$router->add('shows/gun-shows', ['controller' => 'Shows', 'action' => 'index']);
$router->add('shows/gun-shows-all', ['controller' => 'Shows', 'action' => 'allshows']);
$router->add('program/laser-exchange', ['controller' => 'ExchangeProgram', 'action' => 'index']);
$router->add('program/firearm-instructor-program', ['controller' => 'Instructors', 'action' => 'index']);

// = = = = = =   more new routes   = = = = = = = = = = = //

// laser series owner's manuals
$router->add('manual/gto-flx-series-laser-owners-manual', ['controller' => 'GtoflxController', 'action' => 'gtoFlxSeriesLaserOwnersManual']);
$router->add('manual/tr-series-laser-owners-manual', ['controller' => 'TrseriesController', 'action' => 'trSeriesLaserOwnersManual']);
$router->add('manual/stingray-classic-laser-owners-manual', ['controller' => 'StingrayController', 'action' => 'stingrayClassicLaserOwnersManual']);

// products by pistol manufacturer route (https://armalaser.com/products/manufacturer/glock)
// $router->add('products/manufacturer/{manufacturer:[A-za-z -]+}', ['controller' => 'Products', 'action' => 'showProductsByPistolMfr']);
// laser for pistol route (https://armalaser.com/products/lasers/tr-series/glock/1/tr1)
// $router->add('products/lasers/tr-series/{pistolMfr:[A-za-z -]+}/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}', ['controller' => 'TrseriesController', 'action' => 'showTrseries']);
// laser for pistol route (https://armalaser.com/products/lasers/gto-flx/glock/37/gto-flx19)
// $router->add('products/lasers/gto-flx/{pistolMfr:[A-za-z -]+}/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}', ['controller' => 'GtoflxController', 'action' => 'showGtoflx']);


// holsters by TR Series laser ID
$router->add('products/holsters/{pistolMfr:[a-zA-z0-9 -]+}/{laserId:[0-9 -]+}/{laserModel:[A-za-z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesId']);

// batteries page
$router->add('products/laser-batteries', ['controller' => 'Accessories', 'action' => 'getBatteries']);

// laser parts page
$router->add('products/laser-parts', ['controller' => 'Accessories', 'action' => 'getToolkits']);

// holster by holster ID
$router->add('products/holster/{id:[0-9 -]+}/{laserModel:[a-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'showHolster']);

// holster models by TR Series ID & holster model name
$router->add('search/holsters/MiniTuck/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/MiniSlide/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/SnapSlide/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/SuperTuck/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Mini Scabbard/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Mini Scabbard/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Mini Scabbard-42/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Mini Scabbard-43/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Insider/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Insider-42/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Insider-43/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/Nemesis/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);

$router->add('search/holsters/MD-4-Modified/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/LG-6-Short/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/SM-3-Small/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/LG-6-Long/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/MD-4-Gen-1-Medium/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/SM-2/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);
$router->add('search/holsters/SM-5-Modified/{id:[0-9 -]+}/{model:[a-s-zA-Z0-9 -]+}', ['controller' => 'Holsters', 'action' => 'byTrseriesIdAndHolsterModel']);


// Battery route (https://armalaser.com/laser-accessories/1/tr-series-battery)
$router->add('laser-accessories/battery/{id:[0-9 -]+}/{slug:[a-zA-z0-9 -]+}',  ['controller' => 'Accessories', 'action' => 'showBattery']);

// Toolkit route (https://armalaser.com/laser-accessories/1/gto-flx-series-toolkit)
$router->add('laser-accessories/toolkit/{id:[0-9 -]+}/{slug:[a-zA-z0-9 -]+}',  ['controller' => 'Accessories', 'action' => 'showToolkit']);

// Accessories route (https://armalaser.com/laser-accessories/1/gto-flx-series-toolkit)
$router->add('laser-accessories/accessory/{id:[0-9 -]+}/{slug:[a-zA-z0-9 -]+}',  ['controller' => 'Accessories', 'action' => 'showAccessory']);

// find dealer
$router->add('info/find-armalaser-authorized-dealer', ['controller' => 'Dealers', 'action' => 'findDealer']);

// become authorized dealer
$router->add('info/become-armalaser-authorized-dealer', ['controller' => 'Dealers', 'action' => 'index']);
// = = = = = =  end new routes = = = = = = = = //


$router->add('info/touch-activation', ['controller' => 'Touchactivation', 'action' => 'index']);
$router->add('admin/subscribe', ['controller' => 'Subscribe', 'action' => 'index']);
// $router->add('admin/register', ['controller' => 'Register', 'action' => 'index']);
$router->add('info/laser-warranty', ['controller' => 'Warranty', 'action' => 'index']);


// $router->add('login/login-user', ['controller' => 'Login', 'action' => 'login-user']);


// get customer data by type
$router->add('admin/{controller}/{action}/{buyerType:[A-za-z -]+}/{id:\d+}', ['namespace' => 'Admin']); // assign namespace

$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']); // assign the namespace
$router->add('admin/dealers/{controller}/{action}', ['namespace' => 'Admin\Dealers']); // assign namespace
$router->add('admin/partners/{controller}/{action}', ['namespace' => 'Admin\Partners']); // assign namespace
$router->add('admin/customers/{controller}/{action}', ['namespace' => 'Admin\Customers']); // assign namespace


$router->add('api/{controller}/{action}', ['namespace' => 'Api']); // assign namespace
// get customer data route
$router->add('api/{controller}/{action}/{key:[A-za-z0-9 -]+}', ['namespace' => 'Api']); // assign namespace

//  = = = = =  must be at bottom of list  = = = = = = = = =
// (https://armalaser.com/glock/43/price)
// $router->add('{manufacturer:[A-za-z -]+}/{model:[A-Za-z0-9 -]+}/{param:[A-Za-z0-9 -]+}', ['controller' => 'Search', 'action' => 'byPistolMakeModel']);

$router->add('{controller}/{action}');
$router->add('{controller}/{id:\d+}/{action}'); // 'id' can be anything
$router->add('{controller}/{action}/{id:\d+}'); // controller, action and id can be in any order
$router->add('{controller}/{action}/{manufacturer}/{model}/{id:\d+}');
$router->add('{controller}/{action}/{manufacturer:[A-za-z -]+}');
$router->add('{controller}/{action}/{model:[A-za-z0-9 -]+}');






// call dispatch method of Router class
$router->dispatch($_SERVER['QUERY_STRING']);
