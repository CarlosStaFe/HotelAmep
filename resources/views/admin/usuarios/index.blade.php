@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Usuarios</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Usuarios registrados</b></h3>

                    <div class="card-tools">
                        <a href="{{ url('/admin/usuarios/create') }}" class="btn btn-primary"
                            aria-label="Crear Nuevo">Crear Nuevo</a>
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="box-sizing: border-box; display: block;">

                    <table id="example1" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr style="background-color: #007bff; color: white; text-align: center;">
                                <th style="width: 50px; min-width: 50px;">Nro.</th>
                                <th style="width: 250px; min-width: 100px;">Nombre</th>
                                <th style="width: 250px; min-width: 100px;">Rol</th>
                                <th style="width: 250px; min-width: 100px;">Email</th>
                                <th style="width: 130px; min-width: 50px;">Activo?</th>
                                <th style="width: 140px; min-width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $usuario)
                                <tr>
                                    <td style="text-align: right;">{{ $loop->iteration }}</td>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td class="text-center">
                                        @if ($usuario->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <a href="{{ url('/admin/usuarios/' . $usuario->id) }}" class="btn btn-success"><i class="bi bi-eye"></i></a>
                                            <a href="{{ url('/admin/usuarios/' . $usuario->id . '/edit') }}" class="btn btn-info"><i class="bi bi-pencil"></i></a>
                                            <a href="{{ url('/admin/usuarios/' . $usuario->id . '/delete') }}" class="btn btn-danger"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
            <!-- /.card -->
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
