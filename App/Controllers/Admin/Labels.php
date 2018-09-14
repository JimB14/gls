<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\User;
use \App\Models\Endicia;
use \App\Models\UPS;
use \App\Models\Order;
use \App\Config;


/**
 * Admin controller
 *
 * PHP version 7.0
 */
class Labels extends \Core\Controller
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
            || $_SESSION['userType'] == 'partner'
            || $_SESSION['userType'] == 'customer')
        {
            header("Location: /");
            exit();

        }
    }


    /**
     * Show the Admin Panel index page
     *
     * @return void
     */
    public function indexAction()
    {


    }



    public function getUspsLabelsAction()
    {
        // Assign target directory based on server
        if ($_SERVER['SERVER_NAME'] != 'localhost')
        {
            // production - IMH
            $target_dir = Config::UPLOAD_PATH . '/assets/shipping_labels/usps/';
        }
        else
        {
            // development
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '\assets\shipping_labels\usps\\';
        }

        // store images in array
        $images = scandir($target_dir);

        // declare empty array for foreach loop
        $newArr = [];

        // separate jpg files & store in $newArr
        foreach ($images as $image)
        {
            $file_parts = pathinfo($image);
            if ($file_parts['extension'] == 'jpg')
            {
                $newArr[] = $image;
            }
        }

        View::renderTemplate('Admin/Armalaser/Show/labels.html', [
            'pagetitle' => 'USPS Labels',
            'images' => $newArr,
            'shipper' => 'USPS'
        ]);
    }



    /**
     * Retrieves shipping labels & displays in view
     *
     * @return View
     */
    public function getUpsLabelsAction()
    {
        // Assign target directory based on server
        if ($_SERVER['SERVER_NAME'] != 'localhost')
        {
            // production - IMH
            $target_dir = Config::UPLOAD_PATH . '/assets/shipping_labels/ups/';
        }
        else
        {
            // development
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '\assets\shipping_labels\ups\\';
        }

        // store images in array
        $images = scandir($target_dir);

        // declare empty array for foreach loop
        $newArr = [];

        // separate jpg files & store in $newArr
        foreach ($images as $image)
        {
            $file_parts = pathinfo($image);
            if ($file_parts['extension'] == 'jpg')
            {
                $newArr[] = $image;
            }
        }

        View::renderTemplate('Admin/Armalaser/Show/labels.html', [
            'pagetitle' => 'UPS Labels',
            'images' => $newArr,
            'shipper' => 'UPS'
        ]);
    }




    /**
     * Retrieves UPS return labels & displays in view
     *
     * @return View
     */
    public function getUpsReturnLabelsAction()
    {
        // Assign target directory based on server
        if ($_SERVER['SERVER_NAME'] != 'localhost')
        {
            // production - IMH
            $target_dir = Config::UPLOAD_PATH . '/assets/shipping_labels/ups/';
        }
        else
        {
            // development
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/shipping_labels/ups/';
        }

        // test
        // echo $target_dir;
        // exit();

        // store images from target directory in array
        $images = scandir($target_dir);

        // test
        // echo '<h4>shipping_labels/ups/ folder/directory content:</h4>';
        // echo '<pre>';
        // print_r($images);
        // echo '</pre>';
        // exit();

        // declare empty array for foreach loop
        $returnLabels = [];

        // separate jpg files & store in $newArr
        foreach ($images as $image)
        {
            // get image parts
            $file_parts = pathinfo($image);

            // find pdf extension
            if ($file_parts['extension'] == 'pdf')
            {
                // remove (17) characters after last character of UPS tracking
                // number & store in associative array
                $returnLabels[]['image'] = substr($image, 0, -17);
            }
        }

        // test
        // echo '<h4>PDF files in $returnLabels: </h4>';
        // echo '<pre>';
        // print_r($returnLabels);
        // echo '</pre>';
        // exit();

        // get orders
        $orders = Order::getOrders($status='return-pending');

        // test
        // echo '<pre>';
        // print_r($orders);
        // echo '</pre>';
        // exit();

        // find image & tracking number matches & store in array
        foreach ($orders as $order)
        {
            foreach($returnLabels as $key => $label)
            {
                if ($order->trackingcode == $label['image'])
                {
                    // store return label url in array
                    $returnLabels[$key]['url'] = $order->return_label_url;
                }
            }
        }

        // test
        // echo '<h4>$returnLabels after loop: </h4>';
        // echo '<pre>';
        // print_r($returnLabels);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/labels-ups-return.html', [
            'pagetitle' => 'UPS Return Labels',
            'labels'    => $returnLabels,
            'shipper'   => 'UPS'
        ]);
    }



    /**
     * Downloads image file to user's local machine
     *
     * @return Boolean
     */
    public function downloadAction()
    {

        if(isset($_REQUEST["file"]))
        {
            $shipper = (isset($_REQUEST['shipper'])) ? filter_var($_REQUEST['shipper'], FILTER_SANITIZE_STRING): '';

            // Assign target directory based on server
            if ($_SERVER['SERVER_NAME'] != 'localhost')
            {
                // production - IMH
                $target_dir = Config::UPLOAD_PATH . '/assets/shipping_labels/'.$shipper.'/';
            }
            else
            {
                // development
                $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/shipping_labels/'.$shipper.'/';
            }

            // Get parameters
            $file = urldecode($_REQUEST["file"]); // Decode URL-encoded string
            $filepath = $target_dir . $file;

            // echo $filepath; exit();

            // Process download
            if(file_exists($filepath))
            {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                flush(); // Flush system output buffer
                readfile($filepath);
                exit;
            }
        }
    }

}
