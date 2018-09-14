<?php

namespace App\Controllers;

use \Core\View;


class Testimonials extends \Core\Controller
{
    public function indexAction()
    {
        View::renderTemplate('Testimonials/index.html', [
            'pagetitle'   => 'Testimonials',
            'activemenu'  => 'testimonials',
            'canonURL'  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
        ]);
    }




    public function addTestimonialAction()
    {
        View::renderTemplate('Testimonials/add-testimonial.html', [
            'pagetitle'   => 'Add Testimonial',
            'activemenu'  => 'addtestimonial'
        ]);
    }
}
