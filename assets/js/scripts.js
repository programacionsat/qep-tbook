$( document ).ready(function() {
    console.log( "ready!" );

    //  Botón de volver al principio de la página
    //Get the button:
    var boton_top = document.getElementById("boton-top");

    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        boton_top.style.display = "block";
      } else {
        boton_top.style.display = "none";
      }
    }

    

    //  Inicialización de popper
    $(function () {
        $('[data-toggle="popover"]').popover()
    });

    $('.popover-dismiss').popover({
        trigger: 'focus'
    })



    //  Muestras las opciones para filtrar los datos por tipo de cliente
    //  y ajustar los umbrales
    $(".filtros-link").click(function() {
        $("#formulario-filtros").toggle();
        console.log("Hola, formulario");
        /*
        if ($("#ajustes-icono").hasClass('icon-caret-down')) {
            $(".icon-caret-down").addClass('icon-caret-up').removeClass('icon-caret-down');
        } else {
            $(".icon-caret-up").addClass('icon-caret-down').removeClass('icon-caret-up');
        }
        */
    });

});

// When the user clicks on the button, scroll to the top of the document
function goTop() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}
    

$( ".dato-inc-servicio" ).click(function() {
  
    var tipo_cliente = $(this).data('tipo-cliente');
    var servicio = $(this).data('servicio');
    var fecha = $(this).data('fecha');
    var hora = $(this).data('hora');
    var url = "index.php/incidencias/servicio_fecha_hora";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            servicio: servicio,
            fecha: fecha,
            hora: hora,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-incidencias-servicio").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});

$( ".dato-inc-salida" ).click(function() {
  
    var salida = $(this).data('salida');
    var fecha = $(this).data('fecha');
    var hora = $(this).data('hora');
    var tipo_cliente = $(this).data('tipo-cliente');
    var url = "index.php/incidencias/salidas_fecha_hora";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            salida: salida,
            fecha: fecha,
            hora: hora,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-salidas").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});

$( ".dato-inc-zona" ).click(function() {
  
    var zona = $(this).data('zona');
    var fecha = $(this).data('fecha');
    var hora = $(this).data('hora');
    var tipo_cliente = $(this).data('tipo-cliente');
    var url = "index.php/incidencias/zonas_fecha_hora";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            zona: zona,
            fecha: fecha,
            hora: hora,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-zonas").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});

$( ".dato-inc-ntt" ).click(function() {
  
    var ntt = $(this).data('ntt');
    var fecha = $(this).data('fecha');
    var hora = $(this).data('hora');
    var tipo_cliente = $(this).data('tipo-cliente');

    var url = "index.php/incidencias/correladas_fecha_hora";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            ntt: ntt,
            fecha: fecha,
            hora: hora,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-correlados").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});

$( ".dato-inc-salida-total" ).click(function() {

    var salida = $(this).data('salida');
    var fecha = $(this).data('fecha');
    var tipo_cliente = $(this).data('tipo-cliente');
    var url = "index.php/incidencias/salidas_fecha";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            salida: salida,
            fecha: fecha,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-salidas").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});

$( ".dato-inc-zona-total" ).click(function() {

    var zona = $(this).data('zona');
    var fecha = $(this).data('fecha');
    var tipo_cliente = $(this).data('tipo-cliente');
    var url = "index.php/incidencias/zonas_fecha";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            zona: zona,
            fecha: fecha,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-zonas").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});

$( ".dato-inc-ntt-total" ).click(function() {
  
    var ntt = $(this).data('ntt');
    var fecha = $(this).data('fecha');
    var tipo_cliente = $(this).data('tipo-cliente');

    var url = "index.php/incidencias/correladas_fecha";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            ntt: ntt,
            fecha: fecha,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-correlados").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});


$( ".dato-inc-servicio-total" ).click(function() {
  
    var tipo_cliente = $(this).data('tipo-cliente');
    var servicio = $(this).data('servicio');
    var fecha = $(this).data('fecha');
    var url = "index.php/incidencias/servicio_fecha";

    console.log("Controlador: " + url);

    $.post(
        url,
        {
            servicio: servicio,
            fecha: fecha,
            tipo_cliente: tipo_cliente
        },
        function(data, status) {
            $("#listado-incidencias-servicio").html(data);
            console.log(data);
            console.log(status);
            //  alert("Data: " + data + "\nStatus: " + status);
    });

    //console.log("servicio: " + servicio + " - " + "hora: " + hora);
});