<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set("Europe/Madrid");

class Incidencias extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model("tbook_model");
        $this->load->model("mysql_model");
    }

    // Filtro por defecto al cargar la página
    private $filtro_tipo_cliente_defecto = "empresa";
     
    public function dashboard() {

        // Insertar visita del usuario:
        if (!isset($_SERVER["HTTP_CAS_USER"])) {
            $usuario = "H157";
        } else {
            $usuario = strtoupper($_SERVER["HTTP_CAS_USER"]);
        }
        $app = "qep-tbook";
        $this->mysql_model->insertar_visita($usuario, $app);

        $fecha_actual = new DateTime();
        
        if ($this->input->post("fecha") == null) {
            $fecha_consulta = new DateTime();
        } else {
            $fecha_consulta = new DateTime($this->input->post("fecha"));
        }

        //  Sensibilidad (para mostrar desviación respecto umbrales)
        if ($this->input->post() == null) {
            $sensibilidad_min = 10;
            $sensibilidad_max = 20;
        } else {
            $sensibilidad_min = (int) $this->input->post("sensibilidad_minima");
            $sensibilidad_max = (int) $this->input->post("sensibilidad_maxima");
        }

        if ($fecha_consulta->format("Y-m-d") == $fecha_actual->format("Y-m-d")) {
            $es_historico = false;
        } else {
            $es_historico = true;

            //  Cuando estamos consultando una fecha distinta a la actual,
            //  al estar separadas las incidencias en dos tablas 
            //  (incidencias de hoy y el histórico), tenemos que comprobar 
            //  si existe una tabla temporal del día a consultar ya creada
            //  o generarla en este momento

            //  Las tablas temporales siguen la siguiente nomenclatura:
            //  *incidencias_tmp_YYYYMMDD*
            
            $nombre_tabla = "incidencias_tmp_" . str_replace("-", "", $fecha_consulta->format("Y-m-d"));

            if (!$existe_tabla = $this->mysql_model->existe_tabla($nombre_tabla)) {

                if (!$crear_tabla = $this->mysql_model->crear_tabla_incidencias_dia($fecha_consulta->format("Y-m-d"))) {
                    echo "Error SQL al crear tabla";
                    exit();
                }

                //echo "Creada la tabla *{$nombre_tabla}*" . PHP_EOL;
                
            }

        }


        //  Empresa = Gran Cuenta y Mediana
        //  Todo = sin filtros
        $tipos_cliente = [
            "empresa"   => "Empresa",
            "todo"      => "Todo"
        ];

        // El filtro por defecto es "Empresa"
        if ($this->input->post("tipo_cliente") != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
            $tipo_cliente_seleccionado = $tipo_cliente;
        } else {
            //$tipo_cliente = "todo";
            $tipo_cliente = "empresa";
            $tipo_cliente_seleccionado = "empresa";
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
        
        //  Obtener umbrales por servicio y hora
        $umbrales_servicio = [];
        
        $numero_dia_semana = $fecha_consulta->format("N");

        foreach ($listado_servicios_mostrar_web as $servicio => $servicio_web) {

            switch ($servicio) {
                case 'grp_internet':
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_internet_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_internet_hora($numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
                case 'movil':
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_movil_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_movil_hora($numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
                case 'telefonia':
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_telefonia_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_telefonia_hora($numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
                case 'voip':
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_voip_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_voip_hora($numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
                case 'datacenter':
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_datacenter_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_datacenter_hora($numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
                case 'sin':
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_sin_servicio_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_sin_servicio_hora($numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
                case 'otros':
                    $filtro_servicios = "('" . implode("', '", array_keys($listado_servicios_reales_a_mostrar)) . "')";
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_otros_servicios_hora($filtro_servicios, $fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_otros_servicios_hora($filtro_servicios, $numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
                default:
                    $incidencias_servicio[$servicio] = $this->mysql_model->obtener_incidencias_servicio_hora($servicio, $fecha_consulta->format("Y-m-d"), $tipo_cliente);
                    $umbrales_servicio[$servicio] = $this->mysql_model->obtener_umbral_servicio_hora($servicio, $numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
                    break;
            }
            
        }

        //  Umbrales por hora sin tener en cuenta el tipo de servicio afectado
        $umbrales_servicios_total_hora = [];
        $umbrales_servicios_total_hora = $this->mysql_model->obtener_umbrales_servicios_hora($numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));


        $datos_incidencias_servicio = [];
        foreach ($incidencias_servicio as $servicio => $datos) {
            foreach ($datos as $dato) {
                $datos_incidencias_servicio[$servicio][$dato["hora_incidencia"]] = $dato["total_incidencias"];
            }
        }

        //  Estructuramos el array con los umbrales por servicio y hora
        $datos_umbrales_servicio = [];
        foreach ($umbrales_servicio as $servicio => $datos) {
            foreach ($datos as $dato) {
                $datos_umbrales_servicio[$servicio][$dato["hora"]] = $dato["incidencias_promedio"];
            }
        }



        //  Control para las incidencias sin servicio afectado ya que sus umbrales
        //  no están completos. Vamos rellenando con 0 las horas para las que
        //  no haya datos (hasta llegar a la hora actual)
        for ($h = 0; $h <= date("G"); $h++) {
            if (!array_key_exists($h, $datos_umbrales_servicio["sin"])) {
                $datos_umbrales_servicio["sin"][$h] = 0;
            }
        }

        //  Estructuramos el array con los umbrales por todos los servicios y hora
        $datos_umbrales_servicios_total_hora = [];
        foreach ($umbrales_servicios_total_hora as $datos) {
            $datos_umbrales_servicios_total_hora[$datos["hora"]] = $datos["incidencias_promedio"];
        }

/*
        //    DEBUG
echo "<pre>";
var_dump($datos_umbrales_servicios_total_hora);
echo "</pre>";
exit();
*/

        //  Totales por servicio
        $datos_incidencias_servicio_total = [];
        foreach ($datos_incidencias_servicio as $servicio_sql => $datos_servicio) {
            $datos_incidencias_servicio_total[$servicio_sql] = array_sum($datos_servicio);
        }

        //  Totales de umbrales por servicio
        $datos_umbrales_servicio_total = [];
        foreach ($datos_umbrales_servicio as $servicio_sql => $datos_umbrales) {
            $datos_umbrales_servicio_total[$servicio_sql] = array_sum($datos_umbrales);
        }

        //  Totales de umbrales de servicio, para el día
        $datos_umbrales_servicios_total_dia = array_sum($datos_umbrales_servicios_total_hora);

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


        //  -------------------------------------------------------------------
        //
        //                      Incidencias por salidas
        // 
        //  -------------------------------------------------------------------  
        $listado_salidas = $this->mysql_model->obtener_salidas_mostrar();
        $incidencias_salida_hora = $this->mysql_model->obtener_incidencias_salida_hora($fecha_consulta->format("Y-m-d"), $tipo_cliente);

        $listado_incidencias_salida_hora = [];
        
        //  Obtener umbrales por salida y hora
        $umbrales_salida = [];

        foreach ($incidencias_salida_hora as $incidencia_salida_hora) {
            $listado_incidencias_salida_hora[$incidencia_salida_hora["salida"]][$incidencia_salida_hora["hora_incidencia"]] = $incidencia_salida_hora["total_incidencias"];
            $umbrales_salida[$incidencia_salida_hora["salida"]] = $this->mysql_model->obtener_umbral_salida_hora($incidencia_salida_hora["salida"], $numero_dia_semana, $tipo_cliente, $fecha_consulta->format("Y-m-d"));
        }

        //  Estructuramos el array con los umbrales por salida y hora
        $datos_umbrales_salida = [];
        foreach ($umbrales_salida as $salida => $datos) {
            foreach ($datos as $dato) {
                $datos_umbrales_salida[$salida][$dato["hora"]] = $dato["incidencias_promedio"];
            }
        }
/*
        //  Estructuramos el array con los umbrales por todos los salidas y hora
        $datos_umbrales_salidas_total_hora = [];
        foreach ($umbrales_salidas_total_hora as $datos) {
            $datos_umbrales_salidas_total_hora[$datos["hora"]] = $datos["incidencias_promedio"];
        }
*/
        //  Totales de umbrales por salida
        $datos_umbrales_salida_total = [];
        foreach ($datos_umbrales_salida as $salida_sql => $datos_umbrales) {
            $datos_umbrales_salida_total[$salida_sql] = array_sum($datos_umbrales);
        }
/*
        //  Totales de umbrales de salida, para el día
        $datos_umbrales_servicios_total_dia = array_sum($datos_umbrales_servicios_total_hora);
*/

        $listado_incidencias_salida_total = [];
        foreach ($listado_incidencias_salida_hora as $salida => $datos_salida) {
            $listado_incidencias_salida_total[$salida] = array_sum($datos_salida);
        }      

        $fecha_actualizacion = $this->mysql_model->obtener_fecha_actualizacion();
        $fecha_actualizacion = new DateTime($fecha_actualizacion[0]["fecha"]);

        $datos["listado_servicios_mostrar_web"] = $listado_servicios_mostrar_web;
        $datos["incidencias_hora"] = $datos_incidencias_hora;
        $datos["incidencias_total"] = $datos_incidencias_total;
        $datos["servicios"] = $datos_incidencias_servicio;
        $datos["servicios_total"] = $datos_incidencias_servicio_total;
        $datos["sensibilidad_min"] = $sensibilidad_min;
        $datos["sensibilidad_max"] = $sensibilidad_max;
        $datos["umbrales_servicios"] = $datos_umbrales_servicio;
        $datos["umbrales_servicios_total"] = $datos_umbrales_servicio_total;
        $datos["umbrales_servicios_total_hora"] = $datos_umbrales_servicios_total_hora;
        $datos["umbrales_servicios_total_dia"] = $datos_umbrales_servicios_total_dia;
        $datos["umbrales_salidas"] = $datos_umbrales_salida;
        $datos["umbrales_salidas_total"] = $datos_umbrales_salida_total;
        //$datos["umbrales_servicios_total_hora"] = $datos_umbrales_salidas_total_hora;
        //$datos["umbrales_servicios_total_dia"] = $datos_umbrales_servicios_total_dia;
        $datos["listado_zonas"] = $listado_zonas;
        $datos["correspondencia_zonas"] = $zonas_nombre;
        $datos["listado_incidencias_zona_hora"] = $listado_incidencias_zona_hora;
        $datos["listado_incidencias_zona_total"] = $listado_incidencias_zona_total;
        $datos["listado_salidas"] = $listado_salidas;
        $datos["listado_incidencias_salida_hora"] = $listado_incidencias_salida_hora;
        $datos["listado_incidencias_salida_total"] = $listado_incidencias_salida_total;
        $datos["listado_ntts"] = $listado_ntts;
        $datos["listado_ntts_hora"] = $listado_ntts_hora;
        $datos["info_ntts"] = $info_ntts;
        $datos["fecha_consulta"] = $fecha_consulta;
        $datos["fecha_actualizacion"] = $fecha_actualizacion;
        $datos["es_historico"] = $es_historico;
        $datos["tipos_cliente"] = $tipos_cliente;
        $datos["tipo_cliente_seleccionado"] = $tipo_cliente_seleccionado;
        $this->load->view("templates/header");
        // $this->load->view("templates/nav");
        $this->load->view("inicio", $datos);
        $this->load->view("templates/footer");
        
    }

    public function servicio_fecha_hora() { 

        $tipo_cliente = $this->input->post("tipo_cliente");

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
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
            case 'grp_internet':
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_internet_hora($fecha, $hora, $tipo_cliente);
                break;
            case 'movil':
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_movil_hora($fecha, $hora, $tipo_cliente);
                break;
            case 'telefonia':
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_telefonia_hora($fecha, $hora, $tipo_cliente);
                break;
            case 'voip':
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_voip_hora($fecha, $hora, $tipo_cliente);
                break;
            case 'datacenter':
                $incidencias_servicio_hora = $this->mysql_model->obtener_listado_incidencias_datacenter_hora($fecha, $hora, $tipo_cliente);
                break;
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
                        <th>Cliente</th>
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
                        <td>{$incidencia["nombre_cliente"]}</td>
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

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
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
                        <th>Cliente</th>
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
                        <td>{$incidencia["nombre_cliente"]}</td>
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

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
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
                        <th>Cliente</th>
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
                        <td>{$incidencia["nombre_cliente"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }

    public function salidas_fecha_hora() {

        $salida = $this->input->post("salida");
        $fecha = $this->input->post("fecha");
        $hora = $this->input->post("hora");
        $tipo_cliente = $this->input->post("tipo_cliente");

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
        }

        $incidencias_salidas_hora = $this->mysql_model->obtener_listado_incidencias_salidas_hora($salida, $fecha, $hora, $tipo_cliente);       
        
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
                        <th>Cliente</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_salidas_hora as $incidencia) {
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
                        <td>{$incidencia["nombre_cliente"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }

    public function salidas_fecha() {

        $salida = $this->input->post("salida");
        $fecha = $this->input->post("fecha");
        $tipo_cliente = $this->input->post("tipo_cliente");

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
        }

        $incidencias_salidas = $this->mysql_model->obtener_listado_incidencias_salidas($salida, $fecha, $tipo_cliente);
        
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
                        <th>Cliente</th>
                        <th>Tipo cliente</th>
                    </tr>
                </thead>
                <tbody>" . PHP_EOL;

        foreach ($incidencias_salidas as $incidencia) {
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
                        <td>{$incidencia["nombre_cliente"]}</td>
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

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
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
                        <th>Cliente</th>
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
                        <td>{$incidencia["nombre_cliente"]}</td>
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

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
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
                        <th>Cliente</th>
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
                        <td>{$incidencia["nombre_cliente"]}</td>
                        <td>{$incidencia["tipo_cliente"]}</td>
                    </tr>" . PHP_EOL;
        }
        echo "
                </tbody>
            </table>" . PHP_EOL;

    }


    public function servicio_fecha() {

        $tipo_cliente = $this->input->post("tipo_cliente");

        // El filtro por defecto es "Empresa"
        if ($tipo_cliente != null) {
            $tipo_cliente = $this->input->post("tipo_cliente");
        } else {
            $tipo_cliente = $this->filtro_tipo_cliente_defecto;
        }

        $servicios_reales_a_mostrar = $this->mysql_model->obtener_servicios_reales_mostrar();

        $listado_servicios_reales_a_mostrar = [];
        foreach ($servicios_reales_a_mostrar as $servicio) {
            $listado_servicios_reales_a_mostrar[$servicio["nombre"]] = $servicio["nombre_web"];
        }
        
        $servicio = $this->input->post("servicio");
        $fecha = $this->input->post("fecha");

        switch ($servicio) {
            case 'grp_internet':
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_internet($fecha, $tipo_cliente);
                break;
            case 'movil':
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_movil($fecha, $tipo_cliente);
                break;
            case 'telefonia':
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_telefonia($fecha, $tipo_cliente);
                break;
            case 'voip':
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_voip($fecha, $tipo_cliente);
                break;
            case 'datacenter':
                $incidencias_servicio = $this->mysql_model->obtener_listado_incidencias_datacenter($fecha, $tipo_cliente);
                break;
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
                        <th>Cliente</th>
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
                        <td>{$incidencia["nombre_cliente"]}</td>
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
