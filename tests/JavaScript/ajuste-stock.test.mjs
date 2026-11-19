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
