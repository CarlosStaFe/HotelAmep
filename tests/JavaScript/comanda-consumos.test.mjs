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

function opcionesListado(document = {}, tabla = {}, pdfMake = undefined, fetch = undefined, FileReader = undefined, Date = globalThis.Date) {
    const listado = readFileSync(new URL('../../resources/views/admin/comandas/index.blade.php', import.meta.url), 'utf8');
    const inicio = listado.indexOf('        $(function() {');
    const fin = listado.indexOf('    </script>', inicio);
    assert.ok(inicio !== -1 && fin !== -1);
    let opciones;
    const $ = selector => {
        if (typeof selector === 'function') return selector();
        assert.equal(selector, '#tablaComandas');
        return { DataTable(config) { opciones = config; return tabla; } };
    };
    $.fn = { dataTable: { ext: { type: { order: {} } } } };
    if (!document.getElementById) document.getElementById = () => ({ addEventListener() {} });
    const script = listado.slice(inicio, fin).replace("@json(asset('assets/img/pinarverde.png'))", '"/assets/img/pinarverde.png"');
    vm.runInNewContext(script, { $, document, pdfMake, fetch, FileReader, Date, console: { error() {} } });
    return opciones;
}

test('el listado ordena por habitación y luego por fecha ascendente', () => {
    const opciones = opcionesListado();
    assert.deepEqual(Array.from(opciones.order, criterio => Array.from(criterio)), [[2, 'asc'], [1, 'asc'], [0, 'asc']]);
});

test('genera PDF de comandas abiertas de la habitación por pasajero y fecha, sin depender de filtros', async () => {
    const campos = {};
    for (const id of ['habitacionCerrar', 'comandaMensaje', 'comandaMensajeContenedor', 'cerrarComandaMensaje', 'cerrarComanda']) {
        campos[id] = { value: '', handlers: {}, addEventListener(evento, callback) { this.handlers[evento] = callback; } };
    }
    const fila = (id, fecha, habitacion, pasajero, importe, estado, pasajeroId, precio = '4.00') => ({
        dataset: { pasajeroId },
        cells: [id, fecha, habitacion, 'Bar', pasajero, 'Café', '1', precio, importe, '3', estado, '']
            .map(textContent => ({ textContent })),
    });
    const filas = [
        fila('3', '2026-11-20 12:00:00', '10', 'Luis', '4.00', 'Abierta', '2'),
        fila('2', '2026-11-20 11:00:00', '10', 'Ana', '-1234.56', 'Abierta', '1'),
        fila('1', '2026-11-19 10:00:00', '10', 'Ana', '1234567.89', 'Abierta', '1', '1234.5'),
        fila('4', '2026-11-20 10:00:00', '10', 'Ana', '100.00', 'Cerrada', '1'),
        fila('5', '2026-11-20 10:00:00', '100', 'Ana', '100.00', 'Abierta', '1'),
        fila('6', '2026-11-18 10:00:00', '10', 'Ana', '0.10', 'Abierta', '3'),
    ];
    const tabla = { rows(selector) {
        assert.equal(selector.search, 'none');
        return { nodes: () => ({ toArray: () => filas }) };
    } };
    let pdf;
    let archivo;
    const pdfMake = { createPdf(definicion) {
        pdf = definicion;
        return { download(nombre) { archivo = nombre; } };
    } };
    const fetch = async url => {
        assert.equal(url, '/assets/img/pinarverde.png');
        return { ok: true, blob: async () => 'imagen' };
    };
    class FileReader {
        readAsDataURL(blob) { assert.equal(blob, 'imagen'); this.result = 'data:image/png;base64,logo'; this.onload(); }
    }
    const ahora = new Date('2026-11-20T16:55:16');
    class FechaFija extends Date { constructor() { super(ahora); } }
    opcionesListado({ getElementById: id => campos[id] }, tabla, pdfMake, fetch, FileReader, FechaFija);
    await campos.cerrarComanda.handlers.click();
    assert.equal(campos.comandaMensaje.textContent, 'Seleccione una habitación.');
    assert.equal(pdf, undefined);
    campos.habitacionCerrar.value = '10';
    await campos.cerrarComanda.handlers.click();
    assert.equal(campos.comandaMensajeContenedor.hidden, true);
    assert.equal(archivo, 'comanda_10_2026-11-20.pdf');
    assert.equal(pdf.pageSize, 'A4');
    assert.equal(pdf.pageOrientation, 'portrait');
    assert.equal(pdf.content[0].columns[0].image, 'data:image/png;base64,logo');
    assert.equal(pdf.content[0].columns[1].text, 'Hotel Pinar del Río');
    assert.equal(pdf.content[0].columns[2].text, `Generado: ${new Intl.DateTimeFormat('es-AR', { dateStyle: 'short', timeStyle: 'medium', hour12: false }).format(ahora)}`);
    assert.equal(pdf.content[1].text, 'Consumos abiertos - Habitación 10');
    const cuerpo = pdf.content[2].table.body;
    assert.ok(cuerpo[0].every(celda => celda.bold === true));
    assert.equal(cuerpo[1][5], '1,234.50');
    for (const fila of cuerpo.slice(1).filter(fila => typeof fila[0] === 'object')) {
        assert.equal(fila[0].bold, true);
        assert.equal(fila[6].bold, true);
    }
    assert.deepEqual(Array.from(cuerpo.slice(1), row => [
        row[0].text || row[0], typeof row[1] === 'string' ? row[1] : '', row[6].text || row[6],
    ]), [
        ['Ana', '2026-11-19 10:00:00', '1,234,567.89'],
        ['Ana', '2026-11-20 11:00:00', '-1,234.56'],
        ['Total pasajero: Ana (ID 1)', '', '1,233,333.33'],
        ['Ana', '2026-11-18 10:00:00', '0.10'],
        ['Total pasajero: Ana (ID 3)', '', '0.10'],
        ['Luis', '2026-11-20 12:00:00', '4.00'],
        ['Total pasajero: Luis (ID 2)', '', '4.00'],
        ['Total habitación 10', '', '1,233,337.43'],
    ]);
    campos.habitacionCerrar.value = '30';
    await campos.cerrarComanda.handlers.click();
    assert.match(campos.comandaMensaje.textContent, /No hay comandas abiertas/);
});

