<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set("Europe/Madrid");
$fecha_actual = new DateTime();

if (!$es_historico) {
    $titulo_web = "¿qué está pasando en Tbook?";
} else {
    $titulo_web = "¿qué ha pasado en Tbook?";
}
?>
        
        <div class="container mt-3 mb-4">
            <h1><?php echo $titulo_web; ?> <span class="text-muted" style="font-size: 2rem;"><?php echo $fecha_consulta->format("d/m/Y"); ?></span></h1>
            <div class="row mt-3">
                <div class="col">
                    <p>Resumen de todos los tickets de cliente creados en Tbook durante el día de hoy segmentados por hora y servicios.</p>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <p class="filtros-link">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter-left" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M2 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z"/>
                        </svg> Ajustes de visualización</p>

                    <form method="post" action="<?php echo base_url(""); ?>" id="formulario-filtros"  style="display: none;">
                        <div class="row">
                            <div class="col-2">
                                Tipo cliente
                            </div>
                            <div class="col-2">
                                <select name="tipo_cliente" class="custom-select custom-select-sm">
                                    <option value="todo">Todo</option>
<?php 
foreach ($tipos_cliente as $value => $option) {

    if ($this->input->post("tipo_cliente") == $value) {
        echo "
                                    <option selected value=\"{$value}\">{$option}</option>" . PHP_EOL;
    } else {
        echo "
                                    <option value=\"{$value}\">{$option}</option>" . PHP_EOL;
    }
}
?>
                                </select>
                            </div>
                            <input type="hidden" name="fecha" value="<?php echo $fecha_consulta->format('Y-m-d'); ?>">
                            <div class="col-2">
                                <input class="btn btn-sm btn-secondary" type="submit" name="filtrar" value="Filtrar">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
<?php 
if ($this->input->post("tipo_cliente") == "empresa") {
?>
            <div class="row mt-4">
                <div class="col">
                    <h2 class="text-center tipo-cliente">Clientes empresa</h2>
                </div>
            </div>
<?php 
}
?>
            <div class="row mt-4">
                <div class="col">
                    <h3>Servicios</h3>

<?php 
if (count($servicios) != 0) {
?>
                    
                    <table class="tabla-incidencias">
                        <thead>
                            <tr>
                                <th></th>
<?php
$hora_actual = $fecha_actual->format("G");
if ($es_historico) {
    $hora_inicio = 10;
    $hora_fin = 23;
} else {
    if ($hora_actual >= 7) {
        $hora_inicio = $hora_actual - 7;
    } else {
        $hora_inicio = 0;
    }
    $hora_fin = $hora_actual;
}

for ($h = $hora_inicio; $h <= $hora_fin; $h++) {
    echo "
                                <th class=\"celda-hora\">$h:00</th>" . PHP_EOL;
}
?>
                                <th class="celda-incidencias bg-dark text-white">Total</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
foreach ($listado_servicios_mostrar_web as $servicio_afectado => $servicio_afectado_web) {

    //  Versión con enlace al servicio:
    /*
    echo "
                            <tr>
                                <td class=\"celda-incidencias celda-cabecera bg-dark text-white\"><a class=\"enlace-servicio\" href=\"" . base_url("index.php/incidencias/servicios/") . "{$servicio_afectado}\">{$servicio_afectado_web}</a></td>";
    */

    //  Versión sin enlace en el nombre del servicio:
    echo "
                            <tr>
                                <td class=\"celda-incidencias celda-cabecera bg-dark text-white\">{$servicio_afectado_web}</td>";
    for ($h = $hora_inicio; $h <= $hora_fin; $h++) {

        if (array_key_exists($servicio_afectado, $servicios)) {

            if (array_key_exists($h, $servicios[$servicio_afectado])) {

                //  Cálculo del porcentaje de averías de este servicio respecto 
                //  al total de averías en ese tramo horario
                $porcentaje = number_format(round(($servicios[$servicio_afectado][$h] / $incidencias_hora[$h]) * 100, 2), "2", ",", ".");

                echo "
                                <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-servicio\" data-toggle=\"modal\" data-target=\"#modal-listado-incidencias-servicio\" data-servicio=\"{$servicio_afectado}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-hora=\"{$h}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\"><span title=\"{$porcentaje} %\">{$servicios[$servicio_afectado][$h]}</span></a></td>" . PHP_EOL;
            } else {
                echo "
                                <td class=\"celda-incidencias\">0</td>" . PHP_EOL;
            }
        } else {
            echo "
                                <td class=\"celda-incidencias\">0</td>" . PHP_EOL;
        }
    }
    
    if (array_key_exists($servicio_afectado, $servicios)) {

        //  Cálculo del porcentaje de averías de este servicio respecto 
        //  al total de averías 
        $porcentaje_total = number_format(round(($servicios_total[$servicio_afectado] / $incidencias_total) * 100, 2), "2", ",", ".");

        echo "
                                <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-servicio-total\" data-toggle=\"modal\" data-target=\"#modal-listado-incidencias-servicio\" data-servicio=\"{$servicio_afectado}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\"><span title=\"{$porcentaje_total} %\">{$servicios_total[$servicio_afectado]}</span></a></td>
                            </tr>" . PHP_EOL;
    } else {
        echo "
                                <td class=\"celda-incidencias\">0</td>" . PHP_EOL;
    }
}
?>
                        </tbody>
                    </table>

<?php 
} else {
?>
                    <div class="alert alert-warning text-center">
                        No hay datos
                    </div>

<?php
} 
?>
                </div>
            </div> <!-- row Servicios -->

            <!-- ======================================================================= -->
            <!--                                 SALIDAS                                 -->
            <!-- ======================================================================= -->

            <div class="row mt-4">
                <div class="col">
                    <h3>Salidas</h3>
