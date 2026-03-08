// Script para manejo de sesiones y navegación dinámica
(function() {
    'use strict';

    // Función para verificar si el usuario está logueado
    function verificarSesion() {
        const usuarioData = localStorage.getItem('usuarioLogueado') || sessionStorage.getItem('usuarioLogueado');
        return usuarioData ? JSON.parse(usuarioData) : null;
    }

    // Función para actualizar la navegación según el estado de sesión
    function actualizarNavegacion() {
        const usuario = verificarSesion();
        const loginLink = document.querySelector('a[href="login.html"]');
        
        if (usuario && loginLink) {
            // Usuario logueado - cambiar Login por Dashboard
            loginLink.href = 'dashboard.html';
            loginLink.textContent = 'Dashboard';
            loginLink.style.backgroundColor = '#4CAF50';
            loginLink.style.color = 'white';
        } else if (!usuario && loginLink) {
            // Usuario no logueado - asegurar que sea Login
            loginLink.href = 'login.html';
            loginLink.textContent = 'Login';
            loginLink.style.backgroundColor = '';
            loginLink.style.color = '';
        }
    }

    // Función para cerrar sesión
    function cerrarSesion() {
        localStorage.removeItem('usuarioLogueado');
        sessionStorage.removeItem('usuarioLogueado');
        window.location.href = 'login.html';
    }

    // Función para redirigir al dashboard si ya está logueado
    function redirigirSiLogueado() {
        const usuario = verificarSesion();
        if (usuario && window.location.pathname.includes('login.html')) {
            window.location.href = 'dashboard.html';
        }
    }

    // Función para proteger páginas que requieren autenticación
    function protegerPagina() {
        const usuario = verificarSesion();
        if (!usuario && window.location.pathname.includes('dashboard.html')) {
            window.location.href = 'login.html';
        }
    }

    // Ejecutar funciones al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        actualizarNavegacion();
        redirigirSiLogueado();
        protegerPagina();
    });

    // Exponer funciones globalmente
    window.sessionManager = {
        verificarSesion: verificarSesion,
        cerrarSesion: cerrarSesion,
        actualizarNavegacion: actualizarNavegacion
    };
})(); 