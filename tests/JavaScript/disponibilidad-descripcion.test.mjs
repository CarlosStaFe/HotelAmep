import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/calendarios/disponibilidad.blade.php', import.meta.url), 'utf8');
const inicio = vista.indexOf('        const estado = document.getElementById');
const fin = vista.indexOf('    </script>', inicio);
assert.ok(inicio !== -1 && fin !== -1);

test('la descripción solo es visible y obligatoria para No Disponible', () => {
    let cambio;
    const campos = {
        estado: { value: 'Disponible', addEventListener(evento, callback) { assert.equal(evento, 'change'); cambio = callback; } },
        descripcion: { value: 'Mantenimiento' },
        'descripcion-grupo': {},
    };
    vm.runInNewContext(vista.slice(inicio, fin), {
        document: { getElementById(id) { return campos[id]; } },
    });

    assert.equal(campos['descripcion-grupo'].hidden, true);
    assert.equal(campos.descripcion.required, false);
    assert.equal(campos.descripcion.disabled, true);

    campos.estado.value = 'No Disponible';
    cambio();
    assert.equal(campos['descripcion-grupo'].hidden, false);
    assert.equal(campos.descripcion.required, true);
    assert.equal(campos.descripcion.disabled, false);

    campos.estado.value = 'Ocupada';
    cambio();
    assert.equal(campos['descripcion-grupo'].hidden, true);
    assert.equal(campos.descripcion.required, false);
    assert.equal(campos.descripcion.disabled, true);
    assert.equal(campos.descripcion.value, 'Mantenimiento');
});
