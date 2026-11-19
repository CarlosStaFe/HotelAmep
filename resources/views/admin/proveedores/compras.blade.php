@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Proveedores</li>
            <li class="breadcrumb-item active" aria-current="page">Cargar Compras</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form id="formCompra" action="{{ route('compras.store') }}" method="POST">
                        @csrf
                        <input type="hidden" id="detalles_json" name="detalles_json" value="{{ old('detalles_json', '[]') }}">
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="proveedor_id" class="form-label">Proveedor <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="proveedor_id" name="proveedor_id" required>
                                    <option value="">Seleccione proveedor</option>
                                    @foreach ($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->nombre }} - {{ $proveedor->cuit }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedor_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="fechaCompra" class="form-label">Fecha <b style="color: red;">(*)</b></label>
                                <input type="date" class="form-control" id="fechaCompra" name="fechaCompra"
                                    placeholder="Ingrese fecha de la compra" value="{{ old('fechaCompra') }}" required>
                                @error('fechaCompra')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="tipoCpbte">Comprobante <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="tipoCpbte" name="tipoCpbte" required>
                                    <option value="" {{ old('tipoCpbte') ? '' : 'selected' }} disabled>Elige un tipo de comprobante...</option>
                                    <option value="Factura A" {{ old('tipoCpbte') == 'Factura A' ? 'selected' : '' }}>Factura A</option>
                                    <option value="Factura B" {{ old('tipoCpbte') == 'Factura B' ? 'selected' : '' }}>Factura B</option>
                                    <option value="Factura C" {{ old('tipoCpbte') == 'Factura C' ? 'selected' : '' }}>Factura C</option>
                                    <option value="Recibo" {{ old('tipoCpbte') == 'Recibo' ? 'selected' : '' }}>Recibo</option>
                                </select>
                                @error('tipoCpbte')
                                    <small style="color: red">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="prefijo" class="form-label">Prefijo<b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="prefijo" name="prefijo"
                                    placeholder="Ingrese C.U.I.T. del proveedor" value="{{ old('prefijo') }}" required>
                                @error('prefijo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="subfijo" class="form-label">Subfijo<b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="subfijo" name="subfijo"
                                    placeholder="Ingrese C.U.I.T. del proveedor" value="{{ old('subfijo') }}" required>
                                @error('subfijo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="estado" class="form-label">Estado <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="estado" name="estado" required>
                                    <option value="Pendiente" {{ old('estado', 'Pendiente') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="Pagada" {{ old('estado') === 'Pagada' ? 'selected' : '' }}>Pagada</option>
                                </select>
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="dias" class="form-label">Días Pago <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="dias" name="dias" min="0" step="1" required
                                    placeholder="Ingrese días del proveedor" value="{{ old('dias') }}">
                                @error('dias')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>                            
                            <div class="col-md-4 mb-3">
                                <label for="observacion" class="form-label">Observación</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="2"
                                    placeholder="Ingrese observación del artículo">{{ old('observacion') }}</textarea>
                            </div>
                        </div>
                        @php
                            $rubroSeleccionado = $rubros->firstWhere('id', old('rubro_id'));
                            $subrubrosDisponibles = $rubroSeleccionado ? $subrubros->where('rubro_id', $rubroSeleccionado->id) : collect();
                            $subrubroSeleccionado = $subrubrosDisponibles->firstWhere('id', old('subrubro_id'));
                            $articulosDisponibles = $subrubroSeleccionado ? $articulos->where('subrubro_id', $subrubroSeleccionado->id) : collect();
                        @endphp
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="rubro_id" class="form-label">Rubro <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="rubro_id" name="rubro_id">
                                    <option value="">Seleccione rubro</option>
                                    @foreach ($rubros as $rubro)
                                        <option value="{{ $rubro->id }}" {{ old('rubro_id') == $rubro->id ? 'selected' : '' }}>{{ $rubro->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="subrubro_id" class="form-label">Subrubro <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="subrubro_id" name="subrubro_id" {{ $subrubrosDisponibles->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Seleccione subrubro</option>
                                    @foreach ($subrubrosDisponibles as $subrubro)
                                        <option value="{{ $subrubro->id }}" {{ old('subrubro_id') == $subrubro->id ? 'selected' : '' }}>{{ $subrubro->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="articulo_id" class="form-label">Artículo <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="articulo_id" name="articulo_id" {{ $articulosDisponibles->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Seleccione artículo</option>
                                    @foreach ($articulosDisponibles as $articulo)
                                        <option value="{{ $articulo->id }}" {{ old('articulo_id') == $articulo->id ? 'selected' : '' }}>{{ $articulo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="precio" class="form-label">Precio Uni. <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="precio" name="precio" value="{{ old('precio') }}" min="0" max="9999999999.99" step="0.01">
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="cantidad" class="form-label">Cantidad <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" value="{{ old('cantidad') }}" min="1" max="999999999" step="1">
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="subtotal" class="form-label">Subtotal <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="subtotal" name="subtotal" step="0.01" aria-describedby="subtotalMensaje" readonly>
                                <small id="subtotalMensaje" class="text-danger" role="status"></small>
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="fechaVto" class="form-label">Fecha Vto.</label>
                                <input type="date" class="form-control" id="fechaVto" name="fechaVto"
                                    placeholder="Ingrese fecha de vencimiento" value="{{ old('fechaVto') }}">
                                @error('fechaVto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-1 d-flex">
                                <button type="button" id="cargarArticulo" class="btn btn-success">Cargar</button>
                            </div>
                        </div>
                        <div id="detalleMensaje" class="text-danger" role="alert"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-right">Nro.</th>
                                        <th>Artículo</th>
                                        <th class="text-right">Precio</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-right">Vencimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDetalles"></tbody>
                            </table>
                        </div>
                        <p>Total: <strong id="totalCompra">0.00</strong></p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ url('/admin/proveedores/index') }}"
                                        class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" id="guardarCompra" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rubro = document.getElementById('rubro_id');
            const subrubro = document.getElementById('subrubro_id');
            const articulo = document.getElementById('articulo_id');
            const subrubros = @json($subrubros);
            const articulos = @json($articulos);

            function cargarOpciones(select, opciones, parentId, parentKey, mensaje, seleccionado = '') {
                const disponibles = parentId
                    ? opciones.filter(opcion => String(opcion[parentKey]) === parentId)
                    : [];
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = mensaje;
                select.replaceChildren(placeholder);

                disponibles.forEach(opcion => {
                    const item = document.createElement('option');
                    item.value = String(opcion.id);
                    item.textContent = opcion.nombre;
                    select.appendChild(item);
                });
                select.value = disponibles.some(opcion => String(opcion.id) === seleccionado) ? seleccionado : '';
                select.disabled = !parentId || disponibles.length === 0;
            }

            function cargarSubrubros(seleccionado = '') {
                cargarOpciones(subrubro, subrubros, rubro.value, 'rubro_id', 'Seleccione subrubro', seleccionado);
            }

            function cargarArticulos(seleccionado = '') {
                cargarOpciones(articulo, articulos, subrubro.value, 'subrubro_id', 'Seleccione artículo', seleccionado);
            }

            rubro.addEventListener('change', function() {
                cargarSubrubros();
                cargarArticulos();
            });
            subrubro.addEventListener('change', function() {
                cargarArticulos();
            });

            const subrubroSeleccionado = subrubro.value;
            const articuloSeleccionado = articulo.value;
            cargarSubrubros(subrubroSeleccionado);
            cargarArticulos(articuloSeleccionado);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const precio = document.getElementById('precio');
            const cantidad = document.getElementById('cantidad');
            const subtotal = document.getElementById('subtotal');
            const mensaje = document.getElementById('subtotalMensaje');

            function calcularSubtotal() {
                subtotal.value = '';
                mensaje.textContent = '';
                if (precio.validity.badInput || cantidad.validity.badInput) {
                    mensaje.textContent = 'Ingrese un precio y una cantidad válidos.';
                    return;
                }
                if (precio.value === '' || cantidad.value === '') return;
                if (!precio.validity.valid || !cantidad.validity.valid) {
                    mensaje.textContent = 'Ingrese un precio y una cantidad válidos.';
                    return;
                }

                const centavos = Math.round(precio.valueAsNumber * 100);
                const unidades = cantidad.valueAsNumber;
                if (!Number.isSafeInteger(centavos) || !Number.isSafeInteger(unidades)) {
                    mensaje.textContent = 'El precio o la cantidad supera el máximo permitido.';
                    return;
                }
                const total = BigInt(centavos) * BigInt(unidades);
                const absoluto = total < 0n ? -total : total;
                if (absoluto > 999999999999999n) {
                    mensaje.textContent = 'El subtotal supera el máximo permitido.';
                    return;
                }
                subtotal.value = `${total < 0n ? '-' : ''}${absoluto / 100n}.${String(absoluto % 100n).padStart(2, '0')}`;
            }

            precio.addEventListener('input', calcularSubtotal);
            cantidad.addEventListener('input', calcularSubtotal);
            calcularSubtotal();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formCompra');
            const rubro = document.getElementById('rubro_id');
            const subrubro = document.getElementById('subrubro_id');
            const articulo = document.getElementById('articulo_id');
            const precio = document.getElementById('precio');
            const cantidad = document.getElementById('cantidad');
            const subtotal = document.getElementById('subtotal');
            const fechaVto = document.getElementById('fechaVto');
            const mensaje = document.getElementById('detalleMensaje');
            const lista = document.getElementById('tablaDetalles');
            const totalCompra = document.getElementById('totalCompra');
            const detallesInput = document.getElementById('detalles_json');
            const cargar = document.getElementById('cargarArticulo');
            const guardar = document.getElementById('guardarCompra');
            const articulos = @json($articulos);
            const nombres = new Map(articulos.map(item => [String(item.id), item.nombre]));
            const maximo = 999999999999999n;

            function centavos(precioUnitario) {
                const [entero, decimal = ''] = String(precioUnitario).split('.');
                return BigInt(entero) * 100n + BigInt(decimal.padEnd(2, '0'));
            }

            function importe(valor) {
                return `${valor / 100n}.${String(valor % 100n).padStart(2, '0')}`;
            }

            function detalleValido(item) {
                return item && typeof item === 'object'
                    && /^\d+$/.test(String(item.articulo_id))
                    && /^\d{1,10}(\.\d{1,2})?$/.test(String(item.precio))
                    && /^\d{1,9}$/.test(String(item.cantidad))
                    && Number(item.cantidad) > 0
                    && (item.vence == null || item.vence === '' || /^\d{4}-\d{2}-\d{2}$/.test(String(item.vence)));
            }

            let detalles;
            try {
                detalles = JSON.parse(detallesInput.value);
            } catch (error) {
                if (!(error instanceof SyntaxError)) throw error;
                mensaje.textContent = 'No se pudo recuperar la lista de artículos. Recargue la página.';
                cargar.disabled = true;
                return;
            }
            if (!Array.isArray(detalles) || !detalles.every(detalleValido)) {
                mensaje.textContent = 'La lista de artículos no es válida. Recargue la página.';
                cargar.disabled = true;
                return;
            }

            function total() {
                return detalles.reduce((suma, item) => suma + centavos(item.precio) * BigInt(item.cantidad), 0n);
            }

            function mostrar() {
                lista.replaceChildren();
                detalles.forEach((item, indice) => {
                    const fila = document.createElement('tr');
                    const vencimiento = item.vence ? item.vence.split('-').reverse().join('/') : '-';
                    const valores = [
                        String(indice + 1), nombres.get(String(item.articulo_id)) ?? 'Artículo no disponible',
                        importe(centavos(item.precio)), String(item.cantidad),
                        importe(centavos(item.precio) * BigInt(item.cantidad)), vencimiento,
                    ];
                    valores.forEach((valor, indiceColumna) => {
                        const celda = document.createElement('td');
                        if ([0, 2, 3, 4, 5].includes(indiceColumna)) celda.className = 'text-right';
                        celda.textContent = valor;
                        fila.appendChild(celda);
                    });
                    const acciones = document.createElement('td');
                    const quitar = document.createElement('button');
                    quitar.type = 'button';
                    quitar.className = 'btn btn-danger btn-sm';
                    quitar.textContent = 'Quitar';
                    quitar.addEventListener('click', function() {
                        detalles.splice(indice, 1);
                        mensaje.textContent = '';
                        mostrar();
                    });
                    acciones.appendChild(quitar);
                    fila.appendChild(acciones);
                    lista.appendChild(fila);
                });
                totalCompra.textContent = importe(total());
                detallesInput.value = JSON.stringify(detalles);
            }

            cargar.addEventListener('click', function() {
                mensaje.textContent = '';
                if (!rubro.value || !subrubro.value || !articulo.value || articulo.disabled || !nombres.has(articulo.value)) {
                    mensaje.textContent = 'Seleccione rubro, subrubro y artículo.';
                    return;
                }
                for (const campo of [precio, cantidad]) {
                    if (!campo.value || !campo.checkValidity()) {
                        mensaje.textContent = 'Ingrese un precio y una cantidad válidos.';
                        campo.reportValidity();
                        return;
                    }
                }
                if (!fechaVto.checkValidity()) {
                    mensaje.textContent = 'Ingrese una fecha de vencimiento válida.';
                    fechaVto.reportValidity();
                    return;
                }
                if (!/^\d{1,10}(\.\d{1,2})?$/.test(precio.value) || !subtotal.value) {
                    mensaje.textContent = 'Ingrese un precio válido con hasta dos decimales.';
                    return;
                }
                const detalle = {
                    articulo_id: articulo.value, precio: precio.value,
                    cantidad: String(cantidad.valueAsNumber), vence: fechaVto.value || null,
                };
                if (!detalleValido(detalle)) {
                    mensaje.textContent = 'Ingrese una cantidad entera y, si corresponde, un vencimiento válido.';
                    return;
                }
                if (total() + centavos(detalle.precio) * BigInt(detalle.cantidad) > maximo) {
                    mensaje.textContent = 'El total de la compra supera el máximo permitido.';
                    return;
                }
                detalles.push(detalle);
                mostrar();
                articulo.value = '';
                precio.value = '';
                cantidad.value = '';
                subtotal.value = '';
                document.getElementById('subtotalMensaje').textContent = '';
                fechaVto.value = '';
            });

            form.addEventListener('submit', function(evento) {
                if (detalles.length === 0 || total() > maximo) {
                    evento.preventDefault();
                    mensaje.textContent = detalles.length === 0
                        ? 'Cargue al menos un artículo antes de guardar.'
                        : 'El total de la compra supera el máximo permitido.';
                } else {
                    guardar.disabled = true;
                }
            });

            mostrar();
        });
    </script>
@stop
