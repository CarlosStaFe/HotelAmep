@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item">Usuarios</li>
            <li class="breadcrumb-item active" aria-current="page">Usuarios conectados</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><b>Usuarios conectados</b></h3>
            <div class="card-tools">
                <a href="{{ route('usuarios.conectados') }}" class="btn btn-primary">Actualizar</a>
            </div>
        </div>
        <div class="card-body">
            @if ($error)
                <div class="alert alert-danger" role="alert">{{ $error }}</div>
            @else
                <p>Se muestran las sesiones con actividad en los últimos {{ $duracion }} minutos. Un usuario puede aparecer más de una vez.</p>
                <table id="usuariosConectados" class="table table-sm table-bordered table-hover table-striped">
                    <thead>
                        <tr class="bg-primary">
                            <th>Usuario</th>
                            <th>Dirección IP</th>
                            <th>Última actividad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sesiones as $sesion)
                            <tr>
                                <td>{{ $sesion->usuario->name }}</td>
                                <td>{{ $sesion->ip_address ?? 'No disponible' }}</td>
                                <td data-order="{{ $sesion->last_activity }}">{{ \Carbon\Carbon::createFromTimestamp($sesion->last_activity, config('app.timezone'))->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@stop

@section('js')
    @if (! $error)
        <script>
            $(function() {
                $('#usuariosConectados').DataTable({
                    pageLength: 10,
                    order: [[2, 'desc']],
                    responsive: true,
                    autoWidth: false,
                    language: {
                        emptyTable: 'No hay usuarios conectados',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        infoFiltered: '(filtrado de _MAX_ registros)',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        search: 'Buscador:',
                        zeroRecords: 'Sin resultados encontrados',
                        paginate: {
                            first: 'Primero',
                            last: 'Último',
                            next: 'Siguiente',
                            previous: 'Anterior'
                        }
                    }
                });
            });
        </script>
    @endif
@stop
