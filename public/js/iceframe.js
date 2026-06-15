// ============================================================
//  IceFrame Inventory - interacciones de interfaz
//  - Auto-cierre de alertas
//  - Tom Select (buscadores con autocompletado)
//  - Anti-doble-clic en formularios (data-once)
//  - Modales "+ Nueva" para crear catálogos al vuelo (fetch JSON)
//  - Gráficos del dashboard (ApexCharts)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    autocerrarAlertas();
    iniciarTomSelect();
    iniciarAntiDobleClic();
    iniciarModalesCatalogo();
    animarContadores();
    animarProgresos();
    iniciarGraficosDashboard();
});

// ---------- Contadores animados (count-up) ----------
function animarContadores() {
    const respeta = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('[data-count]').forEach(function (el) {
        const destino = parseFloat(el.dataset.count) || 0;
        const decimales = parseInt(el.dataset.decimals || '0', 10);
        const prefijo = el.dataset.prefix || '';
        const fmt = function (n) {
            return prefijo + n.toLocaleString('es-EC', { minimumFractionDigits: decimales, maximumFractionDigits: decimales });
        };
        if (respeta || destino === 0) { el.textContent = fmt(destino); return; }

        const duracion = 900;
        const inicio = performance.now();
        function paso(ahora) {
            const t = Math.min((ahora - inicio) / duracion, 1);
            const eased = 1 - Math.pow(1 - t, 3); // easeOutCubic
            el.textContent = fmt(destino * eased);
            if (t < 1) requestAnimationFrame(paso);
            else el.textContent = fmt(destino);
        }
        requestAnimationFrame(paso);
    });
}

// ---------- Barras de progreso animadas ----------
function animarProgresos() {
    document.querySelectorAll('.iceframe-progress > span[data-width]').forEach(function (span) {
        requestAnimationFrame(function () {
            setTimeout(function () { span.style.width = span.dataset.width; }, 150);
        });
    });
}

// ---------- Token CSRF ----------
function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// ---------- Auto-cierre de alertas de éxito ----------
function autocerrarAlertas() {
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        setTimeout(function () {
            if (window.bootstrap && bootstrap.Alert) {
                bootstrap.Alert.getOrCreateInstance(alert).close();
            }
        }, 5000);
    });
}

// ---------- Tom Select: autocompletado en <select class="select-buscable"> ----------
function iniciarTomSelect() {
    if (typeof TomSelect === 'undefined') return;
    document.querySelectorAll('select.select-buscable').forEach(function (sel) {
        if (sel.tomselect) return; // ya inicializado
        const placeholderConfigurado = sel.dataset.placeholder || '';
        const opcionVacia = Array.from(sel.options).find(function (option) {
            return option.value === '';
        });
        const placeholder = placeholderConfigurado
            || (opcionVacia ? opcionVacia.textContent.trim() : 'Buscar…');

        if (placeholderConfigurado && opcionVacia) {
            opcionVacia.textContent = '';
        }

        new TomSelect(sel, {
            allowEmptyOption: !placeholderConfigurado,
            placeholder: placeholder,
            // Con placeholder, la opción vacía no se lista; el botón "limpiar"
            // permite volver al estado por defecto (p. ej. "Todas").
            plugins: placeholderConfigurado ? ['clear_button'] : [],
            sortField: { field: 'text', direction: 'asc' },
            onFocus: function () {
                if (placeholderConfigurado && !this.getValue()) {
                    this.control_input.placeholder = '';
                }
            },
            onBlur: function () {
                if (placeholderConfigurado && !this.getValue()) {
                    this.control_input.placeholder = placeholder;
                }
            },
        });
    });
}

// ---------- Anti-doble-clic genérico ----------
// Cualquier formulario con [data-once] se bloquea tras el primer submit:
// el botón se deshabilita y cambia a "Procesando…". Combinado con la guardia
// de idempotencia del backend, evita ventas duplicadas por doble clic.
function iniciarAntiDobleClic() {
    document.querySelectorAll('form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.dataset.submitting === '1') return;
            form.dataset.submitting = '1';
            const btn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (btn) {
                // setTimeout(0): permite que el valor del botón se envíe antes de deshabilitar.
                setTimeout(function () {
                    btn.disabled = true;
                    if (btn.tagName === 'BUTTON') {
                        btn.dataset.originalHtml = btn.innerHTML;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando…';
                    }
                }, 0);
            }
        });
    });
}

