import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const vista = readFileSync(new URL('../../resources/views/admin/articulos/ajuste.blade.php', import.meta.url), 'utf8');
const inicio = vista.indexOf("        document.addEventListener('DOMContentLoaded'");
const fin = vista.indexOf('    </script>', inicio);
assert.ok(inicio !== -1 && fin !== -1);

test('avisa al seleccionar un punto sin stock y oculta el aviso en los demás', () => {
    const punto = {
        options: [{ dataset: { sinStock: '1' } }, { dataset: { sinStock: '0' } }, { dataset: {} }],
        selectedIndex: 0,
        handlers: {},
        addEventListener(evento, handler) { this.handlers[evento] = handler; },
    };
    const aviso = { hidden: true };
    const document = {
        getElementById(id) { return id === 'punto' ? punto : aviso; },
        addEventListener(evento, handler) { if (evento === 'DOMContentLoaded') handler(); },
    };
    vm.runInNewContext(vista.slice(inicio, fin), { document });
    assert.equal(aviso.hidden, false);
    punto.selectedIndex = 1;
    punto.handlers.change();
    assert.equal(aviso.hidden, true);
    punto.selectedIndex = 2;
    punto.handlers.change();
    assert.equal(aviso.hidden, true);
    punto.selectedIndex = 0;
    punto.handlers.change();
    assert.equal(aviso.hidden, false);
});

test('en mover actualiza el aviso para el punto de origen', () => {
    const mover = readFileSync(new URL('../../resources/views/admin/articulos/mover.blade.php', import.meta.url), 'utf8');
    const inicio = mover.indexOf("        document.addEventListener('DOMContentLoaded'");
    const fin = mover.indexOf('    </script>', inicio);
    assert.ok(inicio !== -1 && fin !== -1);
    const punto = {
        options: [{ dataset: { sinStock: '1' } }, { dataset: { sinStock: '0' } }],
        selectedIndex: 0, handlers: {},
        addEventListener(evento, handler) { this.handlers[evento] = handler; },
    };
    const aviso = { hidden: true };
    const document = {
        getElementById(id) { return id === 'punto' ? punto : aviso; },
        addEventListener(evento, handler) { if (evento === 'DOMContentLoaded') handler(); },
    };
    vm.runInNewContext(mover.slice(inicio, fin), { document });
    assert.equal(aviso.hidden, false);
    punto.selectedIndex = 1;
    punto.handlers.change();
    assert.equal(aviso.hidden, true);
});

test('el contador del listado sigue el orden y el filtro de la tabla', () => {
    const listado = readFileSync(new URL('../../resources/views/admin/articulos/listajustes.blade.php', import.meta.url), 'utf8');
    const inicio = listado.indexOf('        $(function()');
    const fin = listado.indexOf('    </script>', inicio);
    assert.ok(inicio !== -1 && fin !== -1);
    let opciones;
    const $ = argumento => {
        if (typeof argumento === 'function') return argumento();
        return {
            DataTable(config) {
                opciones = config;
                return { buttons: () => ({ container: () => ({ appendTo() {} }) }) };
            },
        };
    };
    vm.runInNewContext(listado.slice(inicio, fin), { $ });
    const numeros = new Map();
    let filas = [7, 3, 10];
    const tabla = {
        rows() { return { every(callback) { filas.forEach((indice, posicion) => callback(indice, 0, posicion)); } }; },
        cell(indice) { return { data(numero) { numeros.set(indice, numero); } }; },
    };
    opciones.drawCallback.call({ api: () => tabla });
    assert.deepEqual(filas.map(indice => numeros.get(indice)), [1, 2, 3]);
    filas = [10, 7];
    opciones.drawCallback.call({ api: () => tabla });
    assert.deepEqual(filas.map(indice => numeros.get(indice)), [1, 2]);
});
