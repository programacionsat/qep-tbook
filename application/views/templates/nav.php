<?php
$subpagina = $this->uri->segment(2);
?>
        <div class="menu-agrupacion">
            <div class="container">
                <nav class="nav d-flex justify-content-start">
                  <a class="p-2 <?php if ($subpagina == 'servicios') { echo 'nav-link-active'; } else { echo 'text-muted'; } ?>" href="<?php echo base_url(); ?>index.php/incidencias/servicios">Servicios</a>
                  <a class="p-2 <?php if ($subpagina == 'zonas') { echo 'nav-link-active'; } else { echo 'text-muted'; } ?>" href="<?php echo base_url(); ?>index.php/incidencias/zonas">Zonas</a>
                </nav>
            </div>
        </div>