import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/proveedores/compras.blade.php', import.meta.url), 'utf8');
const inicio = vista.indexOf("        document.addEventListener('DOMContentLoaded'");
const fin = vista.indexOf('    </script>', inicio);
assert.ok(inicio !== -1 && fin !== -1);
const subrubros = [
    { id: 11, rubro_id: 1, nombre: 'Bebidas' },
    { id: 12, rubro_id: 1, nombre: 'Comidas' },
    { id: 21, rubro_id: 2, nombre: 'Limpieza' },
];
const articulos = [
    { id: 111, subrubro_id: 11, nombre: '<Agua>' },
    { id: 112, subrubro_id: 11, nombre: 'Jugo' },
    { id: 121, subrubro_id: 12, nombre: 'Pan' },
    { id: 211, subrubro_id: 21, nombre: 'Jabón' },
];
const script = vista.slice(inicio, fin)
    .replace('@json($subrubros)', JSON.stringify(subrubros))
    .replace('@json($articulos)', JSON.stringify(articulos));

function entorno(valores = {}) {
    function select(id) {
        return {
            value: valores[id] || '', disabled: false, children: [], handlers: {},
            addEventListener(evento, handler) { this.handlers[evento] = handler; },
            replaceChildren(...children) { this.children = children; this.value = ''; },
            appendChild(child) { this.children.push(child); },
            cambiar(value) { this.value = value; this.handlers.change(); },
        };
    }
    const campos = {
        rubro_id: select('rubro_id'), subrubro_id: select('subrubro_id'), articulo_id: select('articulo_id'),
    };
    const document = {
        getElementById(id) { return campos[id]; },
        createElement() { return { value: '', textContent: '' }; },
        addEventListener(evento, handler) { if (evento === 'DOMContentLoaded') handler(); },
    };
    vm.runInNewContext(script, { document });
    return campos;
}

test('filtra los selectores y limpia selecciones dependientes al cambiar de rubro', () => {
    const { rubro_id: rubro, subrubro_id: subrubro, articulo_id: articulo } = entorno();
    assert.equal(subrubro.disabled, true);
    assert.equal(articulo.disabled, true);
    rubro.cambiar('1');
    assert.deepEqual(subrubro.children.map(item => item.value), ['', '11', '12']);
    assert.equal(subrubro.disabled, false);
    subrubro.cambiar('11');
    assert.deepEqual(articulo.children.map(item => item.value), ['', '111', '112']);
    assert.equal(articulo.children[1].textContent, '<Agua>');
    articulo.value = '111';
    rubro.cambiar('2');
    assert.deepEqual(subrubro.children.map(item => item.value), ['', '21']);
    assert.equal(subrubro.value, '');
    assert.equal(articulo.value, '');
    assert.equal(articulo.disabled, true);
    subrubro.cambiar('21');
    assert.deepEqual(articulo.children.map(item => item.value), ['', '211']);
    rubro.cambiar('');
    assert.equal(subrubro.disabled, true);
    assert.equal(articulo.disabled, true);
});

test('restaura solo selecciones válidas y deshabilita listas vacías', () => {
    const validos = entorno({ rubro_id: '1', subrubro_id: '11', articulo_id: '112' });
    assert.equal(validos.subrubro_id.value, '11');
    assert.equal(validos.articulo_id.value, '112');
    const invalidos = entorno({ rubro_id: '1', subrubro_id: '21', articulo_id: '211' });
    assert.equal(invalidos.subrubro_id.value, '');
    assert.equal(invalidos.articulo_id.value, '');
    assert.equal(invalidos.articulo_id.disabled, true);
    invalidos.rubro_id.cambiar('3');
    assert.equal(invalidos.subrubro_id.disabled, true);
    assert.equal(invalidos.articulo_id.disabled, true);
});

