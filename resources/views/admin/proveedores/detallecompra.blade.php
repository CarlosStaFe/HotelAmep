@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('compras.index') }}">Compras</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalle de compra</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><b>Detalle de compra Nro. {{ $compra->id }}</b></h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($compra->fechaCpra)->format('d/m/Y') }}</div>
                <div class="col-md-3"><strong>Proveedor:</strong> {{ $compra->proveedor_nombre ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Razón Social:</strong> {{ $compra->proveedor_razon_social ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Comprobante:</strong> {{ $compra->tipoCpbte }} {{ $compra->prefijo }}-{{ $compra->subfijo }}</div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th>Artículo</th>
                            <th class="text-right">Cantidad</th>
                            <th class="text-right">Precio unitario</th>
                            <th class="text-right">Subtotal</th>
                            <th>Vencimiento</th>
                            <th>Punto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->articulo_nombre ?? 'Artículo no disponible (ID: '.$detalle->articulo_id.')' }}</td>
                                <td class="text-right">{{ number_format((float) $detalle->cantidad, 3, '.', ',') }}</td>
                                <td class="text-right">{{ number_format((float) $detalle->precio, 2, '.', ',') }}</td>
                                <td class="text-right">{{ number_format((float) $detalle->subtotal, 2, '.', ',') }}</td>
                                <td>{{ $detalle->vence ? \Carbon\Carbon::parse($detalle->vence)->format('d/m/Y') : 'Sin vencimiento' }}</td>
                                <td>{{ $detalle->punto }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Esta compra no tiene detalles.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-right"><strong>Total: {{ number_format((float) $compra->total, 2, '.', ',') }}</strong></p>
            <a href="{{ route('compras.index') }}" class="btn btn-secondary">Volver</a>
        </div>
    </div>
@stop
