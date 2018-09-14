<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Accessory;


class Touchactivation extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Touchactivation/index.html', [
            'pagetitle' => 'What is touch activation?',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }




    public function gloveContacts()
    {
        // get accessories
        $accessory = Accessory::getAccessoryById(4);

        // test
        // echo '<pre>';
        // print_r($accessory);
        // echo '</pre>';
        // echo $accessory->buy_link;
        // exit();

        View::renderTemplate('Touchactivation/glove-contacts.html', [
            'pagetitle' => 'Glove Contacts',
            'buy_link'  => $accessory->buy_link,
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }
}
