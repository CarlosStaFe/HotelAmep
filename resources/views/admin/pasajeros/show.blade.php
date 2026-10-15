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
        <div class="col-md-9">
            <div class="card card-success">
                <div class="card-header">
                    <h2 class="card-title"><b>Detalle del pasajero</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="tipoDocumento" class="form-label">Tipo de documento</label>
                            <input type="text" class="form-control" id="tipoDocumento" name="tipoDocumento" value="{{ $pasajero->tipoDocumento }}" readonly>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="documento" class="form-label">Documento</label>
                            <input type="text" class="form-control" id="documento" name="documento" value="{{ $pasajero->documento }}" readonly>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="apelyNombre" class="form-label">Apellido y Nombres</label>
                            <input type="text" class="form-control" id="apelyNombre" name="apelyNombre" value="{{ $pasajero->apelyNombre }}" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="{{ $pasajero->telefono }}" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="direccion" class="form-label">Domicilio</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" value="{{ $pasajero->direccion }}" readonly>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="provincia" class="form-label">Provincia</label>
                            <input type="text" class="form-control" id="provincia" value="{{ $pasajero->localidad->provincia ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="localidad" class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="localidad" value="{{ $pasajero->localidad->localidad ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="cod_postal" class="form-label">Cod.Postal</label>
                            <input type="text" class="form-control" id="cod_postal" value="{{ $pasajero->localidad->cod_postal ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $pasajero->email }}" readonly>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="usaComanda" class="form-label">Usa Comanda</label>
                            <input type="text" class="form-control" id="usaComanda" value="{{ $pasajero->usaComanda ? 'Sí' : 'No' }}" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="{{ url('/admin/pasajeros/index') }}" class="btn btn-secondary">Volver</a>
                        <a href="{{ route('pasajeros.edit', $pasajero->id) }}" class="btn btn-success">Editar</a>
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