// ---------- Modales "+ Nueva" (crear catálogo al vuelo) ----------
function iniciarModalesCatalogo() {
    document.querySelectorAll('.iceframe-catalogo-guardar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const modal = btn.closest('.modal');
            const url = btn.dataset.url;
            const targetSel = document.querySelector(btn.dataset.target);
            const errorBox = modal.querySelector('.iceframe-modal-error');

            const payload = {};
            let primerInvalido = null;
            modal.querySelectorAll('[data-field]').forEach(function (input) {
                payload[input.dataset.field] = input.value.trim();
                if (!primerInvalido && input.hasAttribute('data-required') && !input.value.trim()) {
                    primerInvalido = input;
                }
            });

            errorBox.classList.add('d-none');
            errorBox.textContent = '';

            if (primerInvalido) {
                errorBox.textContent = 'Todos los campos son obligatorios.';
                errorBox.classList.remove('d-none');
                primerInvalido.focus();
                return;
            }

            btn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(payload),
            })
                .then(function (resp) {
                    return resp.json().then(function (data) {
                        return { ok: resp.ok, status: resp.status, data: data };
                    });
                })
                .then(function (res) {
                    if (!res.ok) {
                        let msg = 'No se pudo guardar.';
                        if (res.data && res.data.errors) {
                            msg = Object.values(res.data.errors).flat().join(' ');
                        } else if (res.data && res.data.message) {
                            msg = res.data.message;
                        }
                        errorBox.textContent = msg;
                        errorBox.classList.remove('d-none');
                        return;
                    }

                    // Insertar y seleccionar la nueva opción (con o sin Tom Select).
                    agregarOpcion(targetSel, res.data.id, res.data.nombre);

                    // Limpiar inputs y cerrar el modal.
                    modal.querySelectorAll('[data-field]').forEach(function (i) { i.value = ''; });
                    if (window.bootstrap && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(modal).hide();
                    }
                })
                .catch(function () {
                    errorBox.textContent = 'Error de conexión. Inténtelo de nuevo.';
                    errorBox.classList.remove('d-none');
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    });
}

function agregarOpcion(select, id, nombre) {
    if (!select) return;
    if (select.tomselect) {
        select.tomselect.addOption({ value: String(id), text: nombre });
        select.tomselect.refreshOptions(false);
        select.tomselect.setValue(String(id));
    } else {
        const opt = new Option(nombre, id, true, true);
        select.add(opt);
        select.value = String(id);
    }
}

// ============================================================
//  GRÁFICOS (ApexCharts)
// ============================================================

const ICE_COLORS = {
    hielo: '#0ea5e9',
    azul: '#0ea5e9',
    verde: '#22c55e',
    rojo: '#ef4444',
    amarillo: '#f59e0b',
};

// Paleta de barras (degradado de azules hielo, estilo "vivo").
const ICE_BAR_PALETTE = ['#0ea5e9', '#38bdf8', '#22c55e', '#7c3aed', '#f59e0b'];

const ICE_BASE = {
    fontFamily: "'Inter', sans-serif",
    foreColor: '#64748b',
    animaciones: { enabled: true, easing: 'easeinout', speed: 800, animateGradually: { enabled: true, delay: 120 } },
    grid: { borderColor: 'rgba(112,144,176,.16)', strokeDashArray: 4, padding: { left: 6, right: 6 } },
};

function moneda(v) {
    return '$' + Number(v).toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function dibujarArea(elId, labels, valores) {
    return new ApexCharts(document.querySelector(elId), {
        chart: { type: 'area', height: '100%', fontFamily: ICE_BASE.fontFamily, foreColor: ICE_BASE.foreColor, toolbar: { show: false }, animations: ICE_BASE.animaciones, sparkline: { enabled: false } },
        series: [{ name: 'Ventas', data: valores }],
        xaxis: { categories: labels, axisBorder: { show: false }, axisTicks: { show: false }, tooltip: { enabled: false } },
        yaxis: { labels: { formatter: function (v) { return '$' + Math.round(v); } } },
        colors: [ICE_COLORS.hielo],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.02, stops: [0, 95] } },
        markers: { size: 0, hover: { size: 6 }, colors: ['#0ea5e9'], strokeColors: '#fff', strokeWidth: 2 },
        dataLabels: { enabled: false },
        grid: ICE_BASE.grid,
        tooltip: { y: { formatter: moneda } },
    }).render();
}