<?php 
if (count($listado_incidencias_salida_hora) != 0) {
?>
                    <table class="tabla-incidencias">
                        <thead>
                            <tr>
                                <th></th>
<?php
$hora_actual = $fecha_actual->format("G");
if ($es_historico) {
    $hora_inicio = 10;
    $hora_fin = 23;
} else {
    if ($hora_actual >= 7) {
        $hora_inicio = $hora_actual - 7;
    } else {
        $hora_inicio = 0;
    }
    $hora_fin = $hora_actual;
}

for ($h = $hora_inicio; $h <= $hora_fin; $h++) {
    echo "
                                <th class=\"celda-hora\">$h:00</th>" . PHP_EOL;
}
?>
                                <th class="celda-incidencias bg-dark text-white">Total</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
foreach ($listado_salidas as $salida) {

    //  Versión con enlace al servicio:
    /*
    echo "
                            <tr>
                                <td class=\"celda-incidencias celda-cabecera bg-dark text-white\"><a class=\"enlace-servicio\" href=\"" . base_url("index.php/incidencias/servicios/") . "{$servicio_afectado}\">{$servicio_afectado_web}</a></td>";
    */

    //  Versión sin enlace en el nombre del servicio:
    echo "
                            <tr>
                                <td class=\"celda-incidencias celda-cabecera bg-dark text-white\">{$salida["nombre"]}</td>";
    for ($h = $hora_inicio; $h <= $hora_fin; $h++) {

        if (array_key_exists($salida["nombre_corto"], $listado_incidencias_salida_hora)) {

            if (array_key_exists($h, $listado_incidencias_salida_hora[$salida["nombre_corto"]])) {

                //  Cálculo del porcentaje de averías de este servicio respecto 
                //  al total de averías en ese tramo horario
                $porcentaje = number_format(round(($listado_incidencias_salida_hora[$salida["nombre_corto"]][$h] / $incidencias_hora[$h]) * 100, 2), "2", ",", ".");

                echo "
                                <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-salida\" data-toggle=\"modal\" data-target=\"#modal-listado-salidas\" data-salida=\"{$salida["nombre_corto"]}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-hora=\"{$h}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\"><span title=\"{$porcentaje} %\">{$listado_incidencias_salida_hora[$salida["nombre_corto"]][$h]}</span></a></td>" . PHP_EOL;
            } else {
                echo "
                                <td class=\"celda-incidencias\">0</td>" . PHP_EOL;
            }
        } else {
            echo "
                                <td class=\"celda-incidencias\">0</td>" . PHP_EOL;
        }
    }
    
    if (array_key_exists($salida["nombre_corto"], $listado_incidencias_salida_hora)) {

        //  Cálculo del porcentaje de averías de este salida respecto 
        //  al total de averías 
        $porcentaje_total = number_format(round(($listado_incidencias_salida_total[$salida["nombre_corto"]] / $incidencias_total) * 100, 2), "2", ",", ".");

        echo "
                                <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-salida-total\" data-toggle=\"modal\" data-target=\"#modal-listado-salidas\" data-salida=\"{$salida["nombre_corto"]}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\"><span title=\"{$porcentaje_total} %\">{$listado_incidencias_salida_total[$salida["nombre_corto"]]}</span></a></td>
                            </tr>" . PHP_EOL;
    } else {
        echo "
                                <td class=\"celda-incidencias\">0</td>" . PHP_EOL;
    }
}
?>
                        </tbody>
                    </table>

<?php
} else {
?>
                    <div class="alert alert-warning text-center">
                        No hay datos
                    </div>
<?php 
}
?>
                </div>
            </div> <!-- row Salidas -->


            <!-- ======================================================================= -->
            <!--                                 ZONAS                                   -->
            <!-- ======================================================================= -->

            <div class="row mt-4">
                <div class="col">
                    <h3>Zonas</h3>

