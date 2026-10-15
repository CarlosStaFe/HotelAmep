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
        <div class="col-md-9">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                </div>
                <div class="row">
                    <div class="col-md-2">
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
                    <div class="col-md-5">
                        <label for="pasajeros" class="form-label">Pasajeros <b style="color: red;">(*)</b></label>
                        <select class="form-control" id="pasajeros" name="pasajeros" required>
                            <option value="">Seleccione Pasajero</option>
{{--                             @foreach ($pasajeros as $pasajero)
                                <option value="{{ $pasajero->id }}"
                                    {{ old('pasajero') == $pasajero->id ? 'selected' : '' }}>
                                    {{ $pasajero->nombre }}
                                </option>
                            @endforeach --}}
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Punto de Venta <b style="color: red;">(*)</b></label>
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
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <table id="example1" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr style="background-color: #007bff; color: white; text-align: center;">
                                <th style="width: 30px;">Nro.</th>
                                <th style="width: 100px;">Rubro</th>
                                <th style="width: 200px;">Nombre</th>
                                <th style="width: 30px;">Stock</th>
                                <th style="width: 50px;">Precio</th>
                                <th style="width: 10px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($articulos as $articulo)
                                <tr>
                                    <td style="text-align: right;">{{ $loop->iteration }}</td>
                                    <td>{{ $articulo->rubro?->nombre ?? 'Sin rubro' }}</td>
                                    <td>{{ $articulo->nombre }}</td>
                                    <td>{{ $articulo->stock ?? 'N/A' }}</td>
                                    <td>{{ $articulo->precio ?? 'N/A' }}</td>
                                    <td style="text-align: center;">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a href="{{ url('/admin/comandas/' . $articulo->id) }}" class="btn btn-success"><i class="bi bi-pencil"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
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
                            <th style="width: 10%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaConsumos">
                        <!-- Las filas se agregarán dinámicamente aquí -->
                    </tbody>
                </table>
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
            $("#example1").DataTable({
                "pageLength": 10,
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
            }).buttons().container().appendTo('#example1_wrapper .row:eq(0)');
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const habitacionInput = document.getElementById('habitacion');
            const pasajeroSelect = document.getElementById('pasajero_id');
            const oldHabitacion = @json(old('habitacion'));
            const oldPasajero = @json(old('pasajero_id'));

            const setDefault = () => {
                pasajeroSelect.innerHTML = '<option value="">Seleccione pasajero</option>';
            };

            const cargarPasajeros = async (habitacion, pasajeroSeleccionado = '') => {
                setDefault();

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
                    const pasajeros = await response.json();

                    pasajeros.forEach((pasajero) => {
                        const option = document.createElement('option');
                        option.value = pasajero.id;
                        option.textContent = pasajero.nombre;

                        if (pasajeroSeleccionado && String(pasajeroSeleccionado) === String(pasajero.id)) {
                            option.selected = true;
                        }

                        pasajeroSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error al cargar pasajeros por habitacion:', error);
                }
            };

            habitacionInput.addEventListener('change', function() {
                cargarPasajeros(this.value);
            });

            habitacionInput.addEventListener('blur', function() {
                if (this.value) {
                    cargarPasajeros(this.value);
                }
            });

            if (oldHabitacion) {
                habitacionInput.value = oldHabitacion;
                cargarPasajeros(oldHabitacion, oldPasajero);
            }
        });
    </script>
@stop
