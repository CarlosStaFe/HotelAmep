@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pasajeros</li>
            <li class="breadcrumb-item active" aria-current="page">Ver Pasajero</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Detalle del pasajero</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label for="tipo_documento" class="form-label">Tipo de documento</label>
                            <input type="text" id="tipo_documento" value="{{ old('tipo_documento') }}" readonly>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="documento" class="form-label">Documento</label>
                            <input type="text" id="documento" name="documento" value="{{ old('documento') }}" readonly>
                        </div>
                        <div class="col-md-5 mb-5">
                            <label for="apelynombre" class="form-label">Apellido y Nombres</label>
                            <input type="text" id="apelynombre" name="apelynombre" value="{{ old('apelynombre') }}" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="domicilio" class="form-label">Domicilio</label>
                            <input type="text" class="form-control" id="domicilio" name="domicilio" value="{{ old('domicilio') }}" readonly>
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
                        <div class="col-md-4 col-sm-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <a href="{{ url('/admin/pasajeros/index') }}" class="btn btn-success">Volver</a>
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
