<?php

namespace App\Controllers;

use \Core\View;

/**
 * Exchangeprogram controller
 *
 * PHP versioni 7.0
 */
class ExchangeProgram extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('ExchangeProgram/index.html', [
            'pagetitle' => 'Exchange Program',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }

}
