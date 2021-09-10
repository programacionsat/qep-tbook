<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set("Europe/Madrid");
//$fecha_actual = new DateTime();

$uri_servicio = $this->uri->segment(3);
?>

        <div class="menu-agrupacion menu-agrupacion-sub">
            <div class="container">
                <nav class="nav d-flex justify-content-start">
<?php 
foreach ($listado_servicios as $nombre => $nombre_web) {
    echo "
                    <a class=\"p-2 ";
    if ($uri_servicio == $nombre) { 
        echo 'nav-sublink-active'; 
    } else { 
        echo 'text-muted'; 
    } 
    echo "\" href=\"" . base_url() . "index.php/incidencias/servicios/{$nombre}\">{$nombre_web}</a>" . PHP_EOL;
}
?>
                </nav>
            </div>
        </div>
        
        <div class="container mt-3 mb-4">
<?php 
if ($servicio_seleccionado != null) {
?>            
            <h1 class="font-weight-bold"><?php echo $nombre_servicio; ?></h1>
            <div class="row mt-3">
                <div class="col">
                    <h2>Salidas</h2>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <h2>NTTs</h2>
                </div>
            </div>
<?php 
} else {
?>
            <div class="row">
                <div class="col">
                    <div class="alert alert-light text-center">
                        Selecciona un servicio para consultar sus incidencias.
                    </div>
                </div>
            </div>
<?php
}
?>
        </div>

