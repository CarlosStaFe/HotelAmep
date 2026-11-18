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
        $, document: { getElementById(id) { return campos[id]; }, createElement: elemento },
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
    assert.match(config.columns[5].render(), /class="btn btn-success cargar-consumo"/);
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
    assert.deepEqual(fila.cells.slice(0, 8).map(celda => celda.textContent), [
        '1', '8', '<Agua>', '2.35', '0.1', '0.24', 'Ana - 123', '12',
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
