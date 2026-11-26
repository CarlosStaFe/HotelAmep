@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compras</li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Compras</li>
        </ol>
    </nav>
@stop

@section('content')
    @php
        $idConError = old('compra_id');
        $compraConError = is_scalar($idConError) && $errors->has('datos_pago')
            ? $compras->first(fn ($item) => (string) $item->id === (string) $idConError && $item->estado === 'Pendiente')
            : null;
        $datosPagoAnteriores = is_string(old('datos_pago')) ? old('datos_pago') : '';
    @endphp
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->has('datos_pago') && ! $compraConError)
                <div class="alert alert-danger" role="alert">{{ $errors->first('datos_pago') }}</div>
            @endif
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Compras registradas</b></h3>

                    @if (auth()->user()->canAccessMenu('compras.create'))
                    <div class="card-tools">
                        <a href="{{ route('compras.create') }}" class="btn btn-primary"
                            aria-label="Crear Nuevo">Crear Nuevo</a>
                    </div>
                    @endif
                </div>

                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <table id="example1" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr style="background-color: #007bff; color: white; text-align: center;">
                                <th style="width: 30px;">Nro.</th>
                                <th style="width: 30px;">Fecha</th>
                                <th style="width: 200px;">Nombre</th>
                                <th style="width: 200px;">Razón Social</th>
                                <th style="width: 50px;">Tipo</th>
                                <th style="width: 40px;">PreFijo</th>
                                <th style="width: 40px;">SubFijo</th>
                                <th style="width: 70px;">Total</th>
                                <th style="width: 40px;">Estado</th>
                                <th style="width: 30px;">Días</th>
                                <th style="width: 100px;">Modificado</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($compras as $compra)
                                @php
                                    $fechaCompra = \Carbon\Carbon::parse($compra->fechaCpra);
                                @endphp
                                <tr @class([
                                    'table-danger' => $compra->estado === 'Pendiente' && $fechaCompra->copy()->addDays($compra->dias)->lt(today()),
                                    'table-success' => in_array($compra->estado, ['Pagada', 'Contado'], true),
                                ])>
                                    <td style="text-align: right;">{{ $compra->id }}</td>
                                    <td style="text-align: right;" data-order="{{ $fechaCompra->format('Y-m-d') }}">{{ $fechaCompra->format('d/m/Y') }}</td>
                                    <td>{{ $compra->proveedor_nombre ?? 'N/A' }}</td>
                                    <td>{{ $compra->proveedor_razon_social ?? 'N/A' }}</td>
                                    <td>{{ $compra->tipoCpbte }}</td>
                                    <td style="text-align: right;">{{ $compra->prefijo }}</td>
                                    <td style="text-align: right;">{{ $compra->subfijo }}</td>
                                    <td class="text-right" data-order="{{ $compra->total }}">{{ number_format($compra->total, 2, '.', ',') }}</td>
                                    <td>{{ $compra->estado }}</td>
                                    <td style="text-align: right;">{{ $compra->dias }}</td>
                                    <td class="text-right" data-order="{{ $compra->updated_at }}">{{ $compra->updated_at ? \Carbon\Carbon::parse($compra->updated_at)->format('d/m/Y H:i:s') : '-' }}</td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('compras.show', $compra->id) }}" class="btn btn-info"
                                            aria-label="Ver detalle de la compra {{ $compra->id }}" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if ($compra->estado === 'Pendiente')
                                            <button type="button" class="btn btn-success" aria-label="Marcar compra {{ $compra->id }} como pagada"
                                                title="Marcar como pagada" data-toggle="modal" data-target="#pagoModal"
                                                data-url="{{ route('compras.pagar', $compra->id) }}" data-compra="{{ $compra->id }}">
                                                <i class="bi bi-currency-dollar"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pagoModal" tabindex="-1" role="dialog" aria-labelledby="pagoModalTitulo" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formPago" method="POST" action="{{ $compraConError ? route('compras.pagar', $compraConError->id) : '' }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="compra_id" id="compraPagoId" value="{{ $compraConError ? $compraConError->id : '' }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pagoModalTitulo">Marcar compra como pagada</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Compra Nro. <span id="numeroCompraPago">{{ $compraConError ? $compraConError->id : '' }}</span></p>
                        <label for="datosPago">Datos del pago</label>
                        <textarea id="datosPago" name="datos_pago" class="form-control @error('datos_pago') is-invalid @enderror"
                            maxlength="255" rows="3" required aria-describedby="datosPagoAyuda">{{ $datosPagoAnteriores }}</textarea>
                        <small id="datosPagoAyuda" class="form-text text-muted">Máximo 255 caracteres. Se agregará a la observación existente.</small>
                        @error('datos_pago')
                            <div class="invalid-feedback" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar pago</button>
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
        $(function() {
            $('#pagoModal').on('show.bs.modal', function(event) {
                if (!event.relatedTarget) return;

                const boton = event.relatedTarget;
                const compraId = String(boton.dataset.compra);
                const campoId = document.getElementById('compraPagoId');
                if (campoId.value !== compraId) {
                    document.getElementById('datosPago').value = '';
                    document.getElementById('datosPago').classList.remove('is-invalid');
                }
                campoId.value = compraId;
                document.getElementById('numeroCompraPago').textContent = compraId;
                document.getElementById('formPago').action = boton.dataset.url;
            });
            $('#pagoModal').on('shown.bs.modal', function() {
                document.getElementById('datosPago').focus();
            });
            @if ($compraConError)
                $('#pagoModal').modal('show');
            @endif
            $("#example1").DataTable({
                "pageLength": 10,
                "order": [[1, "desc"], [0, "desc"]],
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
