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
