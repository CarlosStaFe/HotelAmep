import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/comandas/comanda.blade.php', import.meta.url), 'utf8');
const inicioTabla = vista.indexOf('        $(function() {');
const finTabla = vista.indexOf('    </script>', inicioTabla);
const inicioModal = vista.indexOf('        let articuloConsumo = null;', finTabla);
const finModal = vista.indexOf('    </script>', inicioModal);
assert.ok(inicioTabla !== -1 && finTabla !== -1 && inicioModal !== -1 && finModal !== -1);
const scriptTabla = vista.slice(inicioTabla, finTabla).replace("@json(route('comandas.cargar'))", '"/admin/comandas/cargar"');
const scriptModal = vista.slice(inicioModal, finModal);

function entorno() {
    function elemento() {
        return {
            dataset: {}, children: [], textContent: '', value: '', handlers: {},
            appendChild(hijo) { this.children.push(hijo); hijo.parent = this; },
            addEventListener(evento, callback) { this.handlers[evento] = callback; },
            setAttribute(nombre, valor) { this[nombre] = valor; },
            remove() { this.parent.children.splice(this.parent.children.indexOf(this), 1); },
            get cells() { return this.children; },
        };
    }
    const campos = {
        consumoProducto: elemento(), consumoPrecio: elemento(), consumoPrecioAviso: elemento(), consumoCantidad: elemento(),
        consumoMoza: elemento(), consumoMensaje: elemento(),
        habitacion: { value: '10' },
        pasajeros: { value: '5', disabled: false, selectedIndex: 0, options: [{ textContent: 'Ana - 123' }] },
        tablaConsumos: elemento(),
    };
    Object.defineProperty(campos.tablaConsumos, 'rows', { get() { return this.children; } });
    campos.formConsumo = { reset() { campos.consumoCantidad.value = ''; campos.consumoMoza.value = ''; } };
    let articulo;
    let config;
    let click;
    let submit;
    const modal = [];
    const dataTable = {
        row() { return { data: () => articulo }; },
        buttons() { return { container: () => ({ appendTo() {} }) }; },
    };
    function $(selector) {
        if (typeof selector === 'function') return selector();
        if (selector === '#example1') return { DataTable(opciones) { config = opciones; return dataTable; } };
        if (selector === '#example1 tbody') return { on(evento, subselector, handler) { click = handler; } };
        if (selector === '#formConsumo') return { on(evento, handler) { submit = handler; } };
        if (selector === '#consumoModal') return { modal(accion) { modal.push(accion); } };
        return { closest() { return { hasClass() { return false; } }; } };
    }
    $.fn = { dataTable: { render: { text() { return () => {}; } } } };
    const contexto = vm.createContext({
        $, document: {
            getElementById(id) { return campos[id]; },
            querySelector() { return { value: 'Bar' }; },
            createElement: elemento,
        },
    });
    vm.runInContext(scriptModal, contexto);
    vm.runInContext(scriptTabla, contexto);
    return {
        campos, modal, config,
        abrir(producto) { articulo = producto; click.call({}); },
        cargar() { let prevenido = false; submit({ preventDefault() { prevenido = true; } }); assert.ok(prevenido); },
    };
}

test('abre el modal del producto y carga un consumo temporal con pasajero y moza', () => {
    const { campos, modal, config, abrir, cargar } = entorno();
    assert.deepEqual(Array.from(config.columns, column => column.data), [
        null, 'subrubro', 'nombre', 'stock_deposito', 'stock_comedor', 'stock_bar', 'stock_recepcion', 'precio', 'id',
    ]);
    let claseNumero;
    config.columnDefs[0].createdCell({ classList: { add(clase) { claseNumero = clase; } } });
    assert.equal(claseNumero, 'text-right');
    assert.match(vista, /<th style="width: 30px; text-align: center;">Nro\.<\/th>/);
    assert.match(config.columns[8].render(), /class="btn btn-success cargar-consumo"/);
    abrir({ id: 8, nombre: '<Agua>', precio: '2.35' });
    assert.equal(campos.consumoProducto.value, '<Agua>');
    assert.equal(campos.consumoPrecio.value, '2.35');
    assert.deepEqual(modal, ['show']);
    campos.consumoCantidad.value = '0.1';
    campos.consumoMoza.value = '12';
    cargar();
    const fila = campos.tablaConsumos.rows[0];
    assert.equal(fila.dataset.articuloId, 8);
    assert.equal(fila.dataset.pasajeroId, '5');
    assert.equal(fila.dataset.moza, '12');
    assert.equal(fila.dataset.habitacion, '10');
    assert.equal(fila.dataset.punto, 'Bar');
    assert.deepEqual(fila.cells.slice(0, 8).map(celda => celda.textContent), [
        '1', '8', '<Agua>', '2.35', '0.1', '0.24', 'Ana - 123', '12',
    ]);
    assert.deepEqual(fila.cells.slice(0, 8).map(celda => celda.className || ''), [
        'text-right', 'd-none', '', 'text-right', 'text-right', 'text-right', '', '',
    ]);
    assert.deepEqual(modal, ['show', 'hide']);
    abrir({ id: 9, nombre: 'Café', precio: 10 });
    campos.consumoCantidad.value = '2';
    campos.consumoMoza.value = '3';
    cargar();
    assert.equal(campos.tablaConsumos.rows.length, 2);
    fila.cells[8].children[0].handlers.click();
    assert.equal(campos.tablaConsumos.rows.length, 1);
    assert.equal(campos.tablaConsumos.rows[0].cells[0].textContent, '1');
});