function dibujarBarrasHorizontal(elId, labels, valores) {
    return new ApexCharts(document.querySelector(elId), {
        chart: { type: 'bar', height: '100%', fontFamily: ICE_BASE.fontFamily, foreColor: ICE_BASE.foreColor, toolbar: { show: false }, animations: ICE_BASE.animaciones },
        series: [{ name: 'Unidades', data: valores }],
        xaxis: { categories: labels, axisBorder: { show: false } },
        colors: ICE_BAR_PALETTE,
        plotOptions: { bar: { horizontal: true, borderRadius: 6, borderRadiusApplication: 'end', distributed: true, barHeight: '62%' } },
        dataLabels: { enabled: true, style: { fontWeight: 700, colors: ['#fff'] } },
        legend: { show: false },
        grid: ICE_BASE.grid,
        tooltip: { y: { formatter: function (v) { return v + ' u.'; } } },
    }).render();
}

function dibujarDona(elId, labels, valores) {
    const total = valores.reduce(function (a, b) { return a + Number(b); }, 0);
    return new ApexCharts(document.querySelector(elId), {
        chart: { type: 'donut', height: '100%', fontFamily: ICE_BASE.fontFamily, foreColor: ICE_BASE.foreColor, animations: ICE_BASE.animaciones },
        series: valores,
        labels: labels,
        // Orden: Venta, Reposicion, Hurto, Ajuste
        colors: [ICE_COLORS.azul, ICE_COLORS.verde, ICE_COLORS.rojo, ICE_COLORS.amarillo],
        legend: { position: 'bottom', fontWeight: 600 },
        stroke: { width: 2, colors: ['#fff'] },
        dataLabels: { enabled: true, style: { fontWeight: 700 } },
        plotOptions: { pie: { donut: { size: '68%', labels: {
            show: true,
            value: { fontSize: '1.6rem', fontWeight: 800, color: '#2b3674' },
            total: { show: true, label: 'Total', fontWeight: 600, color: '#a3aed0', formatter: function () { return total; } },
        } } } },
    }).render();
}

function dibujarBarrasVertical(elId, labels, valores) {
    return new ApexCharts(document.querySelector(elId), {
        chart: { type: 'bar', height: '100%', fontFamily: ICE_BASE.fontFamily, foreColor: ICE_BASE.foreColor, toolbar: { show: false }, animations: ICE_BASE.animaciones },
        series: [{ name: 'Valor', data: valores }],
        xaxis: { categories: labels, axisBorder: { show: false } },
        yaxis: { labels: { formatter: function (v) { return '$' + Math.round(v); } } },
        colors: ICE_BAR_PALETTE,
        plotOptions: { bar: { horizontal: false, borderRadius: 8, borderRadiusApplication: 'end', distributed: true, columnWidth: '52%' } },
        dataLabels: { enabled: false },
        legend: { show: false },
        grid: ICE_BASE.grid,
        fill: { type: 'gradient', gradient: { shade: 'light', type: 'vertical', shadeIntensity: 0.3, opacityFrom: 1, opacityTo: 0.85 } },
        tooltip: { y: { formatter: moneda } },
    }).render();
}

function hayDatos(arr) {
    return Array.isArray(arr) && arr.some(function (v) { return Number(v) > 0; });
}

// ---------- Dashboard (versiones resumidas) ----------
function iniciarGraficosDashboard() {
    if (typeof ApexCharts === 'undefined' || !window.IceframeCharts) return;
    const d = window.IceframeCharts;

    if (document.querySelector('#chart-ventas')) {
        dibujarArea('#chart-ventas', d.ventas.labels, d.ventas.valores);
    }
    if (document.querySelector('#chart-movimientos')) {
        if (hayDatos(d.movimientos.valores)) {
            dibujarDona('#chart-movimientos', d.movimientos.labels, d.movimientos.valores);
        } else {
            mensajeVacio('#chart-movimientos');
        }
    }
    if (document.querySelector('#chart-top')) {
        if (d.top.labels.length) {
            dibujarBarrasHorizontal('#chart-top', d.top.labels, d.top.valores);
        } else {
            mensajeVacio('#chart-top');
        }
    }
    if (document.querySelector('#chart-categorias')) {
        if (d.categorias.labels.length) {
            dibujarBarrasVertical('#chart-categorias', d.categorias.labels, d.categorias.valores);
        } else {
            mensajeVacio('#chart-categorias');
        }
    }
}

function mensajeVacio(elId) {
    const el = document.querySelector(elId);
    if (el) el.innerHTML = '<div class="text-center text-secondary py-5">Sin datos para mostrar.</div>';
}