test('calcula el subtotal al cambiar el precio o la cantidad y rechaza valores inválidos', () => {
    const inicioSubtotal = vista.indexOf("        document.addEventListener('DOMContentLoaded'", fin);
    const finSubtotal = vista.indexOf('    </script>', inicioSubtotal);
    assert.ok(inicioSubtotal !== -1 && finSubtotal !== -1);
    assert.match(vista, /id="subtotal"[^>]*readonly/);
    function campo(value = '') {
        return {
            value, textContent: '', validity: { valid: true, badInput: false }, handlers: {},
            get valueAsNumber() { return this.value === '' ? NaN : Number(this.value); },
            addEventListener(evento, handler) { this.handlers[evento] = handler; },
            cambiar(nuevo) { this.value = nuevo; this.handlers.input(); },
        };
    }
    const campos = {
        precio: campo('2.55'), cantidad: campo('3'), subtotal: campo(), subtotalMensaje: campo(),
    };
    const document = {
        getElementById(id) { return campos[id]; },
        addEventListener(evento, handler) { if (evento === 'DOMContentLoaded') handler(); },
    };
    vm.runInNewContext(vista.slice(inicioSubtotal, finSubtotal), { document });
    assert.equal(campos.subtotal.value, '7.65');
    campos.cantidad.cambiar('2');
    assert.equal(campos.subtotal.value, '5.10');
    campos.precio.cambiar('0.29');
    campos.cantidad.cambiar('100');
    assert.equal(campos.subtotal.value, '29.00');
    campos.precio.cambiar('9999999999999.99');
    campos.cantidad.cambiar('1');
    assert.equal(campos.subtotal.value, '9999999999999.99');
    campos.cantidad.cambiar('2');
    assert.equal(campos.subtotal.value, '');
    assert.match(campos.subtotalMensaje.textContent, /subtotal supera/);
    campos.precio.cambiar('');
    assert.equal(campos.subtotal.value, '');
    assert.equal(campos.subtotalMensaje.textContent, '');
    campos.precio.validity.valid = false;
    campos.precio.cambiar('1.001');
    assert.equal(campos.subtotal.value, '');
    assert.match(campos.subtotalMensaje.textContent, /válidos/);
});

function entornoCarga(detallesPrevios = []) {
    const segundo = vista.indexOf("        document.addEventListener('DOMContentLoaded'", fin);
    const finSegundo = vista.indexOf('    </script>', segundo);
    const inicioCarga = vista.indexOf("        document.addEventListener('DOMContentLoaded'", finSegundo);
    const finCarga = vista.indexOf('    </script>', inicioCarga);
    assert.ok(inicioCarga !== -1 && finCarga !== -1);
    const scriptCarga = vista.slice(inicioCarga, finCarga).replace('@json($articulos)', JSON.stringify(articulos));
    function elemento(value = '') {
        return {
            value, textContent: '', children: [], handlers: {}, disabled: false,
            get valueAsNumber() { return Number(this.value); },
            addEventListener(evento, handler) { this.handlers[evento] = handler; },
            appendChild(child) { this.children.push(child); },
            replaceChildren(...children) { this.children = children; },
            checkValidity() { return true; },
            reportValidity() {},
        };
    }
    const campos = {
        formCompra: elemento(), rubro_id: elemento(), subrubro_id: elemento(), articulo_id: elemento(),
        precio: elemento(), cantidad: elemento(), subtotal: elemento(), fechaVto: elemento(),
        detalleMensaje: elemento(), tablaDetalles: elemento(), totalCompra: elemento(), subtotalMensaje: elemento(),
        detalles_json: elemento(JSON.stringify(detallesPrevios)), cargarArticulo: elemento(), guardarCompra: elemento(),
    };
    const document = {
        getElementById(id) { return campos[id]; },
        createElement() { return elemento(); },
        addEventListener(evento, handler) { if (evento === 'DOMContentLoaded') handler(); },
    };
    vm.runInNewContext(scriptCarga, { document });
    return campos;
}