<?php 
if (count($listado_incidencias_zona_hora) != 0) {
?>

                    <table class="tabla-incidencias">
                        <thead>
                            <tr>
                                <th></th>
<?php
$hora_actual = $fecha_actual->format("G");
if ($es_historico) {
    $hora_inicio = 10;
    $hora_fin = 23;
} else {
    if ($hora_actual >= 7) {
        $hora_inicio = $hora_actual - 7;
    } else {
        $hora_inicio = 0;
    }
    $hora_fin = $hora_actual;
}

for ($h = $hora_inicio; $h <= $hora_fin; $h++) {
    echo "
                                <th class=\"celda-hora\">$h:00</th>" . PHP_EOL;
}
?>
                                <th class="celda-incidencias bg-dark text-white">Total</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
foreach ($listado_zonas as $zona) {

    echo "
                            <tr>
                                <td class=\"celda-incidencias celda-cabecera bg-dark text-white\"><a class=\"enlace-servicio\" href=\"" . base_url("index.php/incidencias/servicios/") . "{$zona["nombre_web"]}\">{$zona["nombre_web"]}</a></td>";

    for ($h = $hora_inicio; $h <= $hora_fin; $h++) {

        if (array_key_exists($zona["nombre"], $listado_incidencias_zona_hora)) {
        
            if (array_key_exists($h, $listado_incidencias_zona_hora[$zona["nombre"]])) {

                //  Cálculo del porcentaje de averías de este servicio respecto 
                //  al total de averías en ese tramo horario
                $porcentaje_zona = number_format(round(($listado_incidencias_zona_hora[$zona["nombre"]][$h] / $incidencias_hora[$h]) * 100, 2), "2", ",", ".");

                echo "
                                    <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-zona\" data-toggle=\"modal\" data-target=\"#modal-listado-zonas\" data-zona=\"{$zona["nombre"]}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-hora=\"{$h}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\"><span title=\"{$porcentaje_zona} %\">{$listado_incidencias_zona_hora[$zona["nombre"]][$h]}</span></a></td>" . PHP_EOL;
            } else {
                echo "
                                    <td class=\"celda-incidencias\">0</td>" . PHP_EOL;

            }
        } else {
            echo "
                                    <td class=\"celda-incidencias\">0</td>" . PHP_EOL;

        }
    }

    if (array_key_exists($zona["nombre"], $listado_incidencias_zona_hora)) {
        //  Cálculo del porcentaje de averías de este servicio respecto 
        //  al total de averías 
        $porcentaje_zona_total = number_format(round(($listado_incidencias_zona_total[$zona["nombre"]] / $incidencias_total) * 100, 2), "2", ",", ".");

        echo "
                                    <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-zona-total\" data-toggle=\"modal\" data-target=\"#modal-listado-zonas\" data-zona=\"{$zona["nombre"]}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\"><span title=\"{$porcentaje_zona_total} %\">{$listado_incidencias_zona_total[$zona["nombre"]]}</span></a></td>" . PHP_EOL;
    } else {
        echo "
                                    <td class=\"celda-incidencias\">0</td>" . PHP_EOL;
    }

    echo "
                                </tr>" . PHP_EOL;
}
?>
                        </tbody>
                    </table>
<?php 
} else {
?>
                    <div class="alert alert-warning text-center">
                        No hay datos
                    </div>

<?php
} 
?>

                </div>
            </div> <!-- row Zonas -->


            <!-- ======================================================================= -->
            <!--                                   NTTs                                  -->
            <!-- ======================================================================= -->

            <div class="row mt-4">
                <div class="col">
                    <h3>NTTs <?php if (count($listado_ntts) != 0) {?><span class="badge badge-secondary"><?php echo count($listado_ntts); ?></span><?php } ?></h3>

