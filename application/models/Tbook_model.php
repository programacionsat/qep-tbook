<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tbook_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    //  Busca las incidencias del día actual
    public function obtener_incidencias_hoy() {

        $sql = "

            SELECT
                r.request_id                                                            AS id_ticket,
                cad.external_id                                                         AS id_externo,
                to_char(r.request_date, 'yyyy-mm-dd hh24:mi:ss')                        AS fecha_creacion,
                substr(cad.service_type, instr(cad.service_type, '-') + 2)              AS servicio_afectado,
                func.functionality_name                                                 AS funcionalidad,
                syn.syntom_name                                                         AS sintoma,
                cad.nodo                                                                AS nodo,
                cad.correlated_ntt                                                      AS ntt,
                r.user_                                                                 AS usuario_creador,
                cad.customer_name                                                       AS nombre_cliente,
                cad.customer_type                                                       AS tipo_cliente,
                rtype.rtype_name                                                        AS tipo_peticion
            FROM
                     tbook.request r
                INNER JOIN tbook.ctt_additional_data        cad ON r.request_id = cad.request_id
                LEFT JOIN tbook.master_functionality       func ON cad.functionality_id = func.functionality_id
                LEFT JOIN tbook.master_syntom              syn ON cad.syntom_id = syn.syntom_id
                INNER JOIN tbook.master_ctt_situation       sit ON r.situation_id = sit.situation_id
                INNER JOIN tbook.master_ctt_request_type    rtype ON r.rtype_id = rtype.rtype_id
            WHERE
                    r.request_date >= trunc(sysdate)
                AND r.request_date < trunc(sysdate + 1)
                AND r.itype_id = 9 -- ticket de cliente
                AND r.situation_id != 52 -- ticket anulado

        ";

        $query = $this->db->query($sql);

        return $query->result_array();

    }

    //  Obtiene las incidencias de un día determinado
    public function obtener_incidencias_dia($fecha) {

        $sql = "

            SELECT
                r.request_id                                                            AS id_ticket,
                cad.external_id                                                         AS id_externo,
                to_char(r.request_date, 'yyyy-mm-dd hh24:mi:ss')                        AS fecha_creacion,    
                substr(cad.service_type, instr(cad.service_type, '-') + 2)              AS servicio_afectado,
                func.functionality_name                                                 AS funcionalidad,
                syn.syntom_name                                                         AS sintoma,
                cad.nodo                                                                AS nodo,    
                CASE
                    WHEN cad.correlate_status = 0 THEN cad.correlated_ntt
                    ELSE null
                END                                                                     AS ntt,
                r.user_                                                                 AS usuario_creador,
                cad.customer_name                                                       AS nombre_cliente,
                cad.customer_type                                                       AS tipo_cliente
            FROM
                     tbook.request r
                INNER JOIN tbook.ctt_additional_data     cad ON r.request_id = cad.request_id
                LEFT JOIN tbook.master_functionality    func ON cad.functionality_id = func.functionality_id
                LEFT JOIN tbook.master_syntom           syn ON cad.syntom_id = syn.syntom_id
                INNER JOIN tbook.master_ctt_situation    sit ON r.situation_id = sit.situation_id
            WHERE
                    r.request_date >= trunc(sysdate)
                AND r.request_date < trunc(sysdate + 1)
                AND r.itype_id = 9 -- ticket de cliente
                AND r.situation_id != 52 -- ticket anulado

        ";

        $query = $this->db->query($sql);

        return $query->result_array();

    }

    //  Obtiene las indicencias entre un rango de fechas
    //  [!] La fecha de fin no se incluye en la búsqueda
    public function obtener_incidencias($desde, $hasta) {

        $sql = "

            SELECT
                r.request_id                                                            AS id_ticket,
                cad.external_id                                                         AS id_externo,
                to_char(r.request_date, 'yyyy-mm-dd hh24:mi:ss')                        AS fecha_creacion,    
                substr(cad.service_type, instr(cad.service_type, '-') + 2)              AS servicio_afectado,
                func.functionality_name                                                 AS funcionalidad,
                syn.syntom_name                                                         AS sintoma,
                cad.nodo                                                                AS nodo,    
                CASE
                    WHEN cad.correlate_status = 0 THEN cad.correlated_ntt
                    ELSE null
                END                                                                     AS ntt,
                r.user_                                                                 AS usuario_creador,
                cad.customer_name                                                       AS nombre_cliente,
                cad.customer_type                                                       AS tipo_cliente,
                rtype.rtype_name                                                        AS tipo_peticion
            FROM
                     tbook.request r
                INNER JOIN tbook.ctt_additional_data     cad ON r.request_id = cad.request_id
                LEFT JOIN tbook.master_functionality    func ON cad.functionality_id = func.functionality_id
                LEFT JOIN tbook.master_syntom           syn ON cad.syntom_id = syn.syntom_id
                INNER JOIN tbook.master_ctt_situation    sit ON r.situation_id = sit.situation_id
                INNER JOIN tbook.master_ctt_request_type    rtype ON r.rtype_id = rtype.rtype_id
            WHERE
                    r.request_date >= TO_DATE('{$desde}', 'yyyy-mm-dd')
                AND r.request_date < TO_DATE('{$hasta}', 'yyyy-mm-dd')
                AND r.itype_id = 9 -- ticket de cliente
                AND r.situation_id != 52 -- ticket anulado

        ";

        $query = $this->db->query($sql);

        return $query->result_array();

    }

    //  Devuelve el identificador del ticket en Tbook para
    //  cuando le pasamos el identificador externo (PEGA, Euskalte, Telecable...)
    public function get_id_tbook($id_ticket) {

        $sql = "

            SELECT t.request_id as id_ticket
            FROM tbook.request t
            INNER JOIN tbook.ctt_additional_data cad 
               ON t.request_id = cad.request_id
            WHERE cad.external_id = '{$id_ticket}'

        ";

        $query = $this->db->query($sql);

        return $query->result_array();
    }


    public function obtener_salida_visita($id_ticket) {

        $sql = "

            SELECT t.task_id
            FROM tbook.task t
            WHERE
                  t.tm_id = 1349 -- CONCERTACION CITA CLIENTE
              AND t.request_id = {$id_ticket}

        ";

        $query = $this->db->query($sql);

        return $query->result_array();
    }


    public function obtener_salida_oym($id_ticket) {

        $sql = "

            SELECT
                t.task_id as id_tarea
            FROM
                     tbook.task t
                INNER JOIN tbook.request r ON t.request_id = r.request_id
                INNER JOIN tbook.hist_task_status s on t.task_id = s.task_id
            WHERE
                    t.request_id = {$id_ticket}
                AND t.tm_id = 1348 -- DIAGNOSTICO REMOTO
                AND s.ts_id = 151 -- EN COLA
                -- AND t.idusergroups = 103 -- R_CAC_SAT (usuario creador)
                AND s.idusergroups = 624 -- R_OpRed_N1_Gestion
        ";

        $query = $this->db->query($sql);

        return $query->result_array();

    }














    public function get_tipo_ticket($id_ticket) {

        $sql = "

            SELECT itype_name as tipo_ticket
            FROM tbook.request t
            INNER JOIN tbook.master_incidence i
               ON t.itype_id = i.itype_id
            WHERE t.request_id = '{$id_ticket}'

        ";

        $query = $this->db->query($sql);

        return $query->result_array();

    }

    public function get_info_ticket($id_ticket, $es_red = false) {

        if ($es_red) {

            $sql = "

                SELECT
                    t.request_id                                                   AS id_ticket,
                    t.source_name                                                  AS origen,
                    t.situation_name                                               AS situacion_ticket,
                    t.rtype_name                                                   AS tipo_peticion,
                    t.rtype_name                                                   AS tipo_ticket,
                    t.usergroupcreator                                             AS grupo_creador,
                    t.user_                                                        AS usuario_creador,
                    to_char(t.request_date, 'dd/mm/yyyy hh24:mi:ss')               AS fecha_creacion,
                    to_char(t.request_solved_date, 'dd/mm/yyyy hh24:mi:ss')        AS fhsolucion,
                    to_char(t.request_update_date, 'dd/mm/yyyy hh24:mi:ss')        AS fecha_actualizacion,
                    to_char(t.sa_discounts_date, 'dd/mm/yyyy hh24:mi:ss')          AS fecha_limite,
                    t.description                                                  AS descripcion
                FROM
                    tbook.v_request_all t
                WHERE t.request_id = '{$id_ticket}'

            ";

        } else {

            $sql = "

                SELECT cad.customer_id as id_cliente,
                       cad.customer_name as nombre_cliente,
                       request.request_id as id_ticket,
                       -- request.user as usuario_creador,
                       cad.customer_type as tipo_cliente,
                       cad.description as descripcion,
                       cad.customer_nif as nif_cliente,
                       cad.customer_phone as telefono_cliente,
                       cad.case_id as id_caso,
                       ctt_services_affected.service_level as importancia,
                       mi.impact_name as impacto, -- estado principal
                       master_ctt_situation.situation_name as situacion_ticket, -- estado
                       to_char(request.request_date,  'dd/mm/yyyy hh24:mi:ss') as fecha_creacion,
                       to_char(request.request_solved_date,  'dd/mm/yyyy hh24:mi:ss') as fhsolucion,
                       to_char(request.request_update_date ,  'dd/mm/yyyy hh24:mi:ss') as fecha_actualizacion,
                       to_char(request.sa_discounts_date ,  'dd/mm/yyyy hh24:mi:ss') as fecha_limite,
                       master_section.ctt_section as tramo,
                       master_cause.cause as causa,
                       cad.customer_addr as direccion_cliente,
                       cad.municipio as municipio_cliente,
                       cad.provincia as provincia_cliente,
                       cad.ui as idui,
                       cad.nodo as nodofinal,
                       cad.correlated_ntt as nntt,
                       ctt_services_affected.service_type as servicio_afectado,
                       mf.functionality_name as funcionalidad,
                       ms.syntom_name as  sintoma,
                       cad.segmentation as segmentacion,
                       to_char(request.request_end_date,  'dd/mm/yyyy hh24:mi:ss') as fechasituacion,
                       master_section.ctt_section as comentariossituacion, 
                       cad.priority as prioridad,
                       cad.external_id as id_externo,
                       cad.expediente_id as id_expediente,
                       to_char(request.request_end_date,  'dd/mm/yyyy hh24:mi:ss') as fhcierre,
                       ctt_services_affected.service_id as iddeservicio,
                       ctt_services_affected.product_type as producto,
                       cad.customer_business_area as area_negocio,
                       cad.postsales_tecnician as tecnicopostventa,
                       ctt_services_affected.tbrn,
                       ctt_services_affected.tbrl,
                       ctt_services_affected.tnrn,
                       ctt_services_affected.tnrl,
                       ctt_services_affected.escenario as escenario,
                       master_ctt_responsibility.respons_name as responsabilidad,
                       master_ctt_source.source_name as origen,
                       cad.customer_acc as codigo_cuenta_cliente,
                       ctt_services_affected.backup_status as backup_status,    
                       ctt_services_affected.id_opcion_backup as id_respaldo,   
                       cad.phone as phone,
                       cad.television as television,
                       cad.internet as internet,
                       cad.data as data,
                       cad.movil as movil,
                       cad.UCI as UCI,
                       cad.REITERATED_DEGREE as grado_reiteracion,
                   -- request.rtype_id,
                   CASE request.rtype_id  
                    WHEN 21 THEN 'CORRECTIVO'
                    WHEN 60 THEN 'PREVENTIVO'
                    WHEN 54 THEN 'OPERACION'
                    WHEN 53 THEN 'SOPORTE'
                   END AS tipo_peticion
                FROM tbook.request INNER JOIN tbook.ctt_services_affected 
                  ON request.request_id = ctt_services_affected.request_id
                  INNER JOIN tbook.ctt_additional_data cad 
                  ON request.request_id = cad.request_id
                  INNER JOIN tbook.master_impact mi 
                  ON mi.impact_id = cad.impact_id
                  INNER JOIN tbook.master_ctt_situation 
                  ON master_ctt_situation.situation_id = request.situation_id
                  INNER JOIN tbook.master_functionality mf 
                  ON mf.functionality_id = cad.functionality_id
                  INNER JOIN tbook.master_syntom ms 
                  ON ms.syntom_id = cad.syntom_id
                  INNER JOIN tbook.master_ctt_source 
                  ON master_ctt_source.source_id = request.source_id
                  LEFT JOIN tbook.master_ctt_responsibility 
                  ON master_ctt_responsibility.respons_id = request.respons_id
                  LEFT JOIN tbook.master_cause 
                  ON master_cause.cod = cad.cause
                  LEFT JOIN tbook.master_section 
                  ON master_section.cod = cad.section
                 WHERE request.request_id = '{$id_ticket}'


            ";
        }

        $query = $this->db->query($sql);

        return $query->result_array();
    }