test('carga y quita artículos temporales y envía la lista al guardar', () => {
    const campos = entornoCarga();
    assert.match(vista, /type="button" id="cargarArticulo"/);
    let prevenido = false;
    campos.formCompra.handlers.submit({ preventDefault() { prevenido = true; } });
    assert.equal(prevenido, true);
    assert.match(campos.detalleMensaje.textContent, /al menos un artículo/);
    campos.cargarArticulo.handlers.click();
    assert.match(campos.detalleMensaje.textContent, /Seleccione rubro/);

    campos.rubro_id.value = '1';
    campos.subrubro_id.value = '11';
    campos.articulo_id.value = '111';
    campos.precio.value = '2.55';
    campos.cantidad.value = '3';
    campos.subtotal.value = '7.65';
    campos.fechaVto.value = '2027-01-01';
    campos.cargarArticulo.handlers.click();
    assert.equal(campos.totalCompra.textContent, '7.65');
    assert.equal(campos.tablaDetalles.children[0].children[1].textContent, '<Agua>');
    assert.equal(campos.tablaDetalles.children[0].children[5].textContent, '01/01/2027');
    for (const indice of [0, 2, 3, 4, 5]) {
        assert.equal(campos.tablaDetalles.children[0].children[indice].className, 'text-right');
    }
    assert.notEqual(campos.tablaDetalles.children[0].children[1].className, 'text-right');
    for (const titulo of ['Nro.', 'Precio', 'Cantidad', 'Subtotal', 'Vencimiento']) {
        assert.ok(vista.includes(`<th class="text-right">${titulo}</th>`));
    }
    assert.deepEqual(JSON.parse(campos.detalles_json.value), [
        { articulo_id: '111', precio: '2.55', cantidad: '3', vence: '2027-01-01' },
    ]);
    assert.equal(campos.precio.value, '');
    assert.equal(campos.rubro_id.value, '1');

    campos.articulo_id.value = '112';
    campos.precio.value = '1.00';
    campos.cantidad.value = '2';
    campos.subtotal.value = '2.00';
    campos.fechaVto.value = '2027-02-01';
    campos.cargarArticulo.handlers.click();
    assert.equal(campos.totalCompra.textContent, '9.65');
    campos.tablaDetalles.children[0].children[6].children[0].handlers.click();
    assert.equal(campos.totalCompra.textContent, '2.00');
    assert.equal(JSON.parse(campos.detalles_json.value)[0].articulo_id, '112');
    prevenido = false;
    campos.formCompra.handlers.submit({ preventDefault() { prevenido = true; } });
    assert.equal(prevenido, false);
    assert.equal(campos.guardarCompra.disabled, true);
});

test('recupera artículos temporales después de un error del formulario', () => {
    const detalles = [{ articulo_id: '111', precio: '0.29', cantidad: '100', vence: '2027-03-15' }];
    const campos = entornoCarga(detalles);
    assert.equal(campos.totalCompra.textContent, '29.00');
    assert.equal(campos.tablaDetalles.children[0].children[1].textContent, '<Agua>');
    assert.equal(campos.tablaDetalles.children[0].children[5].textContent, '15/03/2027');
    assert.deepEqual(JSON.parse(campos.detalles_json.value), detalles);
});

test('carga y recupera artículos sin fecha de vencimiento', () => {
    const campos = entornoCarga();
    campos.rubro_id.value = '1';
    campos.subrubro_id.value = '11';
    campos.articulo_id.value = '111';
    campos.precio.value = '2.55';
    campos.cantidad.value = '3';
    campos.subtotal.value = '7.65';
    campos.fechaVto.value = '';
    campos.cargarArticulo.handlers.click();
    const detalles = JSON.parse(campos.detalles_json.value);
    assert.equal(detalles[0].vence, null);
    assert.equal(campos.tablaDetalles.children[0].children[5].textContent, '-');
    const recuperados = entornoCarga([
        ...detalles,
        { articulo_id: '112', precio: '1.00', cantidad: '1', vence: '' },
        { articulo_id: '111', precio: '1.00', cantidad: '1' },
    ]);
    assert.equal(recuperados.tablaDetalles.children.length, 3);
    for (const fila of recuperados.tablaDetalles.children) {
        assert.equal(fila.children[5].textContent, '-');
    }
    assert.equal(recuperados.totalCompra.textContent, '9.65');
});

test('rechaza un vencimiento informado inválido', () => {
    const campos = entornoCarga();
    campos.rubro_id.value = '1';
    campos.subrubro_id.value = '11';
    campos.articulo_id.value = '111';
    campos.precio.value = '2.55';
    campos.cantidad.value = '3';
    campos.subtotal.value = '7.65';
    campos.fechaVto.value = '2027-99-99';
    campos.fechaVto.checkValidity = () => false;
    campos.cargarArticulo.handlers.click();
    assert.match(campos.detalleMensaje.textContent, /fecha de vencimiento válida/);
    assert.deepEqual(JSON.parse(campos.detalles_json.value), []);
});