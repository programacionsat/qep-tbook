<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MySQL_model extends CI_Model {

    private $tabla_hoy = "incidencias_hoy";
    private $tabla_historico = "incidencias_historico";
    private $tabla_umbrales_servicios = "umbrales_servicios";

    public function __construct() {
        parent::__construct();
        $this->mysql = $this->load->database("mysql", TRUE);
        $this->control = $this->load->database("control", TRUE);
    }

    //  Registra la visita del usuario a la web
    public function insertar_visita($usuario, $app) {

        $fecha = date("Y-m-d H:i:s");

        $datos_visita = [
            'usuario'   => $usuario,
            'app'       => $app,
            'fecha'     => $fecha
        ];

        $this->control->insert("usuarios_apps_log", $datos_visita);
    }

    //  Elimina las incidencias de la tabla de incidencias de hoy
    public function eliminar_incidencias_hoy() {

        date_default_timezone_set("Europe/Madrid");

        $sql = "

            DELETE 
            FROM $this->tabla_hoy
            -- WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d')  = CURDATE()

        ";

        $query = $this->mysql->query($sql);

        // Devuelve true si se ha vaciado correctamente la tabla
        return $query;

    }

    //  Elimina las incidencias del histórico de un determinado 
    //  rango de fechas
    public function eliminar_incidencias($fecha_desde, $fecha_hasta) {

        date_default_timezone_set("Europe/Madrid");

        $sql = "

            DELETE 
            FROM incidencias_historico
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d')  >= '{$fecha_desde}'
              AND DATE_FORMAT(fecha_creacion, '%Y-%m-%d')  <= '{$fecha_hasta}'

        ";

        $query = $this->mysql->query($sql);

        // Devuelve true si se ha vaciado correctamente la tabla
        return $query;

    }

    public function insertar_incidencia($tabla, $incidencia) {

        switch ($tabla) {
            case 'hoy':
                $tabla = "incidencias_hoy";
                break;
            case 'historico':
                $tabla = "incidencias_historico";
                break;
        }

        $datos = [
            "id_ticket"         => $incidencia["ID_TICKET"],
            "id_externo"        => $incidencia["ID_EXTERNO"],
            "fecha_creacion"    => $incidencia["FECHA_CREACION"],
            "servicio_afectado" => $incidencia["SERVICIO_AFECTADO"],
            "funcionalidad"     => $incidencia["FUNCIONALIDAD"],
            "sintoma"           => $incidencia["SINTOMA"],
            "nodo"              => $incidencia["NODO"],
            "ntt"               => $incidencia["NTT"],
            "usuario_creador"   => $incidencia["USUARIO_CREADOR"],
            "nombre_cliente"    => $incidencia["NOMBRE_CLIENTE"],
            "tipo_cliente"      => $incidencia["TIPO_CLIENTE"],
            "tipo_peticion"     => $incidencia["TIPO_PETICION"]
        ];

        $query = $this->mysql->insert($tabla, $datos);

        return $query;

    }

    public function obtener_servicios_reales_mostrar() {

        $sql = "

            SELECT nombre,
                   nombre_web
            FROM servicios_afectados_master
            WHERE mostrar = 's'
              AND es_servicio = 's'
            ORDER BY orden

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Obtiene los servicios que se muestran en la web
    public function obtener_servicios_mostrar() {

        $sql = "

            SELECT nombre,
                   nombre_web
            FROM servicios_afectados_master
            WHERE mostrar = 's'
            ORDER BY orden

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  $tipo_cliente = Empresa | Todo
    public function obtener_incidencias_sin_servicio_hora($fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM $tabla_incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado IS NULL
              $filtro_tipo_cliente
            GROUP BY HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    public function obtener_incidencias_otros_servicios_hora($filtro_servicios, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado NOT IN {$filtro_servicios}
              $filtro_tipo_cliente
            GROUP BY HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Obtener el conteo de incidencias por servicio
    public function obtener_incidencias_servicio_hora($servicio, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado = '{$servicio}'
              $filtro_tipo_cliente
            GROUP BY HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();
    }

    //  Obtener el conteo total de incidencias por servicio
    public function obtener_incidencias_servicio_hora_total($servicio, $fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   servicio_afectado
            FROM $this->tabla_hoy 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado = '{$servicio}'
              $filtro_tipo_cliente
            GROUP BY servicio_afectado

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();
    }

    public function obtener_incidencias_otros_servicios_hora_total($filtro_servicios, $fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado NOT IN {$filtro_servicios}
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    public function obtener_incidencias_sin_servicio_hora_total($fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado IS NULL
              $filtro_tipo_cliente
            GROUP BY servicio_afectado

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();
        
    }

    public function obtener_incidencias_hora($fecha) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
            GROUP BY HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_servicio_hora($servicio, $fecha, $hora, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND servicio_afectado = '{$servicio}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_sin_servicio_hora($fecha, $hora, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND servicio_afectado IS NULL
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_otros_servicios_hora($filtro_servicios, $fecha, $hora, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND servicio_afectado NOT IN {$filtro_servicios}
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }


    public function obtener_listado_incidencias_salidas_hora($salida, $fecha, $hora, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND salida = '{$salida}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }


    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_salidas($salida, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND salida = '{$salida}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }


    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_servicio($servicio, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado = '{$servicio}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_sin_servicio($fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado IS NULL
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_otros_servicios($filtro_servicios, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado NOT IN {$filtro_servicios}
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve el nombre que se la ha dado al servicio a partir del 
    //  que se le da en Tbook.
    public function obtener_nombre_servicio($servicio_sql) {

        $sql = "

            SELECT nombre_web
            FROM servicios_afectados_master
            WHERE nombre = '{$servicio_sql}'

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }


    public function actualizar_fecha() {

        $fecha_actual = date("Y-m-d H:i:s");

        $sql = "

            UPDATE estadisticas
               SET fecha = '{$fecha_actual}'
            WHERE id = 1

        ";

        $query = $this->mysql->query($sql);

        return $query;

    }

    public function obtener_fecha_actualizacion() {

        $sql = "

            SELECT fecha
            FROM estadisticas
            WHERE id = 1

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve un listado de los NTT de una determinada fecha
    public function obtener_ntts($fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT t.ntt as ntt, 
                   COUNT(t.id_ticket) AS num_tickets_correlados
            FROM $tabla_incidencias t
            WHERE DATE_FORMAT(t.fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND t.ntt IS NOT NULL
              $filtro_tipo_cliente
            GROUP BY t.ntt
            ORDER BY 2 DESC

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();      

    }

    public function obtener_ntts_hora($fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT t.ntt as ntt, 
                   COUNT(t.id_ticket) AS num_tickets_correlados,
                   HOUR(t.fecha_creacion) AS hora_creacion_ticket
            FROM $tabla_incidencias t
            WHERE DATE_FORMAT(t.fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND t.ntt IS NOT NULL
              $filtro_tipo_cliente
            GROUP BY t.ntt,
                     HOUR(t.fecha_creacion)
            ORDER BY 2 desc

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();      

    }

    //  Devuelve información de un ticket de red
    public function obtener_info_ntt($ntt, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT servicio_afectado as servicio,
                   MID(t.nodo, 1, 2) as zona,
                   COUNT(id_ticket) as tickets_correlados
            FROM $tabla_incidencias t
            WHERE DATE_FORMAT(t.fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND t.ntt = {$ntt}
              $filtro_tipo_cliente
              AND t.nodo != '0'
              AND t.nodo IS NOT NULL
            GROUP BY servicio_afectado,
                     MID(t.nodo, 1, 2)
            ORDER BY 3 DESC

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();    

    }


    public function obtener_listado_incidencias_correladas_hora($ntt, $fecha, $hora, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND ntt = {$ntt}
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();      

    }

    public function obtener_listado_incidencias_zonas_hora($zona, $fecha, $hora, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND MID(nodo, 1, 2) = '{$zona}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    public function obtener_listado_incidencias_zonas($zona, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND MID(nodo, 1, 2) = '{$zona}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    public function obtener_listado_incidencias_correladas($ntt, $fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT *
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND ntt = {$ntt}
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();      

    }

    public function obtener_zonas_mostrar() {

        $sql = "

            SELECT *
            FROM zonas_master
            WHERE mostrar = 's'
            ORDER BY orden

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    //  Devuelve el nombre de la zona y su abreviatura
    //  para poder comparar con los nodos, por ejemplo
    public function obtener_correspondencia_zonas() {

        $sql = "

            SELECT nombre,
                   nombre_web 
            FROM zonas_master
        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    public function obtener_salidas_mostrar() {

        $sql = "

            SELECT *
            FROM salidas_master
            WHERE mostrar = 's'
            ORDER BY orden

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    public function obtener_incidencias_zona_hora($fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT MID(nodo, 1, 2) AS zona,
                   COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              $filtro_tipo_cliente
            GROUP BY MID(nodo, 1, 2), 
                     HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }


    public function obtener_incidencias_salida_hora($fecha, $tipo_cliente) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha != date("Y-m-d")) {
            $tabla_incidencias = "incidencias_tmp_" . str_replace("-", "", $fecha);
        } else {
            $tabla_incidencias = $this->tabla_hoy;
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT salida AS salida,
                   COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM $tabla_incidencias 
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              $filtro_tipo_cliente
            GROUP BY salida,
                     HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    //  Inserta una fecha con todos sus datos en la tabla
    //  de calendario.
    public function insertar_fecha($fecha) {

        $datos = [
            "fecha"             => $fecha["fecha"],
            "nombre_dia"        => $fecha["nombre_dia"],
            "numero_dia_semana" => $fecha["numero_dia_semana"],
            "ano"               => $fecha["ano"],
            "nombre_mes"        => $fecha["nombre_mes"],
            "numero_mes"        => $fecha["numero_mes"]
        ];

        $query = $this->mysql->insert('calendario', $datos);

        return $query;

    }

    //  Devuelve el número de apariciones de cierto día en un rango temporal
    //  Por ejemplo, el número de lunes desde 2018 hasta 2020
    public function obtener_numero_fechas($nombre_dia_semana, $ano_inicio, $ano_fin) {

        $sql = "

            SELECT COUNT(fecha) as dias
            FROM calendario c
            WHERE c.ano >= $ano_inicio
              AND c.ano <= $ano_fin
              AND c.nombre_dia = '{$nombre_dia_semana}'

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    public function obtener_incidencias_servicio_umbrales($nombre_dia_semana, $ano_inicio, $ano_fin) {

        $sql = "

            SELECT 
                HOUR(t.fecha_creacion) AS hora,
                t.servicio_afectado AS servicio_afectado,
                t.tipo_cliente AS tipo_cliente,
                COUNT(t.id_ticket) AS total
            FROM incidencias t
            WHERE DATE_FORMAT(t.fecha_creacion, '%Y-%m-%d') IN (
                                                                SELECT fecha
                                                                FROM calendario c
                                                                WHERE c.ano >= $ano_inicio
                                                                  AND c.ano <= $ano_fin
                                                                  AND c.nombre_dia = '{$nombre_dia_semana}'
                                                            )
            GROUP BY HOUR(t.fecha_creacion), 
                     t.servicio_afectado, 
                     t.tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    //  Inserta el valor de un umbral para los servicios
    public function insertar_umbral_servicios($umbral) {

        $datos = [
            "nombre_dia"            => $umbral["nombre_dia"],
            "numero_dia"            => $umbral["numero_dia"],
            "hora"                  => $umbral["hora"],
            "servicio_afectado"     => $umbral["servicio_afectado"],
            "tipo_cliente"          => $umbral["tipo_cliente"],
            "incidencias_promedio"  => $umbral["incidencias_promedio"]
        ];

        $query = $this->mysql->insert('umbrales_servicios', $datos);

        return $query;

    }

    public function eliminar_umbrales_servicios() {

        date_default_timezone_set("Europe/Madrid");

        $sql = "

            TRUNCATE $this->tabla_umbrales_servicios

        ";

        $query = $this->mysql->query($sql);

        // Devuelve true si se ha vaciado correctamente la tabla
        return $query;

    }

    //  Crea una tabla con las incidencias de una determinada fecha
    public function crear_tabla_incidencias_dia($fecha) {

        $fecha_nombre = str_replace("-", "", $fecha);

        $sql = "
            CREATE TABLE incidencias_tmp_{$fecha_nombre} AS
            SELECT *
            FROM incidencias_historico
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
        ";

        $query = $this->mysql->query($sql);

        return $query; 

    }

    public function existe_tabla($nombre_tabla) {

        $sql = "

            SHOW TABLES LIKE '{$nombre_tabla}'

        ";

        $query = $this->mysql->query($sql);

        if ($query->conn_id->affected_rows == 1) {
            return true;
        } else {
            return false;
        }

    }

    public function obtener_umbral_servicio_hora($servicio, $dia, $tipo_cliente, $fecha_consulta) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha_consulta != date("Y-m-d")) {
            $filtro_hora_umbral = "";
        } else {
            $filtro_hora_umbral = "AND hora <= " . date("G");
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT hora, 
                   SUM(incidencias_promedio) AS incidencias_promedio
            FROM $this->tabla_umbrales_servicios
            WHERE numero_dia = {$dia}
              $filtro_hora_umbral
              AND servicio_afectado = '{$servicio}'
              $filtro_tipo_cliente
            GROUP BY hora

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    //  Obtener umbrales de incidencias sin servicio afectado
    //  Dependiendo de la hora del día, el valor será diferente ya que
    //  no tiene sentido que si estamos a las 12, comparemos con los
    //  umbrales del día entero.
    public function obtener_umbral_sin_servicio_hora($dia, $tipo_cliente, $fecha_consulta) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha_consulta != date("Y-m-d")) {
            $filtro_hora_umbral = "";
        } else {
            $filtro_hora_umbral = "AND hora <= " . date("G");
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT hora, 
                   SUM(incidencias_promedio) AS incidencias_promedio
            FROM $this->tabla_umbrales_servicios
            WHERE numero_dia = {$dia}
              $filtro_hora_umbral
              AND servicio_afectado IS NULL
              $filtro_tipo_cliente
            GROUP BY hora

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }


    public function obtener_umbral_otros_servicios_hora($filtro_servicios, $dia, $tipo_cliente, $fecha_consulta) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha_consulta != date("Y-m-d")) {
            $filtro_hora_umbral = "";
        } else {
            $filtro_hora_umbral = "AND hora <= " . date("G");
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT hora, 
                   SUM(incidencias_promedio) AS incidencias_promedio
            FROM $this->tabla_umbrales_servicios
            WHERE numero_dia = {$dia}
              $filtro_hora_umbral
              AND servicio_afectado NOT IN {$filtro_servicios}
              $filtro_tipo_cliente
            GROUP BY hora

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    public function obtener_umbrales_servicios_hora($dia, $tipo_cliente, $fecha_consulta) {

        //  Si la fecha de consulta no es el día actual, entonces
        //  tenemos que hacer la consulta en el histórico
        if ($fecha_consulta != date("Y-m-d")) {
            $filtro_hora_umbral = "";
        } else {
            $filtro_hora_umbral = "AND hora <= " . date("G");
        }

        switch ($tipo_cliente) {
            case 'todo':
                $filtro_tipo_cliente = "";
                break;
            case 'empresa':
                $filtro_tipo_cliente = "AND tipo_cliente IN ('Gran Cuenta', 'Mediana') ";
                break;
        }

        $sql = "

            SELECT hora, 
                   SUM(incidencias_promedio) AS incidencias_promedio
            FROM $this->tabla_umbrales_servicios
            WHERE numero_dia = {$dia}
              $filtro_hora_umbral
              $filtro_tipo_cliente
            GROUP BY hora

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }

    public function actualizar_salida_soporte($tabla, $fecha) {

        switch ($tabla) {
            case 'hoy':
                $tabla = "incidencias_hoy";
                break;
            case 'historico':
                $tabla = "incidencias_historico";
                break;
        }

        $sql = "
            UPDATE $tabla
               SET salida = 'Soporte'
             WHERE tipo_peticion = 'SOPORTE'
        ";


        $query = $this->mysql->query($sql);

        return $query; 

    }


    public function actualizar_salida_correlado_ntt($tabla, $fecha) {

        switch ($tabla) {
            case 'hoy':
                $tabla = "incidencias_hoy";
                break;
            case 'historico':
                $tabla = "incidencias_historico";
                break;
        }

        $sql = "
            UPDATE $tabla
               SET salida = 'Correlado'
             WHERE ntt IS NOT NULL
        ";


        $query = $this->mysql->query($sql);

        return $query; 

    }




    public function actualizar_salida_otros($tabla, $fecha) {

        switch ($tabla) {
            case 'hoy':
                $tabla = "incidencias_hoy";
                break;
            case 'historico':
                $tabla = "incidencias_historico";
                break;
        }

        $sql = "
            UPDATE $tabla
               SET salida = 'Otros'
             WHERE salida IS NULL
        ";


        $query = $this->mysql->query($sql);

        return $query; 

    }


    public function actualizar_salida_visita($tabla, $id_ticket) {

        switch ($tabla) {
            case 'hoy':
                $tabla = "incidencias_hoy";
                break;
            case 'historico':
                $tabla = "incidencias_historico";
                break;
        }

        $sql = "
            UPDATE $tabla
               SET salida = 'Visita'
             WHERE id_ticket = {$id_ticket}
        ";


        $query = $this->mysql->query($sql);

        return $query; 

    }


    public function actualizar_salida_oym($tabla, $id_ticket) {

        switch ($tabla) {
            case 'hoy':
                $tabla = "incidencias_hoy";
                break;
            case 'historico':
                $tabla = "incidencias_historico";
                break;
        }

        $sql = "
            UPDATE $tabla
               SET salida = 'OyM'
             WHERE id_ticket = {$id_ticket}
        ";


        $query = $this->mysql->query($sql);

        return $query; 

    }


    public function obtener_incidencias_hoy() {

        $sql = "
            SELECT id_ticket
            FROM {$this->tabla_hoy}
        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 
    }
}
?>