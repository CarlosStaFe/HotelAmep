import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/calendarios/calendario.blade.php', import.meta.url), 'utf8');
const inicio = vista.indexOf('        function agregarPasajeroModal() {');
const fin = vista.indexOf("        document.addEventListener('DOMContentLoaded'", inicio);
assert.ok(inicio !== -1 && fin !== -1);
const funcion = vista.slice(inicio, fin);

function crearEntorno() {
    const crearElemento = () => ({
        dataset: {},
        children: [],
        textContent: '',
        appendChild(hijo) { this.children.push(hijo); hijo.parent = this; },
        replaceChildren() { this.children = []; },
        setAttribute(nombre, valor) { this[nombre] = valor; },
        addEventListener(nombre, callback) { this[nombre] = callback; },
        remove() { this.parent.children.splice(this.parent.children.indexOf(this), 1); },
    });
    const pasajero = { value: '', selectedIndex: 0, options: [{ textContent: '', dataset: {} }] };
    const comanda = { value: '' };
    const habitacion = { value: '', focused: false, focus() { this.focused = true; } };
    const fechaInicio = { value: '' };
    const fechaFin = { value: '' };
    const cuerpo = crearElemento();
    Object.defineProperty(cuerpo, 'rows', { get() { return this.children; } });
    const mensaje = { textContent: '' };
    const elementos = {
        ocupacion_pasajero_id: pasajero,
        ocupacion_comanda: comanda,
        ocupacion_habitacion_id: habitacion,
        ocupacion_fecha_inicio: fechaInicio,
        ocupacion_fecha_fin: fechaFin,
        pasajerosModalBody: cuerpo,
        pasajerosModalMensaje: mensaje,
    };
    const errores = [{ eliminado: false, remove() { this.eliminado = true; } }];
    const contexto = vm.createContext({
        habitacionAnterior: '1',
        habitacionSeleccionadaModal: '',
        habitaciones: [
            { id: 10, plazas: 3 },
            { id: 11, plazas: 1 },
            { id: 12, plazas: 0 },
            { id: 13, plazas: null },
        ],
        document: {
            getElementById(id) { return elementos[id]; },
            createElement: crearElemento,
            querySelectorAll(selector) {
                assert.equal(selector, '#ocupacionModal .ocupacion-error');
                return errores;
            },
        },
    });
    vm.runInContext(funcion, contexto);
    return {
        pasajero, comanda, habitacion, fechaInicio, fechaFin, cuerpo, mensaje, errores, contexto,
        agregar: contexto.agregarPasajeroModal,
        cambiarHabitacion: contexto.cambiarHabitacionModal,
        limpiar: contexto.limpiarFormularioOcupacion,
    };
}

test('agrega pasajeros con su comanda, impide duplicados y permite quitarlos', () => {
    const { pasajero, comanda, habitacion, cuerpo, mensaje, agregar } = crearEntorno();
    pasajero.value = '1';
    comanda.value = 'SI';
    agregar();
    assert.equal(mensaje.textContent, 'Seleccione una habitación.');
    assert.equal(habitacion.focused, true);
    assert.equal(cuerpo.rows.length, 0);

    habitacion.value = '10';
    pasajero.value = '';
    agregar();
    assert.match(mensaje.textContent, /Seleccione un pasajero/);
    assert.equal(cuerpo.rows.length, 0);

    pasajero.value = '1';
    pasajero.options[0].textContent = ' Ana - 123 ';
    comanda.value = 'SI';
    agregar();
    assert.equal(cuerpo.rows.length, 1);
    assert.equal(cuerpo.rows[0].children[0].textContent, 'Ana - 123');
    assert.equal(cuerpo.rows[0].children[1].textContent, 'SI');
    assert.equal(cuerpo.rows[0].children[1].children[0].name, 'pasajeros[p1][id]');
    assert.equal(cuerpo.rows[0].children[1].children[0].value, '1');
    assert.equal(cuerpo.rows[0].children[1].children[1].name, 'pasajeros[p1][comanda]');
    assert.equal(cuerpo.rows[0].children[1].children[1].value, 'SI');

    agregar();
    assert.match(mensaje.textContent, /ya está/);
    assert.equal(cuerpo.rows.length, 1);

    pasajero.value = '2';
    pasajero.options[0].textContent = 'Luis - 456';
    comanda.value = 'NO';
    agregar();
    assert.equal(cuerpo.rows.length, 2);
    assert.equal(cuerpo.rows[1].children[0].textContent, 'Luis - 456');
    assert.equal(cuerpo.rows[1].children[1].textContent, 'NO');
    cuerpo.rows[0].children[2].children[0].click();
    assert.equal(cuerpo.rows.length, 1);
    assert.equal(cuerpo.rows[0].dataset.pasajeroId, '2');
});

