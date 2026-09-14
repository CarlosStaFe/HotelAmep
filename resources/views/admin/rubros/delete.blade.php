@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rubros</li>
            <li class="breadcrumb-item active" aria-current="page">Eliminar Rubro</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h2 class="card-title"><b>Eliminar Rubro</b></h2>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <div class="mb-3">
                        <label for="ctrocosto_id" class="form-label">Centro de Costo <b style="color: red;">(*)</b></label>
                        <input type="text" class="form-control" id="ctrocosto_id" name="ctrocosto_id"
                            value="{{ $rubro->ctrocosto->nombre ?? 'Sin centro de costo' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                            value="{{ $rubro->nombre }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="activo" class="form-label">Estado</label>
                        <input type="text" class="form-control" id="activo" name="activo"
                            value="{{ $rubro->activo ? 'Activo' : 'Inactivo' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea class="form-control" id="observacion" name="observacion" rows="2" readonly>{{ $rubro->observacion }}</textarea>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <form action="{{ url('/admin/rubros/' . $rubro->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <a href="{{ url('/admin/rubros/index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
