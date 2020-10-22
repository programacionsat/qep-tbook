<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set("Europe/Madrid");

class Incidencias extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model("tbook_model");
        $this->load->model("mysql_model");
    }

    //  Muestra 
    public function dashboard() {

        $fecha_actual = new DateTime();
        
        if ($this->input->post("fecha") == null) {
            $fecha_consulta = new DateTime();
        } else {
            $fecha_consulta = new DateTime($this->input->post("fecha"));
        }

        if ($fecha_consulta->format("Y-m-d") == $fecha_actual->format("Y-m-d")) {
            $es_historico = false;
        } else {
            $es_historico = true;
        }



        $tipos_cliente = [
            "ggcc"          => "Gran Cuenta",
            "mediana"       => "Mediana",
            "micro"         => "Microempresa",
            "residencial"   => "Residencial",
            "colaborador"   => "Colaborador",
            "ambos"         => "Ambos"
        ];

        if ($this->input->post("tipo_cliente") != null) {
            switch ($this->input->post("tipo_cliente")) {
                case 'ggcc':
                    $tipo_cliente = "GRAN CUENTA";
                    break;
                case 'mediana':
                    $tipo_cliente = "MEDIANA";
                    break;
                case 'micro':
                    $tipo_cliente = "MICROEMPRESA";
                    break;
                case 'residencial':
                    $tipo_cliente = "RESIDENCIAL";
                    break;
                case 'colaborador':
                    $tipo_cliente = "COLABORADOR";
                    break;
                case 'ambos':
                    $tipo_cliente = "AMBOS";
                    break;
                default:
                    $tipo_cliente = "todo";
            }
        } else {
            $tipo_cliente = "todo";
        }

/*
        //    DEBUG
echo "<pre>";
var_dump($this->input->post("fecha"));
var_dump($fecha_consulta->format("Y-m-d"));
var_dump($fecha_actual->format("Y-m-d"));
var_dump($tipo_cliente);
var_dump($es_historico);
echo "</pre>";
*/


        //  -------------------------------------------------------------------
        //
        //                      Incidencias por servicio
        // 
        //  -------------------------------------------------------------------  

        //  Obtenemos un listado de los servicios que se pintarán en la web
        $servicios_reales_a_mostrar = $this->mysql_model->obtener_servicios_reales_mostrar();

        $listado_servicios_reales_a_mostrar = [];
        foreach ($servicios_reales_a_mostrar as $servicio) {
            $listado_servicios_reales_a_mostrar[$servicio["nombre"]] = $servicio["nombre_web"];
        }

        $servicios_a_mostrar = $this->mysql_model->obtener_servicios_mostrar();

        $listado_servicios_mostrar_web = [];
        foreach ($servicios_a_mostrar as $servicio) {
            $listado_servicios_mostrar_web[$servicio["nombre"]] = $servicio["nombre_web"];
        }

        $incidencias_servicio = [];
        foreach ($listado_servicios_mostrar_web as $servicio => $servicio_web) {

            switch ($servicio) {
                case 'sin':
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_sin_servicio_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    break;
                case 'otros':
                    $filtro_servicios = "('" . implode("', '", array_keys($listado_servicios_reales_a_mostrar)) . "')";
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_otros_servicios_hora($filtro_servicios, $fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    break;
                default:
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_servicio_hora($servicio, $fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    break;
            }
            
        }    

        $datos_incidencias_servicio = [];
        foreach ($incidencias_servicio as $servicio => $datos) {
            foreach ($datos as $dato) {
                $datos_incidencias_servicio[$servicio][$dato["hora_incidencia"]] = $dato["total_incidencias"];
            }
        }

        //  Totales por servicio
        $datos_incidencias_servicio_total = [];
        foreach ($datos_incidencias_servicio as $servicio_sql => $datos_servicio) {
            $datos_incidencias_servicio_total[$servicio_sql] = array_sum($datos_servicio);
        }

        //  Cálculo de las incidencias totales por hora para poder calcular 
        //  los porcentajes de incidencias por servicio respecto al total
        $incidencias_hora = $this->mysql_model->obtener_incidencias_hora($fecha_consulta->format("Y-m-d"));
        $datos_incidencias_hora = [];
        $datos_incidencias_total = 0;
        foreach ($incidencias_hora as $datos) {
            $datos_incidencias_hora[$datos["hora_incidencia"]] = $datos["total_incidencias"];
            $datos_incidencias_total += $datos["total_incidencias"];
        }

        //  -------------------------------------------------------------------
        //
        //                      Tickets de red (NTT)
        // 
        //  -------------------------------------------------------------------  
        $tickets_red = $this->mysql_model->obtener_ntts($fecha_consulta->format("Y-m-d"), $tipo_cliente);
        $listado_ntts = [];
        $info_ntts = [];
        foreach ($tickets_red as $ticket) {
            $listado_ntts[$ticket["ntt"]]["tickets_correlados"] = $ticket["num_tickets_correlados"];

            //  Incidencias por servicio y zona para cierto NTT
            $info_ntts[$ticket["ntt"]] = $this->mysql_model->obtener_info_ntt($ticket["ntt"], $fecha_consulta->format("Y-m-d"), $tipo_cliente);
        }

        $tickets_red_hora = $this->mysql_model->obtener_ntts_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
        $listado_ntts_hora = [];
        foreach ($tickets_red_hora as $ticket_hora) {
            $listado_ntts_hora[$ticket_hora["ntt"]][$ticket_hora["hora_creacion_ticket"]] = $ticket_hora["num_tickets_correlados"];
        }

        //  -------------------------------------------------------------------
        //
        //                      Incidencias por zonas (nodos)
        // 
        //  -------------------------------------------------------------------  
        $correspondencia_zonas = $this->mysql_model->obtener_correspondencia_zonas();
        foreach ($correspondencia_zonas as $datos_zona) {
            $zonas_nombre[$datos_zona["nombre"]] = $datos_zona["nombre_web"];
        }

        $listado_zonas = $this->mysql_model->obtener_zonas_mostrar();
        $incidencias_zona_hora = $this->mysql_model->obtener_incidencias_zona_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);

        $listado_incidencias_zona_hora = [];
        foreach ($incidencias_zona_hora as $incidencia_zona_hora) {
            $listado_incidencias_zona_hora[$incidencia_zona_hora["zona"]][$incidencia_zona_hora["hora_incidencia"]] = $incidencia_zona_hora["total_incidencias"];
        }

        $listado_incidencias_zona_total = [];
        foreach ($listado_incidencias_zona_hora as $zona => $datos_zona) {
            $listado_incidencias_zona_total[$zona] = array_sum($datos_zona);
        }
        

        $fecha_actualizacion = $this->mysql_model->obtener_fecha_actualizacion();
        $fecha_actualizacion = new DateTime($fecha_actualizacion[0]["fecha"]);

        $datos["listado_servicios_mostrar_web"] = $listado_servicios_mostrar_web;
        $datos["incidencias_hora"] = $datos_incidencias_hora;
        $datos["incidencias_total"] = $datos_incidencias_total;
        $datos["servicios"] = $datos_incidencias_servicio;
        $datos["servicios_total"] = $datos_incidencias_servicio_total;
        $datos["listado_zonas"] = $listado_zonas;
        $datos["correspondencia_zonas"] = $zonas_nombre;
        $datos["listado_incidencias_zona_hora"] = $listado_incidencias_zona_hora;
        $datos["listado_incidencias_zona_total"] = $listado_incidencias_zona_total;
        $datos["listado_ntts"] = $listado_ntts;
        $datos["listado_ntts_hora"] = $listado_ntts_hora;
        $datos["info_ntts"] = $info_ntts;
        $datos["fecha_consulta"] = $fecha_consulta;
        $datos["fecha_actualizacion"] = $fecha_actualizacion;
        $datos["es_historico"] = $es_historico;
        $datos["tipos_cliente"] = $tipos_cliente;
        $this->load->view("templates/header");
        // $this->load->view("templates/nav");
        $this->load->view("inicio", $datos);
        $this->load->view("templates/footer");
        
    }

    public function servicio_fecha_hora() {

        $tipo_cliente = $this->input->post("tipo_cliente");

        if ($tipo_cliente != null) {
            switch ($tipo_cliente) {
                case 'ggcc':
                    $tipo_cliente = "GRAN CUENTA";
                    break;
                case 'mediana':
                    $tipo_cliente = "MEDIANA";
                    break;
                case 'micro':
                    $tipo_cliente = "MICROEMPRESA";
                    break;
                case 'residencial':
                    $tipo_cliente = "RESIDENCIAL";
                    break;
                case 'colaborador':
                    $tipo_cliente = "COLABORADOR";
                    break;
                case 'ambos':
                    $tipo_cliente = "AMBOS";
                    break;
                default:
                    $tipo_cliente = "todo";
            }
        } else {
            $tipo_cliente = "todo";
        }

        $servicios_reales_a_mostrar = $this->mysql_model->obtener_servicios_reales_mostrar();

        $listado_servicios_reales_a_mostrar = [];
        foreach ($servicios_reales_a_mostrar as $servicio) {
            $listado_servicios_reales_a_mostrar[$servicio["nombre"]] = $servicio["nombre_web"];
        }

        $servicio = $this->input->post("servicio");
        $fecha = $this->input->post("fecha");
        $hora = $this->input->post("hora");

        switch ($servicio) {
            case 'otros':
                $filtro_servicios = "('" . implode("', '", array_keys($listado_servicios_reales_a_mostrar)) . "')";
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_otros_servicios_hora($filtro_servicios, $fecha, $hora, $tipo_cliente);
                break;
            case 'sin':
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_sin_servicio_hora($fecha, $hora, $tipo_cliente);
                break;
            default:
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_servicio_hora($servicio, $fecha, $hora, $tipo_cliente);
                break;
        }

        
        
        echo "
            <table class=\"table table-sm table-striped tabla-listado-incidencias\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th>ID ticket</th>
                        <th>ID externo</th>
                        <th>Servicio</th>
                        <th>Funcionalidad</th>
                        <th>Síntoma</th>
                        <th>Salida</th>
                        <th>Nodo</th>
                        <th>NTT</th>
                        <th>Usuario</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_servicio_hora as $incidencia) {
            echo "
                    <tr>
                        <td>{$incidencia["id_ticket"]}</td>
                        <td>{$incidencia["id_externo"]}</td>
                        <td>{$incidencia["servicio_afectado"]}</td>
                        <td>{$incidencia["funcionalidad"]}</td>
                        <td>{$incidencia["sintoma"]}</td>
                        <td>{$incidencia["salida"]}</td>
                        <td>{$incidencia["nodo"]}</td>
                        <td>{$incidencia["ntt"]}</td>
                        <td>{$incidencia["usuario_creador"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }

    public function correladas_fecha_hora() {

        $ntt = $this->input->post("ntt");
        $fecha = $this->input->post("fecha");
        $hora = $this->input->post("hora");
        $tipo_cliente = $this->input->post("tipo_cliente");

        if ($tipo_cliente != null) {
            switch ($tipo_cliente) {
                case 'ggcc':
                    $tipo_cliente = "GRAN CUENTA";
                    break;
                case 'mediana':
                    $tipo_cliente = "MEDIANA";
                    break;
                case 'micro':
                    $tipo_cliente = "MICROEMPRESA";
                    break;
                case 'residencial':
                    $tipo_cliente = "RESIDENCIAL";
                    break;
                case 'colaborador':
                    $tipo_cliente = "COLABORADOR";
                    break;
                case 'ambos':
                    $tipo_cliente = "AMBOS";
                    break;
                default:
                    $tipo_cliente = "todo";
            }
        } else {
            $tipo_cliente = "todo";
        }

        $incidencias_correladas_hora = $this->mysql_model->obtener_listado_incidencias_correladas_hora($ntt, $fecha, $hora, $tipo_cliente);       
        
        echo "
            <table class=\"table table-sm table-striped tabla-listado-incidencias\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th>ID ticket</th>
                        <th>ID externo</th>
                        <th>Servicio</th>
                        <th>Funcionalidad</th>
                        <th>Síntoma</th>
                        <th>Salida</th>
                        <th>Nodo</th>
                        <th>NTT</th>
                        <th>Usuario</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_correladas_hora as $incidencia) {
            echo "
                    <tr>
                        <td>{$incidencia["id_ticket"]}</td>
                        <td>{$incidencia["id_externo"]}</td>
                        <td>{$incidencia["servicio_afectado"]}</td>
                        <td>{$incidencia["funcionalidad"]}</td>
                        <td>{$incidencia["sintoma"]}</td>
                        <td>{$incidencia["salida"]}</td>
                        <td>{$incidencia["nodo"]}</td>
                        <td>{$incidencia["ntt"]}</td>
                        <td>{$incidencia["usuario_creador"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }

    public function zonas_fecha_hora() {

        $zona = $this->input->post("zona");
        $fecha = $this->input->post("fecha");
        $hora = $this->input->post("hora");
        $tipo_cliente = $this->input->post("tipo_cliente");

        if ($tipo_cliente != null) {
            switch ($tipo_cliente) {
                case 'ggcc':
                    $tipo_cliente = "GRAN CUENTA";
                    break;
                case 'mediana':
                    $tipo_cliente = "MEDIANA";
                    break;
                case 'micro':
                    $tipo_cliente = "MICROEMPRESA";
                    break;
                case 'residencial':
                    $tipo_cliente = "RESIDENCIAL";
                    break;
                case 'colaborador':
                    $tipo_cliente = "COLABORADOR";
                    break;
                case 'ambos':
                    $tipo_cliente = "AMBOS";
                    break;
                default:
                    $tipo_cliente = "todo";
            }
        } else {
            $tipo_cliente = "todo";
        }

        $incidencias_zonas_hora = $this->mysql_model->obtener_listado_incidencias_zonas_hora($zona, $fecha, $hora, $tipo_cliente);       
        
        echo "
            <table class=\"table table-sm table-striped tabla-listado-incidencias\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th>ID ticket</th>
                        <th>ID externo</th>
                        <th>Servicio</th>
                        <th>Funcionalidad</th>
                        <th>Síntoma</th>
                        <th>Salida</th>
                        <th>Nodo</th>
                        <th>NTT</th>
                        <th>Usuario</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_zonas_hora as $incidencia) {
            echo "
                    <tr>
                        <td>{$incidencia["id_ticket"]}</td>
                        <td>{$incidencia["id_externo"]}</td>
                        <td>{$incidencia["servicio_afectado"]}</td>
                        <td>{$incidencia["funcionalidad"]}</td>
                        <td>{$incidencia["sintoma"]}</td>
                        <td>{$incidencia["salida"]}</td>
                        <td>{$incidencia["nodo"]}</td>
                        <td>{$incidencia["ntt"]}</td>
                        <td>{$incidencia["usuario_creador"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }

    public function zonas_fecha() {

        $zona = $this->input->post("zona");
        $fecha = $this->input->post("fecha");
        $tipo_cliente = $this->input->post("tipo_cliente");

        if ($tipo_cliente != null) {
            switch ($tipo_cliente) {
                case 'ggcc':
                    $tipo_cliente = "GRAN CUENTA";
                    break;
                case 'mediana':
                    $tipo_cliente = "MEDIANA";
                    break;
                case 'micro':
                    $tipo_cliente = "MICROEMPRESA";
                    break;
                case 'residencial':
                    $tipo_cliente = "RESIDENCIAL";
                    break;
                case 'colaborador':
                    $tipo_cliente = "COLABORADOR";
                    break;
                case 'ambos':
                    $tipo_cliente = "AMBOS";
                    break;
                default:
                    $tipo_cliente = "todo";
            }
        } else {
            $tipo_cliente = "todo";
        }

        $incidencias_zonas_hora = $this->mysql_model->obtener_listado_incidencias_zonas($zona, $fecha, $tipo_cliente);
        
        echo "
            <table class=\"table table-sm table-striped tabla-listado-incidencias\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th>ID ticket</th>
                        <th>ID externo</th>
                        <th>Servicio</th>
                        <th>Funcionalidad</th>
                        <th>Síntoma</th>
                        <th>Salida</th>
                        <th>Nodo</th>
                        <th>NTT</th>
                        <th>Usuario</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_zonas_hora as $incidencia) {
            echo "
                    <tr>
                        <td>{$incidencia["id_ticket"]}</td>
                        <td>{$incidencia["id_externo"]}</td>
                        <td>{$incidencia["servicio_afectado"]}</td>
                        <td>{$incidencia["funcionalidad"]}</td>
                        <td>{$incidencia["sintoma"]}</td>
                        <td>{$incidencia["salida"]}</td>
                        <td>{$incidencia["nodo"]}</td>
                        <td>{$incidencia["ntt"]}</td>
                        <td>{$incidencia["usuario_creador"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }

    public function correladas_fecha() {

        $ntt = $this->input->post("ntt");
        $fecha = $this->input->post("fecha");
        $tipo_cliente = $this->input->post("tipo_cliente");

        if ($tipo_cliente != null) {
            switch ($tipo_cliente) {
                case 'ggcc':
                    $tipo_cliente = "GRAN CUENTA";
                    break;
                case 'mediana':
                    $tipo_cliente = "MEDIANA";
                    break;
                case 'micro':
                    $tipo_cliente = "MICROEMPRESA";
                    break;
                case 'residencial':
                    $tipo_cliente = "RESIDENCIAL";
                    break;
                case 'colaborador':
                    $tipo_cliente = "COLABORADOR";
                    break;
                case 'ambos':
                    $tipo_cliente = "AMBOS";
                    break;
                default:
                    $tipo_cliente = "todo";
            }
        } else {
            $tipo_cliente = "todo";
        }

        $incidencias_correladas = $this->mysql_model->obtener_listado_incidencias_correladas($ntt, $fecha, $tipo_cliente);
        
        echo "
            <table class=\"table table-sm table-striped tabla-listado-incidencias\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th>ID ticket</th>
                        <th>ID externo</th>
                        <th>Servicio</th>
                        <th>Funcionalidad</th>
                        <th>Síntoma</th>
                        <th>Salida</th>
                        <th>Nodo</th>
                        <th>NTT</th>
                        <th>Usuario</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_correladas as $incidencia) {
            echo "
                    <tr>
                        <td>{$incidencia["id_ticket"]}</td>
                        <td>{$incidencia["id_externo"]}</td>
                        <td>{$incidencia["servicio_afectado"]}</td>
                        <td>{$incidencia["funcionalidad"]}</td>
                        <td>{$incidencia["sintoma"]}</td>
                        <td>{$incidencia["salida"]}</td>
                        <td>{$incidencia["nodo"]}</td>
                        <td>{$incidencia["ntt"]}</td>
                        <td>{$incidencia["usuario_creador"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }


    public function servicio_fecha() {

        $tipo_cliente = $this->input->post("tipo_cliente");

        if ($tipo_cliente != null) {
            switch ($tipo_cliente) {
                case 'ggcc':
                    $tipo_cliente = "GRAN CUENTA";
                    break;
                case 'mediana':
                    $tipo_cliente = "MEDIANA";
                    break;
                case 'micro':
                    $tipo_cliente = "MICROEMPRESA";
                    break;
                case 'residencial':
                    $tipo_cliente = "RESIDENCIAL";
                    break;
                case 'colaborador':
                    $tipo_cliente = "COLABORADOR";
                    break;
                case 'ambos':
                    $tipo_cliente = "AMBOS";
                    break;
                default:
                    $tipo_cliente = "todo";
            }
        } else {
            $tipo_cliente = "todo";
        }

        $servicios_reales_a_mostrar = $this->mysql_model->obtener_servicios_reales_mostrar();

        $listado_servicios_reales_a_mostrar = [];
        foreach ($servicios_reales_a_mostrar as $servicio) {
            $listado_servicios_reales_a_mostrar[$servicio["nombre"]] = $servicio["nombre_web"];
        }
        
        $servicio = $this->input->post("servicio");
        $fecha = $this->input->post("fecha");

        switch ($servicio) {
            case 'otros':
                $filtro_servicios = "('" . implode("', '", array_keys($listado_servicios_reales_a_mostrar)) . "')";
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_otros_servicios($filtro_servicios, $fecha, $tipo_cliente);
                break;
            case 'sin':
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_sin_servicio($fecha, $tipo_cliente);
                break;
            default:
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_servicio($servicio, $fecha, $tipo_cliente);
                break;
        }

        
        
        echo "
            <table class=\"table table-sm table-striped tabla-listado-incidencias\">
                <thead class=\"thead-dark\">
                    <tr>
                        <th>ID ticket</th>
                        <th>ID externo</th>
                        <th>Servicio</th>
                        <th>Funcionalidad</th>
                        <th>Síntoma</th>
                        <th>Salida</th>
                        <th>Nodo</th>
                        <th>NTT</th>
                        <th>Usuario</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_servicio as $incidencia) {
            echo "
                    <tr>
                        <td>{$incidencia["id_ticket"]}</td>
                        <td>{$incidencia["id_externo"]}</td>
                        <td>{$incidencia["servicio_afectado"]}</td>
                        <td>{$incidencia["funcionalidad"]}</td>
                        <td>{$incidencia["sintoma"]}</td>
                        <td>{$incidencia["salida"]}</td>
                        <td>{$incidencia["nodo"]}</td>
                        <td>{$incidencia["ntt"]}</td>
                        <td>{$incidencia["usuario_creador"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }

    public function historico() {
        
        $this->load->view("templates/header_simple");
        $this->load->view("historico");
        $this->load->view("templates/footer_simple");
    
    }

    //  Información de incidencias de un determinado servicio
    public function servicios($nombre_servicio = null, $fecha = "hoy") {

        //  Obtenemos un listado de los servicios que se pintarán en la web
        $servicios_reales_a_mostrar = $this->mysql_model->obtener_servicios_reales_mostrar();

        $listado_servicios_reales_a_mostrar = [];
        foreach ($servicios_reales_a_mostrar as $servicio) {
            $listado_servicios_reales_a_mostrar[$servicio["nombre"]] = $servicio["nombre_web"];
        }


        if ($nombre_servicio != null) {
            $nombre_servicio_web = $this->mysql_model->obtener_nombre_servicio($nombre_servicio);

            $nombre_servicio_web = $nombre_servicio_web[0]["nombre_web"];

            $data["nombre_servicio"] = $nombre_servicio_web;
            $data["servicio"] = $nombre_servicio;
        }

        $data["servicio_seleccionado"] = $nombre_servicio;
        $data["listado_servicios"] = $listado_servicios_reales_a_mostrar;

        $this->load->view("templates/header");
        $this->load->view("templates/nav");
        $this->load->view("servicios/info", $data);    
        $this->load->view("templates/footer");
        
    }

    public function zonas() {

        echo "Zonas";

    }

}
