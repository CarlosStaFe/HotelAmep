@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Calendario</li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Asignadas</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Habitaciones asignadas</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success mr-3" data-toggle="modal"
                            data-target="#asignarModal">Asignar</button>
                        <a href="{{ url('/admin/pasajeros/index') }}" class="btn btn-primary ml-3"
                            aria-label="Pasajeros">Pasajeros</a>
                    </div>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <table id="example1" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr style="background-color: #007bff; color: white; text-align: center;">
                                <th style="width: 20px;">Orden</th>
                                <th style="width: 60px;">Check-in</th>
                                <th style="width: 60px;">Check-out</th>
                                <th style="width: 30px;">Nro.</th>
                                <th style="width: 200px;">Pasajero</th>
                                <th style="width: 60px;">Documento</th>
                                <th style="width: 40px;">Comanda</th>
                                <th style="width: 40px;">Estado</th>
                                <th style="width: 40px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asignadas as $asignada)
                                @php
                                    $checkin = \Carbon\Carbon::parse($asignada->checkin);
                                    $checkout = \Carbon\Carbon::parse($asignada->checkout);
                                @endphp
                                <tr>
                                    <td style="text-align: right;">{{ $loop->iteration }}</td>
                                    <td data-order="{{ $checkin->format('Y-m-d') }}"><span class="fecha-checkin">{{ $checkin->format('d-m-Y') }}</span></td>
                                    <td data-order="{{ $checkout->format('Y-m-d') }}"><span class="fecha-checkout">{{ $checkout->format('d-m-Y') }}</span></td>
                                    <td><span class="numero-habitacion" data-habitacion-id="{{ $asignada->habitacion_id }}">{{ $asignada->habitacion->numero ?? 'N/A' }}</span></td>
                                    <td>{{ $asignada->pasajero->apelyNombre ?? 'N/A' }}</td>
                                    <td>{{ $asignada->pasajero->documento ?? 'N/A' }}</td>
                                    <td style="text-align: center;">
                                        <span class="badge {{ $asignada->pasajero->usaComanda ? 'badge-success' : 'badge-danger' }}">{{ $asignada->pasajero->usaComanda ? 'SI' : 'NO' }}</span>
                                    </td>
                                    <td style="text-align: left;">
                                        @include('admin.calendarios._estado', ['estado' => $asignada->estado])
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ url('/admin/pasajeros/' . $asignada->pasajero->id) }}" title="Ver pasajero" class="btn btn-success"><i class="bi bi-eye"></i></a>                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="asignarModal" tabindex="-1" role="dialog" aria-labelledby="asignarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('calendarios.asignadas.store') }}" method="POST" onsubmit="return validarAsignacion()">
                    @csrf
                    <input type="hidden" name="formulario" value="asignacion">
                    <div class="modal-header">
                        <h5 class="modal-title" id="asignarModalLabel">Asignar pasajeros a una reserva</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any() && old('formulario') === 'asignacion')
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        <div class="form-group">
                            <label for="asignar_reserva">Reserva <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="asignar_reserva" name="reserva_id" data-anterior="{{ old('reserva_id', '') }}" onchange="cambiarReservaAsignacion()" required>
                                <option value="">Seleccione una reserva</option>
                                @foreach ($reservas as $reserva)
                                    <option value="{{ $reserva->id }}" data-plazas="{{ $reserva->habitacion->plazas }}" data-ocupadas="{{ $plazasOcupadas[$reserva->id] ?? 0 }}" @selected(old('reserva_id') == $reserva->id)>
                                        Habitación {{ $reserva->habitacion->numero }} - {{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d-m-Y') }} a {{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d-m-Y') }} - {{ $reserva->estado }} - {{ $reserva->detalle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="asignar_pasajero">Pasajero <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="asignar_pasajero" onchange="seleccionarComandaAsignacion()">
                                    <option value="">Seleccione un pasajero</option>
                                    @foreach ($pasajeros as $pasajero)
                                        @php
                                            $nacimiento = $pasajero->fechaNacimiento ? \Carbon\Carbon::parse($pasajero->fechaNacimiento)->startOfDay() : null;
                                            $edad = $nacimiento && !$nacimiento->isFuture() ? (int) floor($nacimiento->diffInYears(today())) : null;
                                        @endphp
                                        <option value="{{ $pasajero->id }}" data-comanda="{{ $pasajero->usaComanda ? 'SI' : 'NO' }}" data-cuenta-pax="{{ is_numeric($edad) && $edad < 5 ? '0' : '1' }}" {{ is_numeric($edad) && $edad < 5 ? 'style=color:red' : '' }}>
                                            {{ $pasajero->apelyNombre }} - {{ $pasajero->documento }} - Edad: {{ $edad ?? 'N/D' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="asignar_comanda">Comanda <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="asignar_comanda">
                                    <option value="">Elija</option>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" onclick="cargarPasajeroAsignacion()">Cargar</button>
                            </div>
                        </div>
                        <div id="asignarMensaje" class="text-danger mb-2" role="alert"></div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Pasajero</th><th>Comanda</th><th></th></tr></thead>
                                <tbody id="asignarPasajeros"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Asignar</button>
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
            font-size: 14px;
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
        function ocultarValoresRepetidos(filas) {
            let checkinAnterior = null;
            let checkoutAnterior = null;
            let habitacionAnterior = null;

            filas.each(function(fila) {
                const checkin = fila.querySelector('.fecha-checkin');
                const checkout = fila.querySelector('.fecha-checkout');
                const habitacion = fila.querySelector('.numero-habitacion');
                checkin.style.visibility = checkin.textContent === checkinAnterior ? 'hidden' : '';
                checkout.style.visibility = checkout.textContent === checkoutAnterior ? 'hidden' : '';
                const fechasVisibles = checkin.style.visibility !== 'hidden' && checkout.style.visibility !== 'hidden';
                habitacion.style.visibility = habitacion.dataset.habitacionId === habitacionAnterior && !fechasVisibles ? 'hidden' : '';
                checkinAnterior = checkin.textContent;
                checkoutAnterior = checkout.textContent;
                habitacionAnterior = habitacion.dataset.habitacionId;
            });
        }

        $(function() {
            $("#example1").DataTable({
                "pageLength": 10,
                "drawCallback": function() {
                    ocultarValoresRepetidos(this.api().rows({ page: 'current' }).nodes());
                },
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
                buttons: [{
                        text: '<i class="fas fa-copy"></i> COPIAR',
                        extend: 'copy',
                        className: 'btn btn-default'
                    },
                    {
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        extend: 'pdf',
                        className: 'btn btn-danger'
                    },
                    {
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        extend: 'csv',
                        className: 'btn btn-info'
                    },
                    {
                        text: '<i class="fas fa-file-excel"></i> EXCEL',
                        extend: 'excel',
                        className: 'btn btn-success'
                    },
                    {
                        text: '<i class="fas fa-print"></i> IMPRIMIR',
                        extend: 'print',
                        className: 'btn btn-warning'
                    }
                ]
            }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
        });
    </script>
    <script>
        function seleccionarComandaAsignacion() {
            const select = document.getElementById('asignar_pasajero');
            document.getElementById('asignar_comanda').value = select.value
                ? select.options[select.selectedIndex].dataset.comanda
                : '';
        }

        function datosReservaAsignacion() {
            const reserva = document.getElementById('asignar_reserva');
            if (!reserva.value) return null;
            const datos = reserva.options[reserva.selectedIndex].dataset;
            if (datos.plazas === '' || !Number.isInteger(Number(datos.plazas)) || Number(datos.plazas) < 0) return null;
            return { plazas: Number(datos.plazas), ocupadas: Number(datos.ocupadas) };
        }

        function contarPasajerosAsignacion() {
            return Array.from(document.getElementById('asignarPasajeros').rows)
                .filter(fila => fila.dataset.cuentaPax !== '0').length;
        }

        function cambiarReservaAsignacion() {
            const reserva = document.getElementById('asignar_reserva');
            const mensaje = document.getElementById('asignarMensaje');
            const cantidad = contarPasajerosAsignacion();
            const datos = datosReservaAsignacion();
            mensaje.textContent = '';
            if (cantidad > 0 && (!datos || datos.ocupadas + cantidad > datos.plazas)) {
                reserva.value = reserva.dataset.anterior;
                mensaje.textContent = 'No se puede cambiar de reserva: quite pasajeros o elija una habitación con suficientes plazas.';
                return;
            }
            reserva.dataset.anterior = reserva.value;
        }

        function cargarPasajeroAsignacion(restaurar = false) {
            const reserva = document.getElementById('asignar_reserva');
            const pasajero = document.getElementById('asignar_pasajero');
            const comanda = document.getElementById('asignar_comanda');
            const cuerpo = document.getElementById('asignarPasajeros');
            const mensaje = document.getElementById('asignarMensaje');
            mensaje.textContent = '';

            if (!restaurar && !reserva.value) {
                mensaje.textContent = 'Seleccione una reserva.';
                reserva.focus();
                return;
            }
            if (!pasajero.value || !comanda.value) {
                mensaje.textContent = 'Seleccione un pasajero y una opción de comanda.';
                return;
            }
            if (Array.from(cuerpo.rows).some(fila => fila.dataset.pasajeroId === pasajero.value)) {
                mensaje.textContent = 'El pasajero ya está en la lista.';
                return;
            }

            const cuentaPax = pasajero.options[pasajero.selectedIndex].dataset.cuentaPax !== '0';
            const datos = datosReservaAsignacion();
            if (!restaurar && !datos) {
                mensaje.textContent = 'No se pudo determinar la cantidad de plazas de la habitación.';
                return;
            }
            if (!restaurar && cuentaPax && datos.ocupadas + contarPasajerosAsignacion() >= datos.plazas) {
                mensaje.textContent = `La habitación tiene ${datos.plazas} plaza(s); no se pueden agregar más pasajeros.`;
                return;
            }

            const fila = document.createElement('tr');
            fila.dataset.pasajeroId = pasajero.value;
            fila.dataset.cuentaPax = cuentaPax ? '1' : '0';
            const nombre = document.createElement('td');
            nombre.textContent = pasajero.options[pasajero.selectedIndex].textContent.trim();
            const etiquetaNombre = nombre.textContent;
            if (!cuentaPax) nombre.textContent += ' (menor de 5 años, no cuenta como pasajero)';
            const opcion = document.createElement('td');
            opcion.textContent = comanda.value;
            for (const [campo, valor] of [['id', pasajero.value], ['comanda', comanda.value]]) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `pasajeros[p${pasajero.value}][${campo}]`;
                input.value = valor;
                opcion.appendChild(input);
            }
            const acciones = document.createElement('td');
            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'btn btn-sm btn-outline-danger';
            quitar.textContent = 'Quitar';
            quitar.setAttribute('aria-label', `Quitar a ${etiquetaNombre}`);
            quitar.addEventListener('click', () => { fila.remove(); mensaje.textContent = ''; });
            acciones.appendChild(quitar);
            fila.appendChild(nombre);
            fila.appendChild(opcion);
            fila.appendChild(acciones);
            cuerpo.appendChild(fila);
        }

        function validarAsignacion() {
            const mensaje = document.getElementById('asignarMensaje');
            const reserva = document.getElementById('asignar_reserva');
            if (!reserva.value) {
                mensaje.textContent = 'Seleccione una reserva.';
                return false;
            }
            if (document.getElementById('asignarPasajeros').rows.length === 0) {
                mensaje.textContent = 'Asigne al menos un pasajero.';
                return false;
            }
            const datos = datosReservaAsignacion();
            if (!datos || datos.ocupadas + contarPasajerosAsignacion() > datos.plazas) {
                mensaje.textContent = 'La cantidad de pasajeros supera las plazas de la habitación o su capacidad no está definida.';
                return false;
            }
            mensaje.textContent = '';
            return true;
        }

        function limpiarFormularioAsignacion() {
            document.getElementById('asignarPasajeros').replaceChildren();
            document.getElementById('asignarMensaje').textContent = '';
            ['asignar_reserva', 'asignar_pasajero', 'asignar_comanda'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.getElementById('asignar_reserva').dataset.anterior = '';
            document.querySelectorAll('#asignarModal .alert-danger').forEach(alerta => alerta.remove());
        }

        $(function() {
            $('#asignarModal').on('hidden.bs.modal', limpiarFormularioAsignacion);
            @if ($errors->any() && old('formulario') === 'asignacion')
                Object.values(@json(old('pasajeros', []))).forEach(asignacion => {
                    if (!asignacion || typeof asignacion !== 'object') return;
                    document.getElementById('asignar_pasajero').value = asignacion.id;
                    document.getElementById('asignar_comanda').value = asignacion.comanda;
                    cargarPasajeroAsignacion(true);
                });
                $('#asignarModal').modal('show');
            @endif
        });
    </script>
@stop