test('no carga datos inválidos ni productos sin precio', () => {
    const { campos, abrir, cargar } = entorno();
    abrir({ id: 1, nombre: 'Agua', precio: 'N/A' });
    assert.equal(campos.consumoPrecio.value, '0.00');
    assert.match(campos.consumoPrecioAviso.textContent, /Precio no registrado/);
    campos.pasajeros.value = '';
    cargar();
    assert.match(campos.consumoMensaje.textContent, /Seleccione un pasajero/);
    campos.pasajeros.value = '5';
    campos.consumoCantidad.value = '0';
    cargar();
    assert.match(campos.consumoMensaje.textContent, /cantidad/);
    campos.consumoCantidad.value = '1';
    campos.consumoMoza.value = '1.5';
    cargar();
    assert.match(campos.consumoMensaje.textContent, /moza/);
    campos.consumoMoza.value = '1';
    cargar();
    assert.match(campos.consumoMensaje.textContent, /precio válido/);
    assert.equal(campos.tablaConsumos.rows.length, 0);
    abrir({ id: 2, nombre: 'Café', precio: 0 });
    assert.equal(campos.consumoPrecio.value, 0);
    assert.equal(campos.consumoPrecioAviso.textContent, '');
});

test('muestra cero para precios no informados y alinea importes y stocks a la derecha', () => {
    const { config } = entorno();
    const precio = config.columns[7].render;
    assert.equal(precio('N/A'), 0);
    assert.equal(precio(null), 0);
    assert.equal(precio(''), 0);
    assert.equal(precio('25.50'), '25.50');
    assert.equal(precio(0), 0);
    assert.deepEqual(Array.from(config.columnDefs[0].targets), [0, 3, 4, 5, 6, 7]);
    for (const titulo of ['Deposito', 'Comedor', 'Bar', 'Recepción', 'Precio']) {
        assert.match(vista, new RegExp(`<th style="width: \\d+px; text-align: center;">${titulo}</th>`));
    }
});

function entornoGrabacion(fetch) {
    const referencia = vista.indexOf("            const grabar = document.getElementById('grabarComanda');");
    const inicio = vista.lastIndexOf("        document.addEventListener('DOMContentLoaded'", referencia);
    const fin = vista.indexOf('    </script>', referencia);
    assert.ok(referencia !== -1 && inicio !== -1 && fin !== -1);
    const script = vista.slice(inicio, fin)
        .replace("@json(route('comandas.store'))", '"/admin/comandas"')
        .replace('@json(csrf_token())', '"csrf"');
    const grabar = {
        disabled: false, handlers: {},
        addEventListener(evento, handler) { this.handlers[evento] = handler; },
    };
    const cerrar = {
        handlers: {},
        addEventListener(evento, handler) { this.handlers[evento] = handler; },
    };
    const mensaje = { textContent: '' };
    const contenedorMensaje = { hidden: true };
    const habitacion = {
        value: '10', eventos: [],
        dispatchEvent(evento) { this.eventos.push(evento.type); },
    };
    const punto = { value: 'Bar', checked: true };
    const formConsumo = { reiniciado: false, reset() { this.reiniciado = true; } };
    const consumoPrecioAviso = { textContent: 'Aviso' };
    const consumoMensaje = { textContent: 'Mensaje' };
    const tabla = {
        rows: [],
        replaceChildren() { this.rows = []; },
    };
    const campos = {
        grabarComanda: grabar, cerrarComandaMensaje: cerrar, comandaMensaje: mensaje,
        comandaMensajeContenedor: contenedorMensaje, habitacion, tablaConsumos: tabla,
        formConsumo, consumoPrecioAviso, consumoMensaje,
    };
    let recargas = 0;
    let busqueda = 'filtro';
    const document = {
        getElementById(id) { return campos[id]; },
        querySelector() { return punto; },
        addEventListener(evento, handler) { if (evento === 'DOMContentLoaded') handler(); },
    };
    const $ = () => ({ DataTable: () => ({
        search(valor) { busqueda = valor; },
        ajax: { reload() { recargas++; } },
    }) });
    class Event { constructor(type) { this.type = type; } }
    vm.runInNewContext(script, { document, fetch, $, Event, console: { error() {} } });
    return {
        grabar, cerrar, mensaje, contenedorMensaje, habitacion, punto, tabla, formConsumo, consumoPrecioAviso, consumoMensaje,
        recargas: () => recargas, busqueda: () => busqueda,
    };
}

