document.getElementById("agendarCitaForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const formData = new FormData(document.getElementById("agendarCitaForm"));
    const nombre = formData.get("nombre");
    const fechaCita = formData.get("fecha_cita");
    const hora = formData.get("hora");

    // Mostrar pop-up
    alert(`¡Muchas gracias por tu interés y confianza, ${nombre}!\nTe estaremos esperando el día ${fechaCita} a las ${hora} para tu cita.`);

    const respuestaDiv = document.getElementById("respuesta");

    // Creación de link para agregar a Google Calendar
    const googleCalendarUrl = createGoogleCalendarLink(nombre, fechaCita, hora);
    respuestaDiv.innerHTML = `<p><a href="${googleCalendarUrl}" target="_blank">Agregar a Google Calendar</a></p>`;

    // Agregar link para descargar el archivo ICS
    const icsFileUrl = 'ruta_a_tu_archivo_ics/Cita_Veterinaria.ics';
    respuestaDiv.innerHTML += `<p><a href="${icsFileUrl}" download>Descargar archivo .ics</a></p>`;
});

// Función para generar el enlace de Google Calendar
function createGoogleCalendarLink(nombre, fechaCita, hora) {
    const startDate = new Date(`${fechaCita}T${hora}`).toISOString().replace(/-|:|\.\d\d\d/g, "");
    const endDate = new Date(new Date(`${fechaCita}T${hora}`).getTime() + 60 * 60 * 1000).toISOString().replace(/-|:|\.\d\d\d/g, "");
    
    const title = `Cita Veterinaria para ${nombre}`;
    const details = `Cita programada para ${nombre}`;
    
    const googleCalendarUrl = `https://calendar.google.com/calendar/r/eventedit?text=${encodeURIComponent(title)}&dates=${startDate}/${endDate}&details=${encodeURIComponent(details)}`;
    
    return googleCalendarUrl;
}
