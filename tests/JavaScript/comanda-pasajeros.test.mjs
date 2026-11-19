import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/comandas/comanda.blade.php', import.meta.url), 'utf8');
const inicioPasajeros = vista.indexOf("            const pasajeroSelect = document.getElementById('pasajeros');");
const inicio = vista.lastIndexOf("        document.addEventListener('DOMContentLoaded', function() {", inicioPasajeros);
const fin = vista.indexOf('    </script>', inicio);
assert.ok(inicio !== -1 && fin !== -1);
const script = vista.slice(inicio, fin)
    .replace("@json(old('habitacion'))", 'null')
    .replace("@json(old('pasajeros'))", 'null')
    .replace("{{ route('pasajeros.habitacion', ['habitacion' => '__HAB__']) }}", '/pasajeros/habitacion/__HAB__');

function entorno(fetch) {
    let inicioPagina;
    const habitacion = {
        value: '', handlers: {},
        addEventListener(evento, callback) { this.handlers[evento] = callback; },
        cambiar(valor) { this.value = valor; this.handlers.change.call(this); },
    };
    const pasajeros = {
        options: [], disabled: false,
        set innerHTML(valor) { this.options = []; this.placeholder = valor; },
        appendChild(opcion) { this.options.push(opcion); },
    };
    const mensaje = { textContent: '' };
    const elementos = { habitacion, pasajeros, pasajerosMensaje: mensaje };
    const errores = [];
    vm.runInNewContext(script, {
        document: {
            addEventListener(evento, callback) { inicioPagina = callback; },
            getElementById(id) { return elementos[id]; },
            createElement() { return {}; },
        },
        fetch, encodeURIComponent,
        console: { error(...args) { errores.push(args); } },
    });
    inicioPagina();
    return { habitacion, pasajeros, mensaje, errores };
}

const esperar = () => new Promise(resolve => setImmediate(resolve));

test('carga nombre y documento al elegir una habitación', async () => {
    const urls = [];
    const { habitacion, pasajeros, mensaje } = entorno(async url => {
        urls.push(url);
        return { ok: true, json: async () => [{ id: 8, nombre: 'Ana', documento: '00123' }] };
    });
    assert.equal(pasajeros.disabled, true);
    habitacion.cambiar('5');
    await esperar();
    assert.deepEqual(urls, ['/pasajeros/habitacion/5']);
    assert.equal(pasajeros.options[0].value, 8);
    assert.equal(pasajeros.options[0].textContent, 'Ana - 00123');
    assert.equal(pasajeros.disabled, false);
    assert.equal(mensaje.textContent, '');
    habitacion.cambiar('');
    assert.equal(pasajeros.options.length, 0);
    assert.equal(pasajeros.disabled, true);
});

test('ignora respuestas tardías e informa errores de carga', async () => {
    const pendientes = [];
    const { habitacion, pasajeros, mensaje, errores } = entorno(() => new Promise(resolve => pendientes.push(resolve)));
    habitacion.cambiar('1');
    habitacion.cambiar('2');
    pendientes[1]({ ok: true, json: async () => [{ id: 2, nombre: 'Nuevo', documento: '222' }] });
    await esperar();
    pendientes[0]({ ok: true, json: async () => [{ id: 1, nombre: 'Anterior', documento: '111' }] });
    await esperar();
    assert.equal(pasajeros.options.length, 1);
    assert.equal(pasajeros.options[0].textContent, 'Nuevo - 222');
    habitacion.cambiar('3');
    pendientes[2]({ ok: false, status: 500 });
    await esperar();
    assert.equal(pasajeros.options.length, 0);
    assert.equal(pasajeros.disabled, true);
    assert.match(mensaje.textContent, /No se pudieron cargar/);
    assert.equal(errores.length, 1);
});