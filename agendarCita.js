document.getElementById("agendarCitaForm").addEventListener("submit", function(event) {
    event.preventDefault();

    // Capturar los datos del formulario
    const formData = new FormData(document.getElementById("agendarCitaForm"));
    const nombre = formData.get("nombre");
    const edad = formData.get("edad");
    const raza = formData.get("raza");
    const fechaCita = formData.get("fecha_cita");
    const hora = formData.get("hora");
    const email = formData.get("email");

    // Enviar los datos al backend (PHP)
    fetch("agendar_cita.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        const respuestaDiv = document.getElementById("respuesta");

        // Mostrar mensaje de éxito
        respuestaDiv.innerHTML = `<p>${data}</p>`;

        // Crear link para agregar a Google Calendar
        const googleCalendarUrl = createGoogleCalendarLink(nombre, fechaCita, hora);
        respuestaDiv.innerHTML += `<p><a href="${googleCalendarUrl}" target="_blank">Agregar a Google Calendar</a></p>`;

        // Agregar link para descargar el archivo ICS
        const icsFileUrl = 'ruta_a_tu_archivo_ics/Cita_Veterinaria.ics';
        respuestaDiv.innerHTML += `<p><a href="${icsFileUrl}" download>Descargar archivo .ics</a></p>`;
    })
    .catch(error => {
        console.error("Error al agendar la cita:", error);
    });
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