test('informa el error si no se puede cargar el logo del PDF', async () => {
    const campos = {};
    for (const id of ['habitacionCerrar', 'comandaMensaje', 'comandaMensajeContenedor', 'cerrarComandaMensaje', 'cerrarComanda']) {
        campos[id] = { value: '10', handlers: {}, addEventListener(evento, callback) { this.handlers[evento] = callback; } };
    }
    const fila = { dataset: { pasajeroId: '1' }, cells: ['1', '2026-11-20 12:00:00', '10', 'Bar', 'Ana', 'Agua', '1', '4', '4', '1', 'Abierta', '']
        .map(textContent => ({ textContent })) };
    const tabla = { rows: () => ({ nodes: () => ({ toArray: () => [fila] }) }) };
    let creado = false;
    const pdfMake = { createPdf() { creado = true; } };
    opcionesListado({ getElementById: id => campos[id] }, tabla, pdfMake, async () => ({ ok: false, status: 404 }));
    await campos.cerrarComanda.handlers.click();
    assert.match(campos.comandaMensaje.textContent, /No se pudo cargar el logo/);
    assert.equal(campos.cerrarComanda.disabled, false);
    assert.equal(creado, false);
});

test('informa si no está disponible la herramienta de PDF', () => {
    const campos = {};
    for (const id of ['habitacionCerrar', 'comandaMensaje', 'comandaMensajeContenedor', 'cerrarComandaMensaje', 'cerrarComanda']) {
        campos[id] = { value: '10', handlers: {}, addEventListener(evento, callback) { this.handlers[evento] = callback; } };
    }
    opcionesListado({ getElementById: id => campos[id] });
    campos.cerrarComanda.handlers.click();
    assert.match(campos.comandaMensaje.textContent, /No se pudo cargar/);
    assert.equal(campos.comandaMensajeContenedor.hidden, false);
});

test('totaliza importes filtrados por habitación al terminar cada grupo, incluso entre páginas', () => {
    const document = {
        createElement(tag) {
            return { tag, children: [], append(...hijos) { this.children.push(...hijos); } };
        },
    };
    const opciones = opcionesListado(document);
    const fila = (habitacion, importe) => ({
        cells: Array.from({ length: 9 }, (_, i) => ({ textContent: i === 2 ? habitacion : i === 8 ? importe : '' })),
        after(total) { this.total = total; },
    });
    const filas = [fila('10', '0.10'), fila('10', '0.20'), fila('10', '-0.01'), fila('20', '-4.00')];
    let pagina = filas.slice(0, 2);
    const api = { rows(selector) {
        const elementos = selector.page === 'current' ? pagina : filas;
        return { nodes: () => ({ toArray: () => elementos }) };
    } };
    opciones.drawCallback.call({ api: () => api });
    assert.equal(filas[1].total, undefined);
    pagina = filas.slice(2);
    opciones.drawCallback.call({ api: () => api });
    assert.equal(filas[2].total.children[0].textContent, 'Total habitación 10');
    assert.equal(filas[2].total.children[1].textContent, '0.29');
    assert.equal(filas[3].total.children[0].textContent, 'Total habitación 20');
    assert.equal(filas[3].total.children[1].textContent, '-4.00');
    filas.splice(0, 1);
    opciones.drawCallback.call({ api: () => api });
    assert.equal(filas[1].total.children[1].textContent, '0.19');
});

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
    let devolver;
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
        if (selector === '#devolverConsumo') return { on(evento, handler) { devolver = handler; } };
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
        devolver() { let prevenido = false; devolver({ preventDefault() { prevenido = true; } }); assert.ok(prevenido); },
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

test('agrega una devolución negativa a la lista temporal y la envía al grabar', async () => {
    const { campos, abrir, devolver } = entorno();
    abrir({ id: 8, nombre: 'Agua', precio: '2.35' });
    campos.consumoCantidad.value = '0.5';
    campos.consumoMoza.value = '12';
    devolver();
    const fila = campos.tablaConsumos.rows[0];
    assert.equal(fila.dataset.devolucion, '1');
    assert.equal(fila.cells[2].textContent, 'Agua (Devolución)');
    assert.equal(fila.cells[4].textContent, '-0.5');
    assert.equal(fila.cells[5].textContent, '-1.18');
    let solicitud;
    const grabacion = entornoGrabacion(async (url, opciones) => {
        solicitud = JSON.parse(opciones.body);
        return { ok: true, status: 200, json: async () => ({ mensaje: 'Guardada.', correos_enviados: 0, advertencias: [] }) };
    });
    grabacion.tabla.rows = [fila];
    await grabacion.grabar.handlers.click();
    assert.equal(solicitud.detalles[0].devolucion, true);
    assert.equal(solicitud.detalles[0].cantidad, '0.5');
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
