@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Proveedores</li>
            <li class="breadcrumb-item active" aria-current="page">Ver Proveedor</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Detalle del proveedor</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" value="{{ $proveedor->nombre }}" readonly>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="razon_social" class="form-label">Razón Social</label>
                            <input type="text" class="form-control" id="razon_social" value="{{ $proveedor->razon_social }}" readonly>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="cuit" class="form-label">C.U.I.T.</label>
                            <input type="text" class="form-control" id="cuit" value="{{ $proveedor->cuit }}" readonly>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="tipo_iva" class="form-label">Tipo I.V.A.</label>
                            <input type="text" class="form-control" id="tipo_iva" value="{{ $proveedor->tipo_iva }}" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <input type="text" class="form-control" id="domicilio" value="{{ $proveedor->domicilio }}" readonly>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="provincia" class="form-label">Provincia</label>
                            <input type="text" class="form-control" id="provincia" value="{{ $proveedor->localidad->provincia ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <label for="localidad" class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="localidad" value="{{ $proveedor->localidad->localidad ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-2 col-sm-2">
                            <label for="cod_postal" class="form-label">Cod.Postal</label>
                            <input type="text" class="form-control" id="cod_postal" value="{{ $proveedor->localidad->cod_postal ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" value="{{ $proveedor->telefono ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="activo" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="activo" value="{{ $proveedor->activo ? 'Activo' : 'Inactivo' }}" readonly>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" rows="2" readonly>{{ $proveedor->observacion ?? '' }}</textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <a href="{{ url('/admin/proveedores/index') }}" class="btn btn-success">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
