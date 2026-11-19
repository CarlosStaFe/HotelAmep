@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Comandas</li>
            <li class="breadcrumb-item active" aria-current="page">Cargar Consumo</li>
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
                <div class="row">
                    <div class="col-md-1 ml-1">
                        <label for="habitacion" class="form-label">Habitación <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="habitacion" name="habitacion" required>
                            <option value="">Seleccione</option>
                            @foreach ($habitaciones as $habitacion)
                                <option value="{{ $habitacion->id }}"
                                    {{ old('habitacion') == $habitacion->id ? 'selected' : '' }}>
                                    {{ $habitacion->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 ml-1">
                        <label for="pasajeros" class="form-label">Pasajeros <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="pasajeros" name="pasajeros" required>
                            <option value="">Seleccione Pasajero</option>
                        </select>
                        <small id="pasajerosMensaje" class="text-danger" role="status"></small>
                    </div>
                    <div class="col-md-5 mr-5 ml-2">
                        <label class="form-label">Puntos de Venta <b style="color: red;">(*)</b></label>
                        <div>
                            <div class="form-check form-check-inline mr-5">
                                <input class="form-check-input" type="radio" id="punto_comedor" name="punto" value="Comedor" {{ old('punto') == 'Comedor' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="punto_comedor">Comedor</label>
                            </div>
                            <div class="form-check form-check-inline mr-5">
                                <input class="form-check-input" type="radio" id="punto_bar" name="punto" value="Bar" {{ old('punto') == 'Bar' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="punto_bar">Bar</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="punto_recepcion" name="punto" value="Recepcion" {{ old('punto') == 'Recepcion' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="punto_recepcion">Recepción</label>
                            </div>
                        </div>
                    </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <table id="example1" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr style="background-color: #007bff; color: white; text-align: center;">
                                <th style="width: 30px; text-align: center;">Nro.</th>
                                <th style="width: 100px;">Subrubro</th>
                                <th style="width: 200px;">Nombre</th>
                                <th style="width: 50px; text-align: center;">Deposito</th>
                                <th style="width: 50px; text-align: center;">Comedor</th>
                                <th style="width: 50px; text-align: center;">Bar</th>
                                <th style="width: 50px; text-align: center;">Recepción</th>
                                <th style="width: 50px; text-align: center;">Precio</th>
                                <th style="width: 10px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-12 mt-4">
                <h5 class="text-primary">Lista de Consumos</h5>
                <table class="table table-bordered table-striped">
                    <thead class="table-primary text-center">
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th class="d-none">id</th>
                            <th style="width: 30%;">Detalle</th>
                            <th style="width: 12%;">Precio</th>
                            <th style="width: 5%;">Cant.</th>
                            <th style="width: 12%;">Importe</th>
                            <th style="width: 30%;">Pasajero</th>
                            <th style="width: 30%;">Moza</th>
                            <th style="width: 10%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaConsumos">
                        <!-- Las filas se agregarán dinámicamente aquí -->
                    </tbody>
                </table>
                <div id="comandaMensajeContenedor" hidden>
                    <button type="button" id="cerrarComandaMensaje" class="close" aria-label="Cerrar mensaje"><span aria-hidden="true">&times;</span></button>
                    <span id="comandaMensaje" role="status" aria-live="polite"></span>
                </div>
                <button type="button" id="grabarComanda" class="btn btn-primary mb-3">Grabar</button>
            </div>

        </div>
    </div>
    <div class="modal fade" id="consumoModal" tabindex="-1" role="dialog" aria-labelledby="consumoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formConsumo" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title" id="consumoModalLabel">Cargar consumo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="consumoProducto">Producto</label>
                            <input type="text" class="form-control" id="consumoProducto" readonly>
                        </div>
                        <div class="form-group">
                            <label for="consumoPrecio">Precio</label>
                            <input type="text" class="form-control" id="consumoPrecio" readonly>
                            <small id="consumoPrecioAviso" class="text-danger" role="status"></small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="consumoCantidad">Cantidad <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="consumoCantidad" min="1" max="999999999" step="1" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="consumoMoza">Número de moza <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="consumoMoza" min="1" step="1" required>
                            </div>
                        </div>
                        <div id="consumoMensaje" class="text-danger" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cargar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Reduce el alto de filas del DataTable */
        #example1 thead th,
        #example1 tbody td {
            padding: 0.35rem 0.5rem;
            line-height: 1.1;
            vertical-align: middle;
        }

        /* Fondo transparente y sin borde en el contenedor */
        #example1_wrapper .dt-buttons {
            background-color: transparent;
            box-shadow: none;
            border: none;
            display: flex;
            justify-content: center;
            /* Centrar los botones */
            gap: 10px;
            /* Espaciado entre botones */
            margin-bottom: 15px;
            /* Separar botones de la tabla */
        }

        /* Estilo personalizado para los botones */
        #example1_wrapper .btn {
            color: #fff;
            /* Color del texto en blanco */
            border-radius: 4px;
            /* Bordes redondeados */
            padding: 5px 15px;
            /* Espaciado interno */
            font-size: 10px;
            /* TamaÃ±o de fuente */
        }

        /* Colores por tipo de botÃ³n */
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-info {
            background-color: #17a2b8;
            border: none;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
            border: none;
        }

        .btn-default {
            background-color: #6e7176;
            color: #212529;
            border: none;
        }
    </style>
@stop

@section('js')
    <script>
        $(function() {
            const tablaArticulos = $("#example1").DataTable({
                "pageLength": 5,
                "ajax": {
                    "url": @json(route('comandas.cargar')),
                    "dataSrc": "data"
                },
                "columns": [
                    {
                        "data": null,
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        "data": "subrubro",
                        "render": $.fn.dataTable.render.text()
                    },
                    {
                        "data": "nombre",
                        "render": $.fn.dataTable.render.text()
                    },
                    {
                        "data": "stock_deposito",
                        "render": $.fn.dataTable.render.text()
                    },
                    {
                        "data": "stock_comedor",
                        "render": $.fn.dataTable.render.text()
                    },
                    {
                        "data": "stock_bar",
                        "render": $.fn.dataTable.render.text()
                    },
                    {
                        "data": "stock_recepcion",
                        "render": $.fn.dataTable.render.text()
                    },
                    {
                        "data": "precio",
                        "render": function(precio) {
                            return precio == null || precio === '' || precio === 'N/A' ? 0 : precio;
                        }
                    },
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function() {
                            return '<div class="btn-group" role="group" aria-label="Acciones">' +
                                '<button type="button" class="btn btn-success cargar-consumo" title="Cargar consumo" aria-label="Cargar consumo"><i class="bi bi-pencil"></i></button></div>';
                        }
                    }
                ],
                "columnDefs": [{
                    "targets": [0, 3, 4, 5, 6, 7],
                    "createdCell": function(celda) {
                        celda.classList.add('text-right');
                    }
                }],
                "language": {
                    "emptyTable": "No hay información",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ Registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 Registros",
                    "infoFiltered": "(Filtrado de _MAX_ total Registros)",
                    "lengthMenu": "Mostrar _MENU_ Registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscador:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                "responsive": true,
                "lengthChange": true,
                "autoWidth": false,
            });
            tablaArticulos.buttons().container().appendTo('#example1_wrapper .row:eq(0)');
            $('#example1 tbody').on('click', '.cargar-consumo', function() {
                const fila = $(this).closest('tr');
                const articulo = tablaArticulos.row(fila.hasClass('child') ? fila.prev() : fila).data();
                abrirModalConsumo(articulo);
            });
            $('#formConsumo').on('submit', cargarConsumo);
        });
    </script>

    <script>
        let articuloConsumo = null;

        function abrirModalConsumo(articulo) {
            articuloConsumo = articulo;
            document.getElementById('formConsumo').reset();
            document.getElementById('consumoProducto').value = articulo.nombre;
            const sinPrecio = articulo.precio == null || articulo.precio === '' || articulo.precio === 'N/A';
            document.getElementById('consumoPrecio').value = sinPrecio ? '0.00' : articulo.precio;
            document.getElementById('consumoPrecioAviso').textContent = sinPrecio
                ? 'Precio no registrado; no se puede cargar el consumo.'
                : '';
            document.getElementById('consumoMensaje').textContent = '';
            $('#consumoModal').modal('show');
        }

        function cargarConsumo(evento) {
            evento.preventDefault();
            const mensaje = document.getElementById('consumoMensaje');
            const cantidad = document.getElementById('consumoCantidad').value;
            const moza = document.getElementById('consumoMoza').value;
            const pasajero = document.getElementById('pasajeros');
            const habitacion = document.getElementById('habitacion');
            const punto = document.querySelector('input[name="punto"]:checked');
            mensaje.textContent = '';

            if (!pasajero.value || pasajero.disabled) {
                mensaje.textContent = 'Seleccione un pasajero antes de cargar el consumo.';
                return;
            }
            if (!habitacion.value || !punto) {
                mensaje.textContent = 'Seleccione una habitación y un punto de venta antes de cargar el consumo.';
                return;
            }
            if (!/^\d{1,9}(\.\d{1,3})?$/.test(cantidad) || Number(cantidad) <= 0) {
                mensaje.textContent = 'Ingrese una cantidad mayor a cero, con hasta tres decimales.';
                return;
            }
            if (!/^\d+$/.test(moza) || !Number.isSafeInteger(Number(moza)) || Number(moza) < 1) {
                mensaje.textContent = 'Ingrese un número de moza entero mayor a cero.';
                return;
            }
            const precio = String(articuloConsumo.precio);
            if (!/^\d{1,10}(\.\d{1,2})?$/.test(precio)) {
                mensaje.textContent = 'El producto no tiene un precio válido.';
                return;
            }
            const [precioEntero, precioDecimal = ''] = precio.split('.');
            const [cantidadEntera, cantidadDecimal = ''] = cantidad.split('.');
            const centavos = BigInt(precioEntero) * 100n + BigInt(precioDecimal.padEnd(2, '0'));
            const milesimas = BigInt(cantidadEntera) * 1000n + BigInt(cantidadDecimal.padEnd(3, '0'));
            const importe = (centavos * milesimas + 500n) / 1000n;
            if (importe > 999999999999n) {
                mensaje.textContent = 'El importe supera el máximo permitido.';
                return;
            }
            const formatearImporte = valor => `${valor / 100n}.${String(valor % 100n).padStart(2, '0')}`;
            const cuerpo = document.getElementById('tablaConsumos');
            const fila = document.createElement('tr');
            fila.dataset.articuloId = articuloConsumo.id;
            fila.dataset.pasajeroId = pasajero.value;
            fila.dataset.cantidad = cantidad;
            fila.dataset.precio = formatearImporte(centavos);
            fila.dataset.moza = moza;
            fila.dataset.habitacion = habitacion.value;
            fila.dataset.punto = punto.value;
            const valores = [
                String(cuerpo.rows.length + 1), String(articuloConsumo.id), articuloConsumo.nombre,
                formatearImporte(centavos), cantidad, formatearImporte(importe),
                pasajero.options[pasajero.selectedIndex].textContent, moza,
            ];
            valores.forEach((valor, indice) => {
                const celda = document.createElement('td');
                if (indice === 1) celda.className = 'd-none';
                if ([0, 3, 4, 5].includes(indice)) celda.className = 'text-right';
                celda.textContent = valor;
                fila.appendChild(celda);
            });
            const acciones = document.createElement('td');
            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'btn btn-danger btn-sm';
            quitar.textContent = 'Quitar';
            quitar.setAttribute('aria-label', `Quitar ${articuloConsumo.nombre} de la lista`);
            quitar.addEventListener('click', () => {
                fila.remove();
                Array.from(cuerpo.rows).forEach((item, indice) => { item.cells[0].textContent = String(indice + 1); });
            });
            acciones.appendChild(quitar);
            fila.appendChild(acciones);
            cuerpo.appendChild(fila);
            $('#consumoModal').modal('hide');
        }

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const grabar = document.getElementById('grabarComanda');
            const mensaje = document.getElementById('comandaMensaje');
            const contenedorMensaje = document.getElementById('comandaMensajeContenedor');
            const cerrarMensaje = document.getElementById('cerrarComandaMensaje');
            const tabla = document.getElementById('tablaConsumos');

            function ocultarMensaje() {
                contenedorMensaje.hidden = true;
                mensaje.textContent = '';
            }

            function mostrarMensaje(texto, estilo = 'alert alert-danger') {
                mensaje.textContent = texto;
                contenedorMensaje.className = estilo;
                contenedorMensaje.hidden = false;
            }

            cerrarMensaje.addEventListener('click', ocultarMensaje);
            grabar.addEventListener('click', async function() {
                ocultarMensaje();
                const habitacionInput = document.getElementById('habitacion');
                const habitacion = habitacionInput.value;
                const punto = document.querySelector('input[name="punto"]:checked');
                const filas = Array.from(tabla.rows);
                if (!habitacion || !punto || filas.length === 0) {
                    mostrarMensaje('Seleccione habitación y punto de venta y cargue al menos un consumo.');
                    return;
                }
                if (filas.some(fila => fila.dataset.habitacion !== habitacion || fila.dataset.punto !== punto.value)) {
                    mostrarMensaje('Los consumos deben corresponder a la habitación y al punto seleccionados. Quite los que no correspondan.');
                    return;
                }
                const detalles = filas.map(fila => ({
                    articulo_id: fila.dataset.articuloId,
                    pasajero_id: fila.dataset.pasajeroId,
                    cantidad: fila.dataset.cantidad,
                    precio: fila.dataset.precio,
                    moza: fila.dataset.moza,
                }));
                grabar.disabled = true;
                let respuesta;
                let resultado;
                try {
                    respuesta = await fetch(@json(route('comandas.store')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        body: JSON.stringify({ habitacion, punto: punto.value, detalles }),
                    });
                    if (respuesta.status >= 500) throw new Error('No se pudo confirmar el resultado.');
                    resultado = await respuesta.json();
                } catch (error) {
                    mostrarMensaje('No se pudo confirmar si se guardó la comanda. Verifique antes de volver a intentarlo.');
                    console.error('Error al guardar la comanda:', error);
                    return;
                }
                if (!respuesta.ok) {
                    mostrarMensaje(resultado.errors
                        ? Object.values(resultado.errors).flat().join(' ')
                        : (resultado.message || 'No se pudo guardar la comanda.'));
                    grabar.disabled = false;
                    return;
                }
                tabla.replaceChildren();
                habitacionInput.value = '';
                habitacionInput.dispatchEvent(new Event('change', { bubbles: true }));
                punto.checked = false;
                document.getElementById('formConsumo').reset();
                document.getElementById('consumoPrecioAviso').textContent = '';
                document.getElementById('consumoMensaje').textContent = '';
                const correoMensaje = resultado.correos_enviados === 1
                    ? 'Se envió 1 correo.'
                    : `Se enviaron ${resultado.correos_enviados} correos.`;
                mostrarMensaje(
                    [resultado.mensaje, correoMensaje, ...resultado.advertencias].join(' '),
                    resultado.advertencias.length ? 'alert alert-warning' : 'alert alert-success'
                );
                grabar.disabled = false;
                const tablaArticulos = $('#example1').DataTable();
                tablaArticulos.search('');
                tablaArticulos.ajax.reload(null, true);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const habitacionInput = document.getElementById('habitacion');
            const pasajeroSelect = document.getElementById('pasajeros');
            const mensaje = document.getElementById('pasajerosMensaje');
            const oldHabitacion = @json(old('habitacion'));
            const oldPasajero = @json(old('pasajeros'));
            let solicitudActual = 0;

            const setDefault = () => {
                pasajeroSelect.innerHTML = '<option value="">Seleccione pasajero</option>';
            };

            const cargarPasajeros = async (habitacion, pasajeroSeleccionado = '') => {
                const solicitud = ++solicitudActual;
                setDefault();
                mensaje.textContent = '';
                pasajeroSelect.disabled = true;

                if (!habitacion) {
                    return;
                }

                try {
                    const endpoint = "{{ route('pasajeros.habitacion', ['habitacion' => '__HAB__']) }}".replace('__HAB__', encodeURIComponent(habitacion));
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!response.ok) {
                        throw new Error(`Error HTTP ${response.status}`);
                    }
                    const pasajeros = await response.json();
                    if (solicitud !== solicitudActual) return;

                    pasajeros.forEach((pasajero) => {
                        const option = document.createElement('option');
                        option.value = pasajero.id;
                        option.textContent = `${pasajero.nombre} - ${pasajero.documento}`;

                        if (pasajeroSeleccionado && String(pasajeroSeleccionado) === String(pasajero.id)) {
                            option.selected = true;
                        }

                        pasajeroSelect.appendChild(option);
                    });
                    if (pasajeros.length === 0) {
                        mensaje.textContent = 'No hay pasajeros ocupando esta habitación.';
                    }
                    pasajeroSelect.disabled = false;
                } catch (error) {
                    if (solicitud !== solicitudActual) return;
                    mensaje.textContent = 'No se pudieron cargar los pasajeros. Vuelva a seleccionar la habitación.';
                    console.error('Error al cargar pasajeros por habitacion:', error);
                }
            };

            habitacionInput.addEventListener('change', function() {
                cargarPasajeros(this.value);
            });

            if (oldHabitacion) {
                habitacionInput.value = oldHabitacion;
                cargarPasajeros(oldHabitacion, oldPasajero);
            } else {
                pasajeroSelect.disabled = true;
            }
        });
    </script>
@stop