test('Reservar exige al menos un pasajero asignado', () => {
    const { contexto, pasajero, comanda, habitacion, mensaje, agregar, cuerpo } = crearEntorno();
    assert.equal(contexto.validarReservaModal(), false);
    assert.match(mensaje.textContent, /al menos un pasajero/);
    habitacion.value = '10';
    pasajero.value = '1';
    comanda.value = 'SI';
    agregar();
    assert.equal(contexto.validarReservaModal(), true);
    cuerpo.rows[0].children[2].children[0].click();
    assert.equal(contexto.validarReservaModal(), false);
});

test('Cancelar limpia el formulario, la lista temporal y los mensajes', () => {
    assert.match(vista, /data-dismiss="modal" onclick="limpiarFormularioOcupacion\(\)">Cancelar/);
    const { pasajero, comanda, habitacion, fechaInicio, fechaFin, cuerpo, mensaje, errores, contexto, agregar, limpiar } = crearEntorno();
    habitacion.value = '10';
    fechaInicio.value = '2026-11-06';
    fechaFin.value = '2026-11-07';
    pasajero.value = '1';
    pasajero.options[0].textContent = 'Ana - 123';
    comanda.value = 'SI';
    agregar();
    pasajero.value = '2';
    pasajero.options[0].textContent = 'Luis - 456';
    agregar();
    agregar();
    assert.equal(cuerpo.rows.length, 2);
    assert.match(mensaje.textContent, /ya está/);

    limpiar();
    assert.equal(cuerpo.rows.length, 0);
    assert.equal(mensaje.textContent, '');
    assert.equal(pasajero.value, '');
    assert.equal(comanda.value, '');
    assert.equal(habitacion.value, '');
    assert.equal(fechaInicio.value, '');
    assert.equal(fechaFin.value, '');
    assert.equal(contexto.habitacionAnterior, '');
    assert.equal(errores[0].eliminado, true);
    habitacion.value = '10';
    pasajero.value = '2';
    comanda.value = 'SI';
    agregar();
    assert.equal(cuerpo.rows.length, 1);
});

test('respeta las plazas, permite reutilizar un lugar y rechaza habitaciones con menor capacidad', () => {
    assert.match(vista, /onchange="cambiarHabitacionModal\(\)"/);
    const { pasajero, comanda, habitacion, cuerpo, mensaje, agregar, cambiarHabitacion } = crearEntorno();
    habitacion.value = '10';
    cambiarHabitacion();
    comanda.value = 'SI';
    for (let id = 1; id <= 3; id++) {
        pasajero.value = String(id);
        pasajero.options[0].textContent = `Pasajero ${id}`;
        agregar();
    }
    assert.equal(cuerpo.rows.length, 3);
    pasajero.value = '4';
    agregar();
    assert.equal(cuerpo.rows.length, 3);
    assert.match(mensaje.textContent, /3 plaza/);

    habitacion.value = '11';
    cambiarHabitacion();
    assert.equal(habitacion.value, '10');
    assert.match(mensaje.textContent, /suficientes plazas/);
    habitacion.value = '';
    cambiarHabitacion();
    assert.equal(habitacion.value, '10');

    cuerpo.rows[0].children[2].children[0].click();
    agregar();
    assert.equal(cuerpo.rows.length, 3);
    assert.equal(cuerpo.rows[2].dataset.pasajeroId, '4');
});

test('los menores de 5 años no consumen plazas', () => {
    const { pasajero, comanda, habitacion, cuerpo, mensaje, agregar, cambiarHabitacion } = crearEntorno();
    habitacion.value = '11';
    cambiarHabitacion();
    comanda.value = 'SI';
    pasajero.value = '1';
    pasajero.options[0].textContent = 'Adulto';
    agregar();
    pasajero.value = '2';
    pasajero.options[0].textContent = 'Bebé';
    pasajero.options[0].dataset.cuentaPax = '0';
    agregar();
    assert.equal(cuerpo.rows.length, 2);
    assert.match(cuerpo.rows[1].children[0].textContent, /menor de 5 años/);
    pasajero.value = '3';
    pasajero.options[0].dataset.cuentaPax = '1';
    agregar();
    assert.equal(cuerpo.rows.length, 2);
    assert.match(mensaje.textContent, /1 plaza/);
});

test('impide asignar pasajeros si no hay plazas o no se conoce la capacidad', () => {
    const { pasajero, comanda, habitacion, cuerpo, mensaje, agregar, cambiarHabitacion } = crearEntorno();
    pasajero.value = '1';
    comanda.value = 'SI';
    habitacion.value = '12';
    cambiarHabitacion();
    agregar();
    assert.equal(cuerpo.rows.length, 0);
    assert.match(mensaje.textContent, /0 plaza/);
    habitacion.value = '13';
    cambiarHabitacion();
    agregar();
    assert.equal(cuerpo.rows.length, 0);
    assert.match(mensaje.textContent, /No se pudo determinar/);
});
