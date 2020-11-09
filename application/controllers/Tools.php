<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

defined('BASEPATH') OR exit('No direct script access allowed');

ini_set("memory_limit", "2048M");

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

    //  Busca y actualiza las incidencias del día de **hoy** tanto en la tabla 
    //  de incidencias de hoy como en el histórico
    public function actualizar() {

        if($this->input->is_cli_request()) {
            echo "[" . date("Y-m-d H:i:s") . "] Obteniendo incidencias INICIO" . PHP_EOL;

            echo "[" . date("Y-m-d H:i:s") . "] Eliminando incidencias" . PHP_EOL;

            //  Eliminamos las incidencias actuales en la tabla de hoy 
            //  y en el histórico.
            if (!$this->mysql_model->eliminar_incidencias_hoy()) {
                echo "Error al eliminar las incidencias de hoy" . PHP_EOL;
                exit();
            }

            if (!$this->mysql_model->eliminar_incidencias(date("Y-m-d"), date("Y-m-d"))) {
                echo "Error al eliminar las incidencias de hoy en el histórico" . PHP_EOL;
                exit();
            }

            echo "[" . date("Y-m-d H:i:s") . "] Buscando incidencias" . PHP_EOL;

            $data["incidencias"] = $this->tbook_model->obtener_incidencias_hoy();

            echo "[" . date("Y-m-d H:i:s") . "] Almacenando incidencias" . PHP_EOL;
            foreach ($data["incidencias"] as $incidencia) {
                // print_r($reclamacion);

                if (!$this->mysql_model->insertar_incidencia("hoy", $incidencia)) {
                    echo "Error insertando incidencia en la tabla de incidencias actuales" . PHP_EOL;
                    exit();
                }

                if (!$this->mysql_model->insertar_incidencia("historico", $incidencia)) {
                    echo "Error insertando incidencia en el histórico" . PHP_EOL;
                    exit();
                }

            }


            //  Buscar soportes y actualizar salida
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Soporte*" . PHP_EOL;
            if (!$this->mysql_model->actualizar_salida_soporte("hoy", date("Y-m-d"))) {
                echo "Error actualizando salida *Soporte*" . PHP_EOL;
                exit();
            }

            if (!$this->mysql_model->actualizar_salida_soporte("historico", date("Y-m-d"))) {
                echo "Error actualizando salida *Soporte*" . PHP_EOL;
                exit();
            }


            //  Actualizar salida *Correlado a NTT*
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Correlado a NTT*" . PHP_EOL;
            if (!$this->mysql_model->actualizar_salida_correlado_ntt("hoy", date("Y-m-d"))) {
                echo "Error actualizando salida *Correlado a NTT*" . PHP_EOL;
                exit();
            }

            if (!$this->mysql_model->actualizar_salida_correlado_ntt("historico", date("Y-m-d"))) {
                echo "Error actualizando salida *Correlado a NTT*" . PHP_EOL;
                exit();
            }


            //  Actualizar salida *Visita*
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Visita*" . PHP_EOL;
            $incidencias_hoy = $this->mysql_model->obtener_incidencias_hoy();

            $tickets_visita = [];
            foreach ($incidencias_hoy as $incidencia) {
                $hay_visita = $this->tbook_model->obtener_salida_visita($incidencia["id_ticket"]);

                if (count($hay_visita) != 0) {
                    $tickets_visita[] = $incidencia["id_ticket"];
                }
            }

            foreach ($tickets_visita as $ticket) {

                if (!$this->mysql_model->actualizar_salida_visita("hoy", $ticket)) {
                    echo "Error actualizando salida *Visita*" . PHP_EOL;
                    exit();
                }

                if (!$this->mysql_model->actualizar_salida_visita("historico", $ticket)) {
                    echo "Error actualizando salida *Visita*" . PHP_EOL;
                    exit();
                }
            }


            //  Actualizar salida *Escalado a O&M*
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Escalado a O&M*" . PHP_EOL;

            $tickets_escalados_oym = [];
            foreach ($incidencias_hoy as $incidencia) {
                $hay_escalados_oym = $this->tbook_model->obtener_salida_oym($incidencia["id_ticket"]);

                if (count($hay_escalados_oym) != 0) {
                    $tickets_escalados_oym[] = $incidencia["id_ticket"];
                }
            }

            foreach ($tickets_escalados_oym as $ticket) {

                if (!$this->mysql_model->actualizar_salida_oym("hoy", $ticket)) {
                    echo "Error actualizando salida *Escalado a O&M*" . PHP_EOL;
                    exit();
                }

                if (!$this->mysql_model->actualizar_salida_oym("historico", $ticket)) {
                    echo "Error actualizando salida *Escalado a O&M*" . PHP_EOL;
                    exit();
                }
            }


            //  Actualizar salida *Otros*
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Otros*" . PHP_EOL;
            if (!$this->mysql_model->actualizar_salida_otros("hoy", date("Y-m-d"))) {
                echo "Error actualizando salida *Otros*" . PHP_EOL;
                exit();
            }

            if (!$this->mysql_model->actualizar_salida_otros("historico", date("Y-m-d"))) {
                echo "Error actualizando salida *Otros*" . PHP_EOL;
                exit();
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

    //  ToDo
    //      [] Añadir código para obtener las salidas (¿¿o crear un método solo para ello??)
    public function obtener_incidencias($fecha_desde, $fecha_hasta) {

        if($this->input->is_cli_request()) {
            echo "[" . date("Y-m-d H:i:s") . "] Obteniendo incidencias desde *{$fecha_desde}* hasta {$fecha_hasta} INICIO" . PHP_EOL;

            echo "[" . date("Y-m-d H:i:s") . "] Vaciando tabla de incidencias" . PHP_EOL;

            if (!$this->mysql_model->eliminar_incidencias($fecha_desde, $fecha_hasta)) {
                echo "Error al eliminar las incidencias de {$fecha_desde} hasta {$fecha_hasta}" . PHP_EOL;
                exit();
            }

            echo "[" . date("Y-m-d H:i:s") . "] Buscando incidencias" . PHP_EOL;
            $incidencias = $this->tbook_model->obtener_incidencias($fecha_desde, $fecha_hasta);

            echo "[" . date("Y-m-d H:i:s") . "] Insertando incidencias" . PHP_EOL;
            foreach ($incidencias as $incidencia) {
                // print_r($incidencia);

                $this->mysql_model->insertar_incidencia("historico", $incidencia);
            }


            //  Buscar soportes y actualizar salida
            //  ---------------------------------------------------------------
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Soporte*" . PHP_EOL;
            if (!$this->mysql_model->actualizar_salida_soporte("historico", date("Y-m-d"))) {
                echo "Error actualizando salida *Soporte*" . PHP_EOL;
                exit();
            }


            //  Actualizar salida *Correlado a NTT*
            //  ---------------------------------------------------------------
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Correlado a NTT*" . PHP_EOL;
            if (!$this->mysql_model->actualizar_salida_correlado_ntt("historico", date("Y-m-d"))) {
                echo "Error actualizando salida *Correlado a NTT*" . PHP_EOL;
                exit();
            }


            //  Actualizar salida *Visita y O&M*
            //  ---------------------------------------------------------------
            echo "[" . date("Y-m-d H:i:s") . "] Buscando salida *Visita* y *Escalado a O&M*" . PHP_EOL;

            $tickets_visita = [];
            $tickets_escalados_oym = [];

            // DEBUG
            //echo count($incidencias) . " incidencias" . PHP_EOL;

            //$num_incidencias = 1;
            foreach ($incidencias as $incidencia) {

                //echo "[{$num_incidencias}] Ticket: {$incidencia["ID_TICKET"]}" . PHP_EOL;

                $hay_visita = $this->tbook_model->obtener_salida_visita($incidencia["ID_TICKET"]);

                $hay_escalados_oym = $this->tbook_model->obtener_salida_oym($incidencia["ID_TICKET"]);

                if (count($hay_escalados_oym) != 0) {
                    $tickets_escalados_oym[] = $incidencia["ID_TICKET"];
                }

                if (count($hay_visita) != 0) {
                    $tickets_visita[] = $incidencia["ID_TICKET"];
                }

                //$num_incidencias++;
            }

            // DEBUG
            echo count($incidencias) . " incidencias" . PHP_EOL;

            //  Actualizar salida *Visita*
            //  ---------------------------------------------------------------
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Visita*" . PHP_EOL;
            foreach ($tickets_visita as $ticket) {

                if (!$this->mysql_model->actualizar_salida_visita("historico", $ticket)) {
                    echo "Error actualizando salida *Visita*" . PHP_EOL;
                    exit();
                }
            }


            //  Actualizar salida *Escalado a O&M*
            //  ---------------------------------------------------------------
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Escalado a O&M*" . PHP_EOL;
            foreach ($tickets_escalados_oym as $ticket) {
                /*
                if (!$this->mysql_model->actualizar_salida_oym("hoy", $ticket)) {
                    echo "Error actualizando salida *Escalado a O&M*" . PHP_EOL;
                    exit();
                }
                */

                if (!$this->mysql_model->actualizar_salida_oym("historico", $ticket)) {
                    echo "Error actualizando salida *Escalado a O&M*" . PHP_EOL;
                    exit();
                }
            }


            //  Actualizar salida *Otros*
            //  ---------------------------------------------------------------
            echo "[" . date("Y-m-d H:i:s") . "] Actualizando salida *Otros*" . PHP_EOL;

            if (!$this->mysql_model->actualizar_salida_otros("historico", date("Y-m-d"))) {
                echo "Error actualizando salida *Otros*" . PHP_EOL;
                exit();
            }

            //print_r($data);

            //  Actualizamos la fecha de última actualización
            $this->mysql_model->actualizar_fecha();

            echo "[" . date("Y-m-d H:i:s") . "] Obteniendo incidencias FIN" . PHP_EOL;
        } else {
            echo "<strong>Este script solo se puede utilizar desde línea de comandos</strong>" . PHP_EOL;
        }
    }


    public function obtener_dia_semana($ano) {

        $fecha_inicio = new DateTime("first monday of January" . $ano);
        //  $fecha_inicio = new DateTime($ano . "-01-01");

        $fecha_fin = new DateTime($ano . "-12-31");
/*
        if (1 != $fecha_inicio->format('N')) {
            $fecha_inicio->modify('next monday');
        }
*/
        while ($fecha_inicio <= $fecha_fin) {
            //  $dates[] = $dateFrom->format('Y-m-d');
            echo $fecha_inicio->format("Y-m-d") . PHP_EOL;
            $fecha_inicio->modify('+1 week');
        }
/*
        $endDate = strtotime($endDate);
        for($i = strtotime('Monday', strtotime($startDate)); $i <= $endDate; $i = strtotime('+1 week', $i))
            echo date('l Y-m-d', $i);
*/
    }


    //  Genera todos los días de un año 
    public function calendario($ano) {

        $dias_semana = [
            "Monday"    => "Lunes",
            "Tuesday"   => "Martes",
            "Wednesday" => "Miércoles",
            "Thursday"  => "Jueves",
            "Friday"    => "Viernes",
            "Saturday"  => "Sábado",
            "Sunday"    => "Domingo"
        ];

        $meses = [
            "January"   => "Enero",
            "February"  => "Febrero",
            "March"     => "Marzo",
            "April"     => "Abril",
            "May"       => "Mayo",
            "June"      => "Junio",
            "July"      => "Julio",
            "August"    => "Agosto",
            "September" => "Septiembre",
            "October"   => "Octubre",
            "November"  => "Noviembre",
            "December"  => "Diciembre"
        ];

        $fecha_inicio = new DateTime($ano . "-01-01");
        $fecha_fin = new DateTime($ano . "-12-31");

        $fecha_datos = [];

        while ($fecha_inicio <= $fecha_fin) {

            $fecha_datos = [
                "fecha"             => $fecha_inicio->format("Y-m-d"),
                "nombre_dia"        => $dias_semana[$fecha_inicio->format("l")],
                "numero_dia_semana" => $fecha_inicio->format("N"),         
                "ano"               => $fecha_inicio->format("Y"),
                "nombre_mes"        => $meses[$fecha_inicio->format("F")],
                "numero_mes"        => $fecha_inicio->format("n"),
            ];

            if (!$insertar_fecha = $this->mysql_model->insertar_fecha($fecha_datos)) {
                echo "Error SQL insertando fecha en calendario" . PHP_EOL;
                exit();
            }

            $fecha_inicio->modify("+1 day");
        }

    }

    public function calcular_umbrales($ano_inicio, $ano_fin) {

        $dias_semana = [
            1 => "Lunes",
            2 => "Martes",
            3 => "Miércoles",
            4 => "Jueves",
            5 => "Viernes",
            6 => "Sábado",
            7 => "Domingo"
        ];

        $dias_totales = [];
        $umbrales = [];
        foreach ($dias_semana as $numero_dia => $dia) {
            
            //  Según los días de la semana
            $fechas = $this->mysql_model->obtener_numero_fechas($dia, $ano_inicio, $ano_fin);
            $dias_totales[$dia] = $fechas[0]["dias"];

            //  var_dump($dias_totales);

            //  Buscamos las incidencias por servicio, hora y tipo de cliente
            $incidencias = $this->mysql_model->obtener_incidencias_servicio_umbrales($dia, $ano_inicio, $ano_fin);
            foreach ($incidencias as $incidencia) {
                $umbrales[] = [
                    "nombre_dia"            => $dia,
                    "numero_dia"            => $numero_dia,
                    "hora"                  => $incidencia["hora"],
                    "servicio_afectado"     => $incidencia["servicio_afectado"],
                    "tipo_cliente"          => $incidencia["tipo_cliente"],
                    "incidencias_promedio"  => round($incidencia["total"] / $dias_totales[$dia], 2)
                ];
            }
        
        }

        //  var_dump($umbrales);

        //  Insertamos los umbrales en la base de datos
        foreach ($umbrales as $umbral) {
            
            if (!$insertar_umbral = $this->mysql_model->insertar_umbral($umbral)) {
                echo "Error SQL insertando umbral" . PHP_EOL;
                exit();
            }
        }

    }

    public function crear_tabla($fecha) {

        $nombre_tabla = "incidencias_tmp_" . str_replace("-", "", $fecha);

        if (!$existe_tabla = $this->mysql_model->existe_tabla($nombre_tabla)) {

            if (!$crear_tabla = $this->mysql_model->crear_tabla_incidencias_dia($fecha)) {
                echo "Error SQL al crear tabla";
                exit();
            }

            echo "Creada la tabla *{$nombre_tabla}*" . PHP_EOL;
            
        } else {
            echo "La tabla ya existe" . PHP_EOL;
        }

    }

}
