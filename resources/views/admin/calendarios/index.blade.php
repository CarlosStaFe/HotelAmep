@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Calendario</li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Reservas</li>
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
                    <h3 class="card-title"><b>Reservas registradas</b></h3>
                    <div class="card-tools">
                        <a href="{{ route('calendarios.calendario') }}" class="btn btn-primary"
                            aria-label="Crear Nuevo">Crear Nuevo</a>
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
                                <th style="width: 180px;">Pasajero</th>
                                <th style="width: 250px;">Detalle</th>
                                <th style="width: 60px;">Estado</th>
                                <th style="width: 80px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservas as $reserva)
                                <tr>
                                    <td style="text-align: right;">{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reserva->fecha_inicio)->format('d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($reserva->fecha_fin)->format('d-m-Y') }}</td>
                                    <td>{{ $reserva->habitacion->numero ?? 'N/A' }}</td>
                                    <td>{{ $reserva->pasajero->apelyNombre ?? 'N/A' }}</td>
                                    <td>{{ $reserva->detalle ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $estadoColor = match ($reserva->estado) {
                                                'Reservada' => '#D2E014',
                                                'Confirmada' => '#F29727',
                                                'Ocupada' => '#B82E27',
                                                'Liberada' => '#A69F9F',
                                                default => '#0CB371',
                                            };
                                        @endphp
                                        <span class="badge" style="background-color: {{ $estadoColor }}; color: {{ $reserva->estado === 'Reservada' ? '#212529' : '#fff' }};">
                                            {{ $reserva->estado ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="btn-group" role="group" aria-label="Acciones de la reserva">
                                            <form action="{{ route('reservas.cambiar-estado', $reserva) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="estado" value="Confirmada">
                                                <button type="submit" class="btn btn-success" title="Marcar confirmada" aria-label="Marcar reserva como confirmada" @disabled($reserva->estado !== 'Reservada')><i class="bi bi-currency-dollar"></i></button>
                                            </form>
                                            <form action="{{ route('reservas.cambiar-estado', $reserva) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="estado" value="Ocupada">
                                                <button type="submit" class="btn btn-info" title="Marcar ocupada" aria-label="Marcar reserva como ocupada" @disabled($reserva->estado !== 'Confirmada')><i class="bi bi-person-square"></i></button>
                                            </form>
                                            <form action="{{ route('reservas.cambiar-estado', $reserva) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="estado" value="Liberada">
                                                <button type="submit" class="btn" style="background-color: #A69F9F; color: #fff;" title="Marcar liberada" aria-label="Marcar reserva como liberada" @disabled($reserva->estado !== 'Ocupada')><i class="bi bi-person-walking"></i></button>
                                            </form>
                                            <form action="{{ route('reservas.cambiar-estado', $reserva) }}" method="POST" onsubmit="return confirm('Se liberarán las fechas y se eliminará la reserva. ¿Continuar?');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="estado" value="Disponible">
                                                <button type="submit" class="btn btn-danger" title="Liberar y eliminar reserva" aria-label="Liberar fechas y eliminar reserva" @disabled(!in_array($reserva->estado, ['Reservada', 'Confirmada', 'Ocupada', 'Liberada'], true))><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
@stop
