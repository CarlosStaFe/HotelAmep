import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/calendarios/asignadas.blade.php', import.meta.url), 'utf8');
const inicio = vista.indexOf('    <script>', vista.indexOf("@section('js')"));
const fin = vista.indexOf('    </script>', inicio);
assert.ok(inicio !== -1 && fin !== -1);
let opciones;
const contexto = vm.createContext({
    $(selector) {
        if (typeof selector === 'function') {
            selector();
            return;
        }
        assert.equal(selector, '#example1');
        return {
            DataTable(config) {
                opciones = config;
                return { buttons: () => ({ container: () => ({ appendTo() {} }) }) };
            },
        };
    },
});
vm.runInContext(vista.slice(inicio + '    <script>'.length, fin), contexto);

function fila(checkin, checkout, habitacion = '1') {
    const fechas = {
        '.fecha-checkin': { textContent: checkin, style: {} },
        '.fecha-checkout': { textContent: checkout, style: {} },
        '.numero-habitacion': { textContent: habitacion, dataset: { habitacionId: habitacion }, style: {} },
    };
    return { fechas, querySelector(selector) { return fechas[selector]; } };
}

function dibujar(filas) {
    opciones.drawCallback.call({
        api: () => ({
            rows(selector) {
                assert.equal(selector.page, 'current');
                return { nodes: () => ({ each(callback) { filas.forEach(callback); } }) };
            },
        }),
    });
}

test('oculta la habitación repetida y la recupera al cambiar el orden o página', () => {
    const primera = fila('10-11-2026', '12-11-2026', '1');
    const segunda = fila('10-11-2026', '12-11-2026', '1');
    const tercera = fila('11-11-2026', '13-11-2026', '2');
    const cuarta = fila('11-11-2026', '13-11-2026', '1');

    dibujar([primera, segunda, tercera, cuarta]);
    assert.deepEqual([primera, segunda, tercera, cuarta].map(f => f.fechas['.numero-habitacion'].style.visibility), ['', 'hidden', '', '']);
    dibujar([segunda, primera]);
    assert.equal(segunda.fechas['.numero-habitacion'].style.visibility, '');
    assert.equal(primera.fechas['.numero-habitacion'].style.visibility, 'hidden');
    dibujar([primera]);
    assert.equal(primera.fechas['.numero-habitacion'].style.visibility, '');
    assert.equal(primera.fechas['.numero-habitacion'].textContent, '1');
});

test('muestra la habitación repetida cuando se muestran ambas fechas', () => {
    const filas = [
        fila('10-11-2026', '12-11-2026', '1'),
        fila('11-11-2026', '13-11-2026', '1'),
        fila('11-11-2026', '14-11-2026', '1'),
        fila('12-11-2026', '14-11-2026', '1'),
        fila('12-11-2026', '14-11-2026', '1'),
    ];

    dibujar(filas);
    assert.deepEqual(filas.map(f => f.fechas['.numero-habitacion'].style.visibility), ['', '', 'hidden', 'hidden', 'hidden']);
    assert.deepEqual(filas.map(f => f.fechas['.fecha-checkin'].style.visibility), ['', '', 'hidden', '', 'hidden']);
    assert.deepEqual(filas.map(f => f.fechas['.fecha-checkout'].style.visibility), ['', '', '', 'hidden', 'hidden']);
});

test('oculta cada fecha solo si coincide con la fila anterior', () => {
    const filas = [
        fila('10-11-2026', '12-11-2026'),
        fila('10-11-2026', '13-11-2026'),
        fila('11-11-2026', '13-11-2026'),
        fila('10-11-2026', '12-11-2026'),
    ];
    dibujar(filas);
    assert.deepEqual(filas.map(f => f.fechas['.fecha-checkin'].style.visibility), ['', 'hidden', '', '']);
    assert.deepEqual(filas.map(f => f.fechas['.fecha-checkout'].style.visibility), ['', '', 'hidden', '']);
});

test('recalcula al cambiar el orden, filtro o página sin perder los valores', () => {
    const primera = fila('10-11-2026', '12-11-2026');
    const segunda = fila('10-11-2026', '12-11-2026');
    dibujar([primera, segunda]);
    dibujar([segunda, primera]);
    assert.equal(segunda.fechas['.fecha-checkin'].style.visibility, '');
    assert.equal(segunda.fechas['.fecha-checkout'].style.visibility, '');
    assert.equal(primera.fechas['.fecha-checkin'].style.visibility, 'hidden');
    assert.equal(primera.fechas['.fecha-checkout'].style.visibility, 'hidden');
    dibujar([primera]);
    assert.equal(primera.fechas['.fecha-checkin'].style.visibility, '');
    assert.equal(primera.fechas['.fecha-checkout'].style.visibility, '');
    assert.equal(primera.fechas['.fecha-checkin'].textContent, '10-11-2026');
    assert.equal(primera.fechas['.fecha-checkout'].textContent, '12-11-2026');
    dibujar([]);
});
