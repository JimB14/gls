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
        // get target directory where shipping labels are stored    
       $target_dir = $this->getTargetDirectory($_SERVER['SERVER_NAME'], 'usps');

        // get images from target directory & store in array
        $images = scandir($target_dir);

        // test
        // echo '<pre>';
        // print_r($images);
        // echo '</pre>';
        // exit();

        // declare empty array for foreach loop
        $newArr = [];

        // separate jpg files & store in $newArr
        foreach ($images as $image)
        {
            // echo '$image: ' . $image . '<br>';
            $file_parts = pathinfo($image);
            // echo 'dirname: ' . $file_parts['dirname'] . '<br>';
            // echo 'basename: ' . $file_parts['basename'] . '<br>';
            // echo 'filename: ' . $file_parts['filename'] . '<br>';
            // echo 'extension: ' . $file_parts['extension'] . '<br><br>';
            if ($file_parts['extension'] == 'jpg')
            {
                // $newArr[] = $file_parts['filename'];
                $newArr[] = $image;
            }
        }

        // test
        // echo '<pre>';
        // print_r($newArr);
        // echo '</pre>';
        // exit();

        View::renderTemplate('Admin/Armalaser/Show/labels.html', [
            'pagetitle' => 'USPS Labels',
            'images'    => $newArr,
            'shipper'   => 'USPS'
        ]);
    }



    /**
     * Retrieves shipping labels & displays in view
     *
     * @return View
     */
    public function getUpsLabelsAction()
    {
       // get target directory where shipping labels are stored    
       $target_dir = $this->getTargetDirectory($_SERVER['SERVER_NAME'], 'ups');

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
        // get target directory where shipping labels are stored    
        $target_dir = $this->getTargetDirectory($_SERVER['SERVER_NAME'], 'ups');
        
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

            // get target directory where shipping labels are stored    
            $target_dir = $this->getTargetDirectory($_SERVER['SERVER_NAME'], $shipper);

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


    /**
     *  Deletes selected image from server; returns user to referer page
     * 
     * @return Boolean
     */
    public function deleteAction() 
    {
        // echo "Connected to deleteAction() in Admin/Labels Controller!";

        // retrieve query string parameters
        $image   = (isset($_REQUEST['file'])) ? filter_var($_REQUEST['file'], FILTER_SANITIZE_STRING) : '';
        $shipper = (isset($_REQUEST['shipper'])) ? filter_var($_REQUEST['shipper'], FILTER_SANITIZE_STRING) : '';

        // convert to lower case
        $shipper = strtolower($shipper);

        // echo $image . '<br>';
        // echo $shipper . '<br>';

        // get target directory where shipping labels are stored    
        $target_dir = $this->getTargetDirectory($_SERVER['SERVER_NAME'], $shipper);

        // create full path to file being deleted    
        $full_path = $target_dir . $image;

        // echo 'Target directory: ' . $target_directory . '<br>';
        // echo 'Full path: ' . $full_path . '<br>';

        // delete image from server
        $result = unlink($full_path);

        // future development: remove XML file of same name

        if ($result) 
        {
            // success message
            // echo '<script>';
            // echo 'alert("The shipping label was successfully deleted! \n\nIf previously downloaded, it will remain on your local hard-drive until you delete it.")';
            // echo '</script>';

            if ($shipper == 'usps')
            {
                // return to same page
                echo '<script>';
                echo 'window.location.href="/admin/labels/get-usps-labels"';
                echo '</script>';
                exit();
            } 
            // shipper is UPS
            else 
            {
                //  set URL for current window
                echo '<script>';
                echo 'window.location.href="/admin/labels/get-ups-labels"';
                echo '</script>';
                exit();
            }
        }
        // failure
        else 
        {
            echo "Error attempting to delete shipping label.";
            // email webmaster
            exit();
        }
    }




    private function getTargetDirectory($server_name, $shipper)
    {
        // Assign target directory based on server
        if ($server_name != 'localhost')
        {
            // production - IMH
            $target_dir = Config::UPLOAD_PATH . "/assets/shipping_labels/$shipper/";
        }
        else
        {
            // development
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/shipping_labels/$shipper/";
        }

        // test
        // echo $target_dir;
        // exit();

        return $target_dir;
    }

}
