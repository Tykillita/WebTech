let mostrador = document.getElementById("mostrador");
let seleccion = document.getElementById("seleccion");
let imgSeleccionada = document.getElementById("img");
let modeloSeleccionado = document.getElementById("modelo");
let descripSeleccionada = document.getElementById("descripcion");
let precioSeleccionado = document.getElementById("precio");



function cargar(item) {
    quitarBordes();
    mostrador.style.width = "60%";
    seleccion.style.width = "40%";
    seleccion.style.opacity = "1";
    item.style.border = "2px solid red";

    imgSeleccionada.src = item.getElementsByTagName("img")[0].src;
    modeloSeleccionado.innerHTML = item.getAttribute("data-nombre");
    descripSeleccionada.innerHTML = "Precio Producto";
    precioSeleccionado.innerHTML = `$${parseFloat(item.getAttribute("data-precio")).toFixed(2)}`;

    const button = document.querySelector(".agregar-carrito");
    button.setAttribute("data-id", item.getAttribute("data-id"));
    button.setAttribute("data-nombre", item.getAttribute("data-nombre"));
    button.setAttribute("data-precio", item.getAttribute("data-precio"));
}

function cerrar() {
    mostrador.style.width = "100%";
    seleccion.style.width = "0%";
    seleccion.style.opacity = "0";
    quitarBordes();
}

function quitarBordes() {
    const items = document.getElementsByClassName("item");
    for (let item of items) {
        item.style.border = "none";
    }
}


document.querySelectorAll(".agregar-carrito").forEach(button => {
    button.addEventListener("click", function () {
        agregarAlCarrito(this);
    });
});

function agregarAlCarrito(button) {
    const producto = {
        id: button.getAttribute("data-id"),
        nombre: button.getAttribute("data-nombre"),
        precio: parseFloat(button.getAttribute("data-precio")),
        imagen: document.getElementById("img").src,
        cantidad: 1
    };

    if (!producto.id || !producto.nombre || isNaN(producto.precio) || !producto.imagen) {
        alert("Error al agregar el producto. Por favor, inténtalo nuevamente.");
        return;
    }

    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    const productoExistente = carrito.find(item => item.id === producto.id);

    if (productoExistente) {
        productoExistente.cantidad++;
    } else {
        carrito.push(producto);
    }

    localStorage.setItem("carrito", JSON.stringify(carrito));
    alert("Producto agregado al carrito.");
    mostrarCarrito();
}

// Mostrar productos del carrito
function mostrarCarrito() {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    const carritoDiv = document.getElementById("carrito");

    // Verificar si el contenedor existe
    console.log(carritoDiv); // Verifica si el contenedor se encuentra en el DOM

    if (!carritoDiv) {
        console.error("El contenedor del carrito no existe en el DOM.");
        return;
    }

    if (carrito.length === 0) {
        carritoDiv.innerHTML = "<p>Tu carrito está vacío.</p>";
        return;
    }

    let carritoHTML = "<h2>Tu Carrito</h2><ul>";
    carrito.forEach(producto => {
        carritoHTML += `
            <li>
                <img src="${producto.imagen}" alt="${producto.nombre}" width="100">
                <p>${producto.nombre}</p>
                <p>Cantidad: ${producto.cantidad}</p>
                <p>Precio Unitario: $${producto.precio.toFixed(2)}</p>
                <p>Total: $${(producto.precio * producto.cantidad).toFixed(2)}</p>
                <button onclick="cambiarCantidad('${producto.id}', 1)">+</button>
                <button onclick="cambiarCantidad('${producto.id}', -1)">-</button>
                <button onclick="eliminarProducto('${producto.id}')">Eliminar</button>
            </li>
        `;
    });
    carritoHTML += "</ul>";
    carritoDiv.innerHTML = carritoHTML;
}

function cambiarCantidad(id, cambio) {
    let carrito = JSON.parse(localStorage.getItem("carrito"));
    const producto = carrito.find(item => item.id === id);

    if (producto) {
        producto.cantidad += cambio;
        if (producto.cantidad <= 0) {
            carrito = carrito.filter(item => item.id !== id);
        }
        localStorage.setItem("carrito", JSON.stringify(carrito));
        mostrarCarrito();
    }
}

function eliminarProducto(id) {
    let carrito = JSON.parse(localStorage.getItem("carrito"));
    carrito = carrito.filter(item => item.id !== id);
    localStorage.setItem("carrito", JSON.stringify(carrito));
    mostrarCarrito();
}

document.addEventListener("DOMContentLoaded", mostrarCarrito);
