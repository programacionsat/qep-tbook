<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

defined('BASEPATH') OR exit('No direct script access allowed');

class Tools extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model("tbook_model");
        $this->load->model("mysql_model");
    }

    public function index() {
        
        if(!$this->input->is_cli_request()) {
            echo "<strong>Este script solo se puede utilizar desde línea de comandos</strong>" . PHP_EOL;
            return;
        }
    }

    //  Busca y actualiza las incidencias del día de hoy
    public function actualizar() {

        if($this->input->is_cli_request()) {
            echo "[" . date("Y-m-d H:i:s") . "] Obteniendo incidencias INICIO" . PHP_EOL;

            echo "[" . date("Y-m-d H:i:s") . "] Vaciando tabla de incidencias" . PHP_EOL;

            if (!$this->mysql_model->eliminar_incidencias_hoy()) {
                echo "Error al eliminar las incidencias de hoy" . PHP_EOL;
                exit();
            }

            echo "[" . date("Y-m-d H:i:s") . "] Buscando incidencias" . PHP_EOL;

            $data["incidencias"] = $this->tbook_model->obtener_incidencias_hoy();

            echo "[" . date("Y-m-d H:i:s") . "] Almacenando incidencias" . PHP_EOL;
            foreach ($data["incidencias"] as $incidencia) {
                // print_r($reclamacion);

                if (!$this->mysql_model->insertar_incidencia($incidencia)) {
                    echo "Error insertando incidencia" . PHP_EOL;
                    exit();
                }

            }

            //  Actualizamos la fecha de última actualización
            $this->mysql_model->actualizar_fecha();

            //print_r($data);

            echo "[" . date("Y-m-d H:i:s") . "] Obteniendo incidencias FIN" . PHP_EOL;
        } else {
            echo "<strong>Este script solo se puede utilizar desde línea de comandos</strong>" . PHP_EOL;
        }

    }

    
    //  Busca las incidencias entre un rango de fechas, elimina las que 
    //  pudiese haber en base de datos en ese tiempo y las inserta
    //  [!] "fecha_hasta" no se incluye
    public function obtener_incidencias($fecha_desde, $fecha_hasta) {

        if($this->input->is_cli_request()) {
            echo "[" . date("Y-m-d H:i:s") . "] Obteniendo incidencias desde *{$fecha_desde}* hasta {$fecha_hasta} INICIO" . PHP_EOL;

            echo "[" . date("Y-m-d H:i:s") . "] Vaciando tabla de incidencias" . PHP_EOL;

            if (!$this->mysql_model->eliminar_incidencias($fecha_desde, $fecha_hasta)) {
                echo "Error al eliminar las incidencias de {$fecha_desde} hasta {$fecha_hasta}" . PHP_EOL;
                exit();
            }

            echo "[" . date("Y-m-d H:i:s") . "] Buscando incidencias" . PHP_EOL;

            $data["incidencias"] = $this->tbook_model->obtener_incidencias($fecha_desde, $fecha_hasta);

            foreach ($data["incidencias"] as $incidencia) {
                // print_r($incidencia);

                $this->mysql_model->insertar_incidencia($incidencia);
            }

            //print_r($data);

            //  Actualizamos la fecha de última actualización
            $this->mysql_model->actualizar_fecha();

            echo "[" . date("Y-m-d H:i:s") . "] Obteniendo incidencias FIN" . PHP_EOL;
        } else {
            echo "<strong>Este script solo se puede utilizar desde línea de comandos</strong>" . PHP_EOL;
        }
    }

}