<?php 
if (count($listado_ntts) != 0) {
?>                    

                    <table class="tabla-incidencias">
                        <thead>
                            <tr>
                                <th></th>
<?php
$hora_actual = $fecha_actual->format("G");
if ($es_historico) {
    $hora_inicio = 10;
    $hora_fin = 23;
} else {
    if ($hora_actual >= 7) {
        $hora_inicio = $hora_actual - 7;
    } else {
        $hora_inicio = 0;
    }
    $hora_fin = $hora_actual;
}

for ($h = $hora_inicio; $h <= $hora_fin; $h++) {
    echo "
                                <th class=\"celda-hora\">$h:00</th>" . PHP_EOL;
}
?>
                                <th class="celda-incidencias bg-dark text-white">Total</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
foreach ($listado_ntts as $ntt => $datos) {

    $datos_ntt_popover = "<ul>";
    foreach ($info_ntts[$ntt] as $datos_ntt) {


        $datos_ntt_popover .= "
            <li>{$datos_ntt["servicio"]} ({$correspondencia_zonas[$datos_ntt["zona"]]}): {$datos_ntt["tickets_correlados"]}</li>" . PHP_EOL;
    }

    $datos_ntt_popover .= "</ul>";

    echo "
                            <tr>
                                <td class=\"celda-incidencias celda-cabecera bg-dark text-white\"><a tabindex=\"0\" class=\"enlace-servicio\" role=\"button\" data-toggle=\"popover\" data-trigger=\"focus\" data-html=\"true\" data-content=\"{$datos_ntt_popover}\">{$ntt}</a></td>";

    for ($h = $hora_inicio; $h <= $hora_fin; $h++) {
        
        if (array_key_exists($h, $listado_ntts_hora[$ntt])) {

            echo "
                                <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-ntt\" data-toggle=\"modal\" data-target=\"#modal-listado-correlados\" data-ntt=\"{$ntt}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-hora=\"{$h}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\">{$listado_ntts_hora[$ntt][$h]}</a></td>" . PHP_EOL;
        } else {
            echo "
                                <td class=\"celda-incidencias\">0</td>" . PHP_EOL;

        }
    }

    echo "
                                <td class=\"celda-incidencias\"><a class=\"dato-inc dato-inc-ntt-total\" data-toggle=\"modal\" data-target=\"#modal-listado-correlados\" data-ntt=\"{$ntt}\" data-fecha=\"{$fecha_consulta->format('Y-m-d')}\" data-tipo-cliente=\"{$this->input->post("tipo_cliente")}\">{$datos["tickets_correlados"]}</a></td>
                            </tr>";
}
?>
                        </tbody>
                    </table>
<?php 
} else {
?>
                    <div class="alert alert-warning text-center">
                        No hay datos
                    </div>

<?php
} 
?>

                </div>
            </div> <!-- row NTTS -->

            <div class="row mt-4">
                <div class="col">
                    <p class="text-muted text-right pr-2">Última actualización: <?php echo $fecha_actualizacion->format("d/m/Y H:i"); ?></p>
                </div>
            </div>
        
        </div> <!-- container -->

        <button onclick="goTop()" id="boton-top" title="Go to top">
            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-up" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z"/>
            </svg>
        </button> 

        <!-- Modal para las incidencias por servicio -->
        <div class="modal fade" id="modal-listado-incidencias-servicio" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Incidencias</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-listado" id="listado-incidencias-servicio">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para las incidencias correladas a un ticket de red -->
        <div class="modal fade" id="modal-listado-correlados" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Incidencias correladas a ticket de red</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-listado" id="listado-correlados">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para las incidencias por salida -->
        <div class="modal fade" id="modal-listado-salidas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Incidencias por salida</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-listado" id="listado-salidas">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para las incidencias por zona -->
        <div class="modal fade" id="modal-listado-zonas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Incidencias por zona</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body modal-listado" id="listado-zonas">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                    </div>
                </div>
            </div>
        </div>