test('graba los consumos del punto elegido y muestra advertencias de correo', async () => {
    let solicitud;
    const campos = entornoGrabacion(async (url, opciones) => {
        solicitud = { url, opciones };
        return { ok: true, status: 200, json: async () => ({ mensaje: 'Guardada.', correos_enviados: 0, advertencias: ['Correo no enviado.'] }) };
    });
    campos.tabla.rows = [{ dataset: {
        habitacion: '10', punto: 'Bar', articuloId: '8', pasajeroId: '5',
        cantidad: '0.1', precio: '2.35', moza: '12',
    } }];
    await campos.grabar.handlers.click();
    assert.equal(solicitud.url, '/admin/comandas');
    assert.deepEqual(JSON.parse(solicitud.opciones.body), {
        habitacion: '10', punto: 'Bar',
        detalles: [{ articulo_id: '8', pasajero_id: '5', cantidad: '0.1', precio: '2.35', moza: '12' }],
    });
    assert.equal(campos.tabla.rows.length, 0);
    assert.equal(campos.mensaje.textContent, 'Guardada. Se enviaron 0 correos. Correo no enviado.');
    assert.equal(campos.contenedorMensaje.className, 'alert alert-warning');
    assert.equal(campos.contenedorMensaje.hidden, false);
    assert.equal(campos.habitacion.value, '');
    assert.deepEqual(campos.habitacion.eventos, ['change']);
    assert.equal(campos.punto.checked, false);
    assert.equal(campos.formConsumo.reiniciado, true);
    assert.equal(campos.consumoPrecioAviso.textContent, '');
    assert.equal(campos.consumoMensaje.textContent, '');
    assert.equal(campos.busqueda(), '');
    assert.equal(campos.grabar.disabled, false);
    assert.equal(campos.recargas(), 1);
});

test('confirma el envío y limpia el formulario cuando no hay advertencias', async () => {
    const campos = entornoGrabacion(async () => ({
        ok: true, status: 200, json: async () => ({ mensaje: 'Guardada.', correos_enviados: 1, advertencias: [] }),
    }));
    campos.tabla.rows = [{ dataset: { habitacion: '10', punto: 'Bar' } }];
    await campos.grabar.handlers.click();
    assert.equal(campos.mensaje.textContent, 'Guardada. Se envió 1 correo.');
    assert.equal(campos.contenedorMensaje.className, 'alert alert-success');
    assert.equal(campos.contenedorMensaje.hidden, false);
    campos.cerrar.handlers.click();
    assert.equal(campos.contenedorMensaje.hidden, true);
    assert.equal(campos.mensaje.textContent, '');
    assert.equal(campos.tabla.rows.length, 0);
    assert.equal(campos.habitacion.value, '');
    assert.equal(campos.punto.checked, false);
});

test('conserva la lista cuando hay error y evita reintentar un resultado desconocido', async () => {
    let respuesta = { ok: false, status: 422, json: async () => ({ errors: { detalles: ['Stock insuficiente.'] } }) };
    const campos = entornoGrabacion(async () => respuesta);
    await campos.grabar.handlers.click();
    assert.match(campos.mensaje.textContent, /al menos un consumo/);
    campos.tabla.rows = [{ dataset: { habitacion: '10', punto: 'Comedor' } }];
    await campos.grabar.handlers.click();
    assert.match(campos.mensaje.textContent, /habitación y al punto/);
    campos.tabla.rows[0].dataset.punto = 'Bar';
    await campos.grabar.handlers.click();
    assert.equal(campos.mensaje.textContent, 'Stock insuficiente.');
    assert.equal(campos.contenedorMensaje.hidden, false);
    campos.cerrar.handlers.click();
    assert.equal(campos.contenedorMensaje.hidden, true);
    assert.equal(campos.mensaje.textContent, '');
    assert.equal(campos.tabla.rows.length, 1);
    assert.equal(campos.habitacion.value, '10');
    assert.equal(campos.punto.checked, true);
    assert.equal(campos.grabar.disabled, false);
    respuesta = { ok: false, status: 500 };
    await campos.grabar.handlers.click();
    assert.match(campos.mensaje.textContent, /Verifique antes de volver/);
    assert.equal(campos.tabla.rows.length, 1);
    assert.equal(campos.grabar.disabled, true);
});
