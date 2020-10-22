            <!-- ======================================================================= -->
            <!--                                 SALIDAS                                 -->
            <!-- ======================================================================= -->

            <div class="row mt-4">
                <div class="col">
                    <h3 class="font-weight-bold">Salidas</h3>
                </div>
            </div> <!-- row Salidas -->


            <!-- ======================================================================= -->
            <!--                                 ZONAS                                   -->
            <!-- ======================================================================= -->

            <div class="row mt-4">
                <div class="col">
                    <h3 class="font-weight-bold">Zonas</h3>

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
                    <h3 class="font-weight-bold">NTTs <?php if (count($listado_ntts) != 0) {?><span class="badge badge-secondary"><?php echo count($listado_ntts); ?></span><?php } ?></h3>

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
