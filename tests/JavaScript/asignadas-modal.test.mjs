import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/calendarios/asignadas.blade.php', import.meta.url), 'utf8');
const inicio = vista.indexOf('        function seleccionarComandaAsignacion()');
const fin = vista.indexOf('        $(function()', inicio);
assert.ok(inicio !== -1 && fin !== -1);

function entorno() {
    function elemento() {
        return {
            dataset: {}, children: [], textContent: '',
            appendChild(hijo) { this.children.push(hijo); hijo.parent = this; },
            replaceChildren() { this.children = []; },
            setAttribute(nombre, valor) { this[nombre] = valor; },
            addEventListener(nombre, callback) { this[nombre] = callback; },
            remove() { this.parent.children.splice(this.parent.children.indexOf(this), 1); },
        };
    }
    const pasajero = { value: '', selectedIndex: 0, options: [{ textContent: 'Ana - 111', dataset: { comanda: 'SI', cuentaPax: '1' } }] };
    const comanda = { value: '' };
    const reserva = {
        value: '', selectedIndex: 0, dataset: { anterior: '' }, focused: false,
        options: [{ dataset: { plazas: '3', ocupadas: '1' } }],
        focus() { this.focused = true; },
    };
    const cuerpo = elemento();
    Object.defineProperty(cuerpo, 'rows', { get() { return this.children; } });
    const mensaje = { textContent: '' };
    const elementos = { asignar_reserva: reserva, asignar_pasajero: pasajero, asignar_comanda: comanda, asignarPasajeros: cuerpo, asignarMensaje: mensaje };
    const contexto = vm.createContext({
        document: { getElementById(id) { return elementos[id]; }, createElement: elemento, querySelectorAll() { return []; } },
    });
    vm.runInContext(vista.slice(inicio, fin), contexto);
    return { reserva, pasajero, comanda, cuerpo, mensaje, contexto };
}

test('carga pasajeros con su comanda, rechaza duplicados y permite quitarlos', () => {
    const { reserva, pasajero, comanda, cuerpo, mensaje, contexto } = entorno();
    assert.equal(contexto.validarAsignacion(), false);
    reserva.value = '3';
    pasajero.value = '7';
    contexto.seleccionarComandaAsignacion();
    assert.equal(comanda.value, 'SI');
    comanda.value = 'NO';
    contexto.cargarPasajeroAsignacion();
    assert.equal(contexto.validarAsignacion(), true);
    assert.equal(cuerpo.rows[0].children[1].children[0].name, 'pasajeros[p7][id]');
    assert.equal(cuerpo.rows[0].children[1].children[1].value, 'NO');
    contexto.cargarPasajeroAsignacion();
    assert.match(mensaje.textContent, /ya está/);
    cuerpo.rows[0].children[2].children[0].click();
    assert.equal(contexto.validarAsignacion(), false);
});

test('cuenta los pasajeros existentes y nuevos, pero no los menores de cinco años', () => {
    const { reserva, pasajero, comanda, cuerpo, mensaje, contexto } = entorno();
    pasajero.value = '7';
    comanda.value = 'SI';
    contexto.cargarPasajeroAsignacion();
    assert.match(mensaje.textContent, /Seleccione una reserva/);
    assert.equal(reserva.focused, true);
    reserva.value = '3';
    contexto.cambiarReservaAsignacion();
    contexto.cargarPasajeroAsignacion();
    pasajero.value = '8';
    contexto.cargarPasajeroAsignacion();
    pasajero.value = '9';
    contexto.cargarPasajeroAsignacion();
    assert.equal(cuerpo.rows.length, 2);
    assert.match(mensaje.textContent, /3 plaza/);
    pasajero.options[0].dataset.cuentaPax = '0';
    contexto.cargarPasajeroAsignacion();
    assert.equal(cuerpo.rows.length, 3);
    assert.match(cuerpo.rows[2].children[0].textContent, /menor de 5 años/);
    cuerpo.rows[0].children[2].children[0].click();
    pasajero.value = '10';
    pasajero.options[0].dataset.cuentaPax = '1';
    contexto.cargarPasajeroAsignacion();
    assert.equal(cuerpo.rows.length, 3);
});

test('rechaza reservas sin capacidad o con plazas insuficientes al cambiar de reserva', () => {
    const { reserva, pasajero, comanda, cuerpo, mensaje, contexto } = entorno();
    reserva.value = '3';
    contexto.cambiarReservaAsignacion();
    pasajero.value = '7';
    comanda.value = 'SI';
    contexto.cargarPasajeroAsignacion();
    reserva.value = '4';
    reserva.options[0].dataset = { plazas: '1', ocupadas: '1' };
    contexto.cambiarReservaAsignacion();
    assert.equal(reserva.value, '3');
    assert.match(mensaje.textContent, /suficientes plazas/);
    assert.equal(cuerpo.rows.length, 1);
    reserva.options[0].dataset.plazas = '';
    pasajero.value = '8';
    contexto.cargarPasajeroAsignacion();
    assert.match(mensaje.textContent, /No se pudo determinar/);
    assert.equal(contexto.validarAsignacion(), false);
});

test('restaura los pasajeros luego de un error aun si la capacidad cambió', () => {
    const { reserva, pasajero, comanda, cuerpo, contexto } = entorno();
    reserva.value = '3';
    reserva.options[0].dataset = { plazas: '1', ocupadas: '1' };
    pasajero.value = '7';
    comanda.value = 'SI';
    contexto.cargarPasajeroAsignacion(true);
    assert.equal(cuerpo.rows.length, 1);
    assert.equal(contexto.validarAsignacion(), false);
});

test('limpia la lista y los campos al cerrar el modal', () => {
    const { reserva, pasajero, comanda, cuerpo, mensaje, contexto } = entorno();
    reserva.value = '3';
    pasajero.value = '7';
    comanda.value = 'SI';
    contexto.cargarPasajeroAsignacion();
    contexto.limpiarFormularioAsignacion();
    assert.equal(cuerpo.rows.length, 0);
    assert.equal(reserva.value, '');
    assert.equal(pasajero.value, '');
    assert.equal(comanda.value, '');
    assert.equal(mensaje.textContent, '');
});
