// ============================================
// Validaciones del lado del cliente, reutilizables por todos los
// módulos (alumnos.js, titulaciones.js, asignaturas.js, etc.).
//
// IMPORTANTE: estas son un complemento de UX. Las validaciones reales
// y definitivas siempre se hacen también en PHP (backend/api_*.php),
// porque el JS del navegador se puede desactivar o saltar.
// ============================================

/** Deja solo dígitos (para cédulas, RUC, teléfonos, etc.). */
function soloDigitos(valor) {
    return String(valor ?? '').replace(/\D+/g, '');
}

/** Deja solo letras (incluye tildes/ñ) y espacios simples (para nombres, apellidos). */
function soloLetras(valor) {
    return String(valor ?? '')
        .replace(/[^\p{L}\s]/gu, '')
        .replace(/\s+/g, ' ');
}

/** Recorta espacios sobrantes al inicio/fin y colapsa espacios dobles. */
function normalizarTexto(valor) {
    return String(valor ?? '').trim().replace(/\s+/g, ' ');
}

/**
 * Valida el dígito verificador de una cédula ecuatoriana (algoritmo módulo 10).
 * Devuelve true/false. Requiere exactamente 10 dígitos.
 */
function validarDigitoVerificadorCedula(numero) {
    if (!/^\d{10}$/.test(numero)) {
        return false;
    }

    var provincia = parseInt(numero.substring(0, 2), 10);
    if (provincia < 1 || provincia > 24) {
        return false;
    }

    var digitos = numero.split('').map(Number);
    var sumaPares = 0;
    var sumaImpares = 0;
    var i;

    for (i = 0; i < 9; i += 2) {
        var mul = digitos[i] * 2;
        if (mul > 9) {
            mul -= 9;
        }
        sumaPares += mul;
    }

    for (i = 1; i < 8; i += 2) {
        sumaImpares += digitos[i];
    }

    var sumaTotal = sumaPares + sumaImpares;
    var residuo = sumaTotal % 10;
    var digitoVerificador = residuo === 0 ? 0 : 10 - residuo;

    return digitoVerificador === digitos[9];
}

/** Valida una cédula completa (formato + dígito verificador). Devuelve mensaje de error o '' si es válida. */
function validarCedula(cedula) {
    var numero = soloDigitos(cedula);

    if (!numero) {
        return 'La cédula es obligatoria.';
    }
    if (numero.length !== 10) {
        return 'La cédula debe tener 10 dígitos.';
    }
    if (!validarDigitoVerificadorCedula(numero)) {
        return 'La cédula ingresada no es válida.';
    }
    return '';
}

/** Valida un nombre/apellido (solo letras, largo mínimo). Devuelve mensaje de error o ''. */
function validarNombrePropio(valor, etiqueta, minimo) {
    var texto = normalizarTexto(valor);
    minimo = minimo || 2;

    if (texto === '') {
        return etiqueta + ' es obligatorio.';
    }
    if (texto.length < minimo) {
        return etiqueta + ' debe tener al menos ' + minimo + ' caracteres.';
    }
    if (!/^\p{L}+(\s\p{L}+)*$/u.test(texto)) {
        return etiqueta + ' solo puede contener letras.';
    }
    return '';
}

/** Valida un correo electrónico. Devuelve mensaje de error o ''. */
function validarCorreo(correo) {
    if (!correo) {
        return 'El correo es obligatorio.';
    }
    var patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return patron.test(correo) ? '' : 'Ingresa un correo electrónico válido.';
}

/** Muestra u oculta el mensaje de error de un campo (espera un <div id="{idError}"> junto al input). */
function mostrarErrorCampo(idError, mensaje) {
    var elemento = document.getElementById(idError);
    if (!elemento) {
        return;
    }
    if (mensaje) {
        elemento.textContent = mensaje;
        elemento.classList.remove('d-none');
    } else {
        elemento.textContent = '';
        elemento.classList.add('d-none');
    }
}

/** Limpia varios mensajes de error de una vez. Recibe un array de ids de los divs de error. */
function limpiarErroresCampos(idsError) {
    idsError.forEach(function (id) {
        mostrarErrorCampo(id, '');
    });
}

document.addEventListener('DOMContentLoaded', function () {

    // --- Cédula: solo dígitos mientras se escribe, máximo 10 ---
    document.querySelectorAll('#cedula, input[name="cedula"]').forEach(function (input) {
        input.addEventListener('input', function () {
            this.value = soloDigitos(this.value).slice(0, 10);
        });
    });

    // --- Nombres y apellidos: solo letras mientras se escribe ---
    document.querySelectorAll('#nombre, #apellido, input[name="nombre"], input[name="apellido"]').forEach(function (input) {
        input.addEventListener('input', function () {
            var posicion = this.selectionStart;
            var largoAntes = this.value.length;
            this.value = soloLetras(this.value);
            var diferencia = largoAntes - this.value.length;
            if (posicion !== null) {
                this.setSelectionRange(posicion - diferencia, posicion - diferencia);
            }
        });
    });

    // --- Créditos: no permitir números negativos o en cero ---
    document.querySelectorAll('input[name="creditos"], #creditos').forEach(function (input) {
        input.addEventListener('input', function () {
            if (this.value !== '' && parseInt(this.value, 10) < 1) {
                this.value = 1;
            }
        });
    });

    // --- Fecha de nacimiento: no permitir fechas futuras ---
    document.querySelectorAll('input[name="fecha_nacimiento"], #fecha_nacimiento').forEach(function (input) {
        var hoy = new Date().toISOString().split('T')[0];
        input.setAttribute('max', hoy);
    });

    // --- Evitar doble envío de formularios (doble clic en "Guardar") ---
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var boton = form.querySelector('button[type="submit"]');
            if (boton) {
                boton.disabled = true;
                boton.dataset.textoOriginal = boton.textContent;
                boton.textContent = 'Guardando...';
                // Si algo falla y la página no navega, reactivamos el botón
                setTimeout(function () {
                    boton.disabled = false;
                    boton.textContent = boton.dataset.textoOriginal;
                }, 4000);
            }
        });
    });
});
