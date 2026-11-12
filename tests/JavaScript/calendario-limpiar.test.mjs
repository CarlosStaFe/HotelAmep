import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/calendarios/calendario.blade.php', import.meta.url), 'utf8');
const inicio = vista.indexOf('        function actualizarSelectHabitaciones() {');
const fin = vista.indexOf('        function filtrarCalendario() {', inicio);
assert.ok(inicio !== -1 && fin !== -1);

test('Limpiar restablece los filtros, el calendario y las habitaciones del modal', () => {
    assert.match(vista, /onclick="limpiarFiltros\(\)"/);
    const campos = {
        fecha_inicio: { value: '2026-01-01' },
        fecha_fin: { value: '2026-01-31' },
        estado: { selectedIndex: 2 },
        pasajeros: { selectedIndex: 3 },
        ocupacion_habitacion_id: {
            value: '10',
            options: [],
            replaceChildren(opcion) { this.options = [opcion]; this.value = opcion.value; },
            add(opcion) { this.options.push(opcion); },
        },
        pasajerosModalBody: {
            rows: [{ dataset: { pasajeroId: '1' } }],
            replaceChildren() { this.rows = []; },
        },
        pasajerosModalMensaje: { textContent: '' },
    };
    const fuentes = [{ removed: false, remove() { this.removed = true; } }];
    const calendario = {
        eventos: [{ id: 1 }],
        getEventSources() { return fuentes; },
        removeAllEvents() { this.eventos = []; },
        gotoDate(fecha) { this.fecha = fecha; },
    };
    const contexto = vm.createContext({
        document: { getElementById(id) { return campos[id] ?? null; } },
        Option: function (texto, value) { this.text = texto; this.value = value; },
        calendario,
        habitacionesFiltradasActuales: [{ id: 10, numero: 1 }],
        habitacionAnterior: '',
        habitacionSeleccionadaModal: '10',
    });
    vm.runInContext(vista.slice(inicio, fin), contexto);

    contexto.limpiarFiltros();

    assert.equal(campos.estado.selectedIndex, 0);
    assert.equal(campos.pasajeros.selectedIndex, 0);
    assert.match(campos.fecha_inicio.value, /^\d{4}-\d{2}-\d{2}$/);
    assert.match(campos.fecha_fin.value, /^\d{4}-\d{2}-\d{2}$/);
    assert.equal(calendario.fecha, campos.fecha_inicio.value);
    assert.equal(fuentes[0].removed, true);
    assert.equal(calendario.eventos.length, 0);
    assert.equal(contexto.habitacionesFiltradasActuales.length, 0);
    assert.equal(campos.ocupacion_habitacion_id.value, '');
    assert.equal(campos.ocupacion_habitacion_id.options.length, 1);
    assert.equal(campos.pasajerosModalBody.rows.length, 0);
});

test('Buscar mantiene todos los estados y pinta de blanco los distintos del elegido', () => {
    const inicioUtilidades = vista.indexOf('        const normalizar =');
    const finUtilidades = vista.indexOf('        function agregarPasajeroModal()', inicioUtilidades);
    const inicioBuscar = vista.indexOf('        function filtrarCalendario() {');
    const finBuscar = vista.indexOf('    </script>', inicioBuscar);
    assert.ok(inicioUtilidades !== -1 && finUtilidades !== -1 && inicioBuscar !== -1 && finBuscar !== -1);
    const mensaje = { textContent: '', classList: { add() {}, remove() {} } };
    const campos = {
        fecha_inicio: { value: '2026-11-10' },
        fecha_fin: { value: '2026-11-11' },
        estado: { value: 'Reservada' },
        pasajeros: { value: '2' },
        resultadoBusqueda: mensaje,
    };
    const eventos = [
        { id: 1, habitacion_id: 1, habitacion: { plazas: 2 }, start: '2026-11-10', end: '2026-11-10', estado: 'Reservada', color: '#AAAA00', textColor: '#000000' },
        { id: 2, habitacion_id: 1, habitacion: { plazas: 2 }, start: '2026-11-11', end: '2026-11-11', estado: 'Disponible', color: '#00AA00', textColor: '#000000' },
        { id: 3, habitacion_id: 2, habitacion: { plazas: 3 }, start: '2026-11-10', estado: 'Reservada', color: '#AAAA00' },
        { id: 4, habitacion_id: 1, habitacion: { plazas: 2 }, start: '2026-11-12', estado: 'Reservada', color: '#AAAA00' },
    ];
    const fuentes = [];
    const calendario = {
        getEventSources() { return fuentes.slice(); },
        removeAllEvents() {},
        addEventSource(items) {
            const fuente = { items, remove() { fuentes.splice(fuentes.indexOf(this), 1); } };
            fuentes.push(fuente);
        },
        gotoDate(fecha) { this.fecha = fecha; },
    };
    const contexto = vm.createContext({
        document: { getElementById(id) { return campos[id] ?? null; } },
        eventosCalendario: eventos,
        habitaciones: [{ id: 1 }, { id: 2 }],
        habitacionesFiltradasActuales: [],
        actualizarSelectHabitaciones() {},
        calendario,
    });
    vm.runInContext(vista.slice(inicioUtilidades, finUtilidades) + vista.slice(inicioBuscar, finBuscar), contexto);

    contexto.filtrarCalendario();
    assert.equal(fuentes.length, 1);
    assert.equal(fuentes[0].items.length, 2);
    assert.equal(fuentes[0].items[0].color, '#AAAA00');
    assert.equal(fuentes[0].items[0].textColor, '#000000');
    assert.equal(fuentes[0].items[1].color, '#FFFFFF');
    assert.equal(fuentes[0].items[1].backgroundColor, '#FFFFFF');
    assert.equal(fuentes[0].items[1].borderColor, '#FFFFFF');
    assert.equal(fuentes[0].items[1].textColor, '#FFFFFF');
    assert.equal(eventos[0].color, '#AAAA00');
    assert.equal(eventos[0].textColor, '#000000');
    assert.equal(contexto.habitacionesFiltradasActuales.length, 1);
    assert.equal(calendario.fecha, '2026-11-10');
    assert.match(mensaje.textContent, /1 habitación/);

    campos.estado.value = 'Disponible';
    contexto.filtrarCalendario();
    assert.equal(fuentes.length, 1);
    assert.equal(fuentes[0].items[0].color, '#FFFFFF');
    assert.equal(fuentes[0].items[0].textColor, '#FFFFFF');
    assert.equal(fuentes[0].items[1].color, '#00AA00');
    assert.equal(fuentes[0].items[1].textColor, '#000000');
    campos.estado.value = 'Todas';
    contexto.filtrarCalendario();
    assert.equal(fuentes.length, 1);
    assert.equal(fuentes[0].items[0].color, '#AAAA00');
    assert.equal(fuentes[0].items[1].color, '#00AA00');

    campos.fecha_fin.value = '2026-11-01';
    contexto.filtrarCalendario();
    assert.match(mensaje.textContent, /rango de fechas válido/);
});