/*
        public function insert_entry()
        {
                $this->title    = $_POST['title']; // please read the below note
                $this->content  = $_POST['content'];
                $this->date     = time();

                $this->db->insert('entries', $this);
        }

        public function update_entry()
        {
                $this->title    = $_POST['title'];
                $this->content  = $_POST['content'];
                $this->date     = time();

                $this->db->update('entries', $this, array('id' => $_POST['id']));
        }
*/

    public function get_tareas($id_ticket) {
        
        $query = $this->db->query("

            SELECT /*t.task_id as id_tarea,*/
                   to_char(t.task_date, 'dd/mm/yyyy hh24:mi') as fecha_creacion,
                   /*t.task_code as codigo,*/
                   t.tm_name as descripcion_tarea,
                   t.ts_name as estado,
                   /*t.result_name as resultado,*/
                   t.assigned_usergroupsname as grupo_asignado,
                   to_char(t.actual_status_date, 'dd/mm/yyyy hh24:mi') as fecha_estado/*,
                   to_char(t.time_to_queued, 'dd/mm/yyyy hh24:mi') as fecha_espera,
                   t.wait_name,
                   to_char(t.limit_date, 'dd/mm/yyyy hh24:mi') as fecha_limite,        
                   t.priority as prioridad,
                   t.user_ as usuario,
                   t.task_obs as informacion_relevante*/
              FROM tbook.v_task t
             WHERE t.request_id = '{$id_ticket}'
             ORDER BY t.task_date

        ");

        return $query->result_array();
    }

    public function get_historico_tareas($id_ticket) {

        $query = $this->db->query("

            SELECT TASK_ID as id_tarea, 
                   TS_NAME as estado, 
                   USERGROUPSNAME as grupo, 
                   to_char(TS_DATE, 'dd/mm/yyyy hh24:mi') as fecha_estado, 
                   WAIT as espera, 
                   USER_ as usuario
              FROM TBOOK.V_TASK_STATUS_HISTORICAL
             WHERE TASK_ID IN
                   (
                           SELECT TASK_ID 
                           FROM TBOOK.V_TASK 
                           WHERE REQUEST_ID = '{$id_ticket}'
                   )
             ORDER BY TASK_ID

        ");

        return $query->result_array();

    }

    public function get_notas_tareas($id_ticket) {

        $query = $this->db->query("

            SELECT to_char(tn.RNOTE_DATE, 'dd/mm/yyyy hh24:mi:ss') as fecha_anotacion,
                   tn.RNOTE_EVENT      as evento_anotacion,
                   tn.TASK_ID          as id_tarea,
                   tn.TM_NAME          as descripcion_tarea,
                   tn.LOGIN            as login_anotacion,
                   tn.USERGROUPSNAME   as grupo_anotacion,
                   tn.SIGNIFICANT_NOTE as anotacion_importante, -- indica icono estrellita
                   tn.OPERATOR_NOTE    as anotacion_operador, -- indica icono persona,
                   tn.RNOTE_TEXT       as anotacion,
                   tn.RTYPE_NAME       as tipo_peticion
              FROM tbook.v_request_notes tn
             WHERE tn.request_id = '{$id_ticket}'
             ORDER BY tn.RNOTE_ID DESC


        ");

        return $query->result_array();
        
    }

    public function get_servicios_afectados($id_ticket) {

        $query = $this->db->query("

            SELECT A.REQUEST_ID            as id_ticket,
                   A.NODE                  as nodo,
                   A.SERVICE_TYPE          as tipo,
                   A.SERVICE_ID            as servicio,
                   A.SERVICE_STATUS        as facturador,
                   A.ACTIVE_DATE           as fecha_inicio_incidencia,
                   COV.COVER               as cobertura,
                   F.FUNCTIONALITY_NAME    as funcionalidad,
                   S.SYNTOM_NAME           as sintoma,
                   B.IMPACT_NAME           as estado_principal,
                   C.STATUS_NAME           as estado_backup,
                   A.ID_OPCION_BACKUP      as backup,
                   A.SERVICE_LEVEL         as nivel,
                   A.SERVICE_DISPONIBILITY as disponibilidad,
                   A.REITERATE             as reiterada,
                   A.REITERATE_DEGREE      as grado_reiteracion
              FROM tbook.CTT_SERVICES_AFFECTED A
              JOIN tbook.REQUEST D
                ON A.REQUEST_ID = D.REQUEST_ID
              JOIN tbook.CTT_ADDITIONAL_DATA DATA
                ON A.REQUEST_ID = DATA.REQUEST_ID
              LEFT JOIN tbook.MASTER_CTT_SITUATION SIT
                ON D.SITUATION_ID = SIT.SITUATION_ID
              LEFT JOIN tbook.V_SAS_LASTCHANGE SAS
                ON SAS.REQUEST_ID = A.REQUEST_ID
               AND SAS.SERVICE_ID = A.SERVICE_ID
              LEFT JOIN tbook.MASTER_IMPACT B
                ON A.IMPACT_ID = B.IMPACT_ID
              LEFT JOIN tbook.MASTER_BACKUP_STATUS C
                ON A.BACKUP_STATUS = C.STATUS_ID
              LEFT JOIN tbook.MASTER_FUNCTIONALITY F
                ON A.FUNCTIONALITY_ID = F.FUNCTIONALITY_ID
              LEFT JOIN tbook.MASTER_SYNTOM S
                ON A.SYNTOM_ID = S.SYNTOM_ID
              LEFT JOIN tbook.MASTER_SERVICE_TYPES T
                ON A.STYPE_ID = T.STYPE_ID
              LEFT JOIN tbook.MASTER_COVER COV
                ON A.COVER = COV.COD
              LEFT JOIN tbook.MASTER_CTT_SOURCE MASTER_SOURCE
                ON tbook.MASTER_SOURCE.SOURCE_ID = D.SOURCE_ID
              LEFT JOIN tbook.MASTER_CTT_REQUEST_TYPE MASTER_RTYPE
                ON MASTER_RTYPE.RTYPE_ID = D.RTYPE_ID
             WHERE A.request_id = '{$id_ticket}'

        ");

        return $query->result_array();
    }

    //  Obtiene los tickets asociados a un ticket de red
    public function get_tickets_correlados($id_ticket_red) {

        $sql = "

            SELECT c.ctt_id as id_ticket,
                   t.customer_name as cliente,
                   t.segmentation as segmentacion
            FROM tbook.hist_correlated_ctt c
            INNER JOIN tbook.v_request_all t
               ON c.ctt_id = t.request_id
            WHERE c.ntt_id = {$id_ticket_red}

        ";

        $query = $this->db->query($sql);

        return $query->result_array();

    }


    //  Obtiene los tickets abiertos por cliente
    public function get_tickets($id_cliente) {
/*
        $sql = "

            SELECT
                t.request_id                                            AS id_ticket,
                to_char(t.request_date, 'dd/mm/YYYY HH24:MI:SS')        AS fecha_creacion,
                s.situation_name                                        AS estado_ticket,
                cad.customer_addr                                       AS direccion,
                cad.municipio                                           AS municipio
            FROM
                     tbook.request t
                INNER JOIN tbook.ctt_additional_data     cad ON t.request_id = cad.request_id
                INNER JOIN tbook.master_ctt_situation    s ON s.situation_id = t.situation_id
            WHERE
                    cad.customer_id = {$id_cliente}
                AND s.situation_name NOT IN (
                    'CERRADO',
                    'ANULADO'
                )

        ";
*/
        
        $sql = "
            SELECT
                t.request_id                                            AS id_ticket,
                to_char(t.request_date, 'dd/mm/YYYY HH24:MI:SS')        AS fecha_creacion,
                t.situation_name                                        AS estado_ticket,
                t.customer_addr                                         AS direccion,
                t.municipio                                             AS municipio,
                t.impact_name                                           AS impacto,
                t.service_id_aff                                        AS login
            FROM
                tbook.v_request_all t
            WHERE
                    t.customer_id = {$id_cliente}
                AND t.situation_name IN (
                    'ABIERTO',
                    'SOLUCIONADO'
                )
        ";
        

        $query = $this->db->query($sql);

        return $query->result_array();

    }
}
?>