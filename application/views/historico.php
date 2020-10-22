<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set("Europe/Madrid");
//$fecha_actual = new DateTime();
?>
        
        <div class="container mt-3 mb-4">
            <h1 class="font-weight-bold">Datos históricos</h1>
            <div class="row mt-3 mb-3">
                <div class="col">
                    <p>Consulta lo que ha pasado en un día específico</p>

                    <form action="<?php echo base_url(); ?>" method="post">
                        <div class="form-row">
                            <div class="col-2">
                                <input class="form-control" type="date" name="fecha">
                            </div>
                            <div class="col-2">
                                <input class="btn btn-secondary" type="submit" value="Buscar">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <p class="mt-4">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-info-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path>
                    <path d="M8.93 6.588l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588z"></path>
                    <circle cx="8" cy="4.5" r="1"></circle>
                </svg> El registro más antiguo es del 01/10/2020</p>
        </div>

