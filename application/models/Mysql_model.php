<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MySQL_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->mysql = $this->load->database("mysql", TRUE);
    }

    public function insert_visita($usuario, $app) {

        $fecha = date("Y-m-d H:i:s");

        $datos_visita = [
            'usuario'   => $usuario,
            'app'       => $app,
            'fecha'     => $fecha
        ];

        $this->mysql->insert("usuarios_apps_log", $datos_visita);
    }

    //  Elimina las incidencias de hoy
    public function eliminar_incidencias_hoy() {

        date_default_timezone_set("Europe/Madrid");

        $fecha_hoy = new DateTime();

        $sql = "

            DELETE 
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d')  = CURDATE()

        ";

        $query = $this->mysql->query($sql);

        // Devuelve true si se ha vaciado correctamente la tabla
        return $query;

    }

    //  Elimina las incidencias de hoy
    public function eliminar_incidencias($fecha_desde, $fecha_hasta) {

        date_default_timezone_set("Europe/Madrid");

        $fecha_hoy = new DateTime();

        $sql = "

            DELETE 
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d')  >= '{$fecha_desde}'
              AND DATE_FORMAT(fecha_creacion, '%Y-%m-%d')  <= '{$fecha_hasta}'

        ";

        $query = $this->mysql->query($sql);

        // Devuelve true si se ha vaciado correctamente la tabla
        return $query;

    }

    public function insertar_incidencia($incidencia) {

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
            "tipo_cliente"      => $incidencia["TIPO_CLIENTE"]
        ];

        $query = $this->mysql->insert('incidencias', $datos);

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

    public function obtener_incidencias_sin_servicio_hora($fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM incidencias
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM incidencias
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM incidencias
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
            FROM incidencias
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

        $sql = "

            SELECT COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
            GROUP BY HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_servicio_hora($servicio, $fecha, $hora, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND servicio_afectado NOT IN {$filtro_servicios}
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }


    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_servicio($servicio, $fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado = '{$servicio}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_sin_servicio($fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND servicio_afectado IS NULL
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    //  Devuelve toda la información sobre una incidencia en base a la fecha y hora
    public function obtener_listado_incidencias_otros_servicios($filtro_servicios, $fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT t.ntt as ntt, 
                   COUNT(t.id_ticket) AS num_tickets_correlados
            FROM incidencias t
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT t.ntt as ntt, 
                   COUNT(t.id_ticket) AS num_tickets_correlados,
                   HOUR(t.fecha_creacion) AS hora_creacion_ticket
            FROM incidencias t
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT servicio_afectado as servicio,
                   MID(t.nodo, 1, 2) as zona,
                   COUNT(id_ticket) as tickets_correlados
            FROM incidencias t
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

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND ntt = {$ntt}
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();      

    }

    public function obtener_listado_incidencias_zonas_hora($zona, $fecha, $hora, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND HOUR(fecha_creacion) = {$hora}
              AND MID(nodo, 1, 2) = '{$zona}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    public function obtener_listado_incidencias_zonas($zona, $fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              AND MID(nodo, 1, 2) = '{$zona}'
              $filtro_tipo_cliente

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array();

    }

    public function obtener_listado_incidencias_correladas($ntt, $fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT *
            FROM incidencias
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

    public function obtener_incidencias_zona_hora($fecha, $tipo_cliente) {

        if ($tipo_cliente == "todo") {
            $filtro_tipo_cliente = "";
        } else {
            $filtro_tipo_cliente = "AND tipo_cliente = '{$tipo_cliente}'";
        }

        $sql = "

            SELECT MID(nodo, 1, 2) AS zona,
                   COUNT(id_ticket) as total_incidencias,
                   HOUR(fecha_creacion) as hora_incidencia
            FROM incidencias
            WHERE DATE_FORMAT(fecha_creacion, '%Y-%m-%d') = '{$fecha}'
              $filtro_tipo_cliente
            GROUP BY MID(nodo, 1, 2), HOUR(fecha_creacion)
            ORDER BY HOUR(fecha_creacion)

        ";

        $query = $this->mysql->query($sql);

        return $query->result_array(); 

    }
}
?>