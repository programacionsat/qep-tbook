<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set("Europe/Madrid");
//$fecha_actual = new DateTime();

?>

        <div class="container mt-3 mb-4">
            <h1 class="font-weight-bold">Manual de usuario</h1>
            <div class="row py-3">
                <div class="col-3" id="sticky-sidebar">
                    <div class="sticky-top">
                        <h3 class="h6">Contenido</h3>
                        <ul>
                            <li><a href="<?php echo base_url(); ?>index.php/docs/manual_usuario#intro">Introducción</a></li>
                            <li><a href="<?php echo base_url(); ?>index.php/docs/manual_usuario#data">Datos</a></li>
                            <li><a href="<?php echo base_url(); ?>index.php/docs/manual_usuario#sections">Secciones</a>
                                <ul>
                                    <li><a href="<?php echo base_url(); ?>index.php/docs/manual_usuario#sections">Servicios</a></li>
                                    <li><a href="<?php echo base_url(); ?>index.php/docs/manual_usuario#sections">Zonas</a></li>
                                    <li><a href="<?php echo base_url(); ?>index.php/docs/manual_usuario#sections">Histórico</a></li>
                                </ul></li>
                        </ul>
                    </div>
                </div>
                <div class="col-9" id="main">
                    <h2 id="intro">Introducción</h2>
                    <p><strong>¿Qué está pasando en TBook?</strong> muestra todos las incidencias se crean en la plataforma de ticketing Tbook y los presenta segmentados por hora, servicios y zonas.</p>

                    <h2 id="data">Datos</h2>
                    <p>Se recogen todos los tickets de cliente creados en Tbook</p>

                    <p>De cada ticket se almacena la siguiente información:</p>

                    <ul>
                        <li>Identificador del ticket</li>
                        <li>Identificador externo</li>
                        <li><strong>Fecha de creación</strong> del ticket</li>
                        <li><strong>Servicio afectado</strong></li>
                        <li><strong>Funcionalidad</strong> afectada</li>
                        <li><strong>Síntoma</strong></li>
                        <li><strong>Nodo</strong></li>
                        <li>Ticket de red asociado (<strong>NTT</strong>)</li>
                        <li><strong>Salida</strong></li>
                        <li><strong>Usuario creador</strong> del ticket</li>
                        <li><strong>Tipo de cliente</strong></li>
                    </ul>

                    <p>Un ejemplo de registro:</p>

                    <table class="table table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID ticket</th>
                                <th>ID externo</th>
                                <th>Fecha creación</th>
                                <th>Servicio</th>
                                <th>Funcionalidad</th>
                                <th>Síntoma</th>
                                <th>Nodo</th>
                                <th>NTT</th>
                                <th>Salida</th>
                                <th>Usuario</th>
                                <th>Tipo cliente</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>49464204</td>
                                <td>R-AVE-0659823</td>
                                <td>01/10/2020 14:56:44</td>
                                <td>TVDixital</td>
                                <td>TELEVISION DIGITAL</td>
                                <td>NO VEO LA TELE NI NAVEGO</td>
                                <td>LU001554</td>
                                <td>49463166</td>
                                <td></td>
                                <td>CC101</td>
                                <td>RESIDENCIAL</td>
                            </tr>
                        </tbody>
                    </table>

                    <p>Aunque una incidencia puede tener más de un servicio afectado, a efectos de <strong>¿qué está pasando en Tbook?</strong> solo se considera el primer servicio cargado.</p>

                    <p>Si el ticket se crea desde un sistema distinto a Tbook, se incluye su propio identificador como identificador externo en Tbook. Por ejemplo, el ticket <code>R-AVE-0659014</code> fue creado desde PEGA, pero al volcarse en Tbook, este genera su identificador (<code>49456821</code>) y almacena el de PEGA como identificador externo.</p>

                    <p>La frecuencia de actualización de los datos es de 5 minutos.</p>

                    <h3 id="sections">Secciones</h3>

                    <h4>Página principal</h4>

                    <ul>
                        <li>Servicios: conteo de incidencias por servicio y hora</li>
                        <li>Zonas: conteo de incidencias por territorio y hora</li>
                        <li>NTTs: conteo de incidencias correladas a un ticket de red por hora</li>
                    </ul>

                    <p>Haciendo clic sobre cualquier dato (siempre que sea distinto de 0), se abrirá una ventana con el detalle de las incidencias de ese tramo horario</p>

                    <p>IMAAAAAAAAAAAAAAAAAAAAAAAAAAAAGEN</p>

                    <h4>Servicios</h4>

                    <h4>Zonas</h4>

                    <p>Muestra las indicencias agrupadas por territorio.</p>

                    <p>Para obtener la zona afectada se mira el nodo al que está conectado el cliente y que tiene asociada la incidencia. Los dos primeros caracteres del nombre del nodo hace referencia a la población en la que se encuentra:</p>

                    <table class="table table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nodo</th>
                                <th>Población</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>AC*</td>
                                <td>A Coruña</td>
                            </tr>
                            <tr>
                                <td>SA*</td>
                                <td>Santiago de Compostela</td>
                            </tr>
                            <tr>
                                <td>FE*</td>
                                <td>Ferrol</td>
                            </tr>
                            <tr>
                                <td>VI*</td>
                                <td>Vigo</td>
                            </tr>
                            <tr>
                                <td>PO*</td>
                                <td>Pontevedra</td>
                            </tr>
                            <tr>
                                <td>OU*</td>
                                <td>Ourense</td>
                            </tr>
                            <tr>
                                <td>LU*</td>
                                <td>Lugo</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Histórico</h4>

                </div>
            </div>
        </div>
