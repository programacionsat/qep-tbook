<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set("Europe/Madrid");

class Docs extends CI_Controller {

    //  Muestra 
    public function index() {

        $this->load->view("templates/header_simple");
        $this->load->view("docs/inicio");
        $this->load->view("templates/footer_simple");
        
    }

    public function manual_usuario() {

        $this->load->view("templates/header_simple");
        $this->load->view("docs/manual_usuario");
        $this->load->view("templates/footer_simple");

    }

}
