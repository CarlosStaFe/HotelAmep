@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Caja</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><b>Registros de caja</b></h3>
        </div>
        <div class="card-body">
            <table id="tablaCaja" class="table table-sm table-bordered table-hover table-striped">
                <thead>
                    <tr style="background-color: #007bff; color: white; text-align: center;">
                        <th>Nro.</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Habitación</th>
                        <th>Efectivo</th>
                        <th>Transferencia</th>
                        <th>Tarjeta</th>
                        <th>Total</th>
                        <th>Usuario</th>
                        <th>Fecha de registro</th>
                        <th>Última actualización</th>
                        <th>Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cajas as $caja)
                        <tr>
                            <td class="text-right">{{ $caja->id }}</td>
                            <td data-order="{{ $caja->checkin }}">{{ \Carbon\Carbon::parse($caja->checkin)->format('d/m/Y') }}</td>
                            <td data-order="{{ $caja->checkout }}">{{ \Carbon\Carbon::parse($caja->checkout)->format('d/m/Y') }}</td>
                            <td>{{ $caja->habitacion }}</td>
                            <td class="text-right" data-order="{{ $caja->efectivo }}">{{ number_format($caja->efectivo, 2, '.', ',') }}</td>
                            <td class="text-right" data-order="{{ $caja->transferencia }}">{{ number_format($caja->transferencia, 2, '.', ',') }}</td>
                            <td class="text-right" data-order="{{ $caja->tarjeta }}">{{ number_format($caja->tarjeta, 2, '.', ',') }}</td>
                            <td class="text-right" data-order="{{ $caja->total }}">{{ number_format($caja->total, 2, '.', ',') }}</td>
                            <td>{{ $caja->usuario ?? 'Usuario no disponible' }} (ID {{ $caja->user_id }})</td>
                            <td data-order="{{ $caja->created_at }}">{{ $caja->created_at ? \Carbon\Carbon::parse($caja->created_at)->format('d/m/Y H:i:s') : '-' }}</td>
                            <td data-order="{{ $caja->updated_at }}">{{ $caja->updated_at ? \Carbon\Carbon::parse($caja->updated_at)->format('d/m/Y H:i:s') : '-' }}</td>
                            <td class="text-center" data-order="{{ $caja->controlado }}">
                                <form action="{{ route('caja.control', $caja->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="controlado" value="{{ $caja->controlado ? '0' : '1' }}">
                                    <button type="submit" class="btn {{ $caja->controlado ? 'btn-success' : 'btn-danger' }} btn-sm">
                                        {{ $caja->controlado ? 'Controlado' : 'Sin Controlar' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            $('#tablaCaja').DataTable({
                pageLength: 10,
                order: [[1, 'asc'], [3, 'asc']],
                stateSave: true,
                stateSaveParams: function(settings, data) {
                    data.columnVersion = 2;
                },
                stateLoadParams: function(settings, data) {
                    return data.columnVersion === 2;
                },
                responsive: true,
                autoWidth: false,
                language: {
                    emptyTable: 'No hay registros de caja',
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
@stop
