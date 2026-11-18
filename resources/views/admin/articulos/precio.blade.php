@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Artículos</li>
            <li class="breadcrumb-item active" aria-current="page">Modificación de Precio</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-9">
            <div class="card card-info">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ route('articulos.grabar-precio', $articulo->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subrubro_id" class="form-label">Subrubro</label>
                                <input type="text" class="form-control" id="subrubro_id" name="subrubro_id" value="{{ $articulo->subrubro->nombre }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $articulo->nombre }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="precio" class="form-label">Precio <b style="color: red;">(*)</b></label>
                                <input class="form-control" type="number" id="precio" name="precio" min="0.01" max="9999999999.99" step="0.01" value="{{ old('precio', $articulo->primerStock?->precioVta) }}" required>
                                @error('precio') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <hr>
                        <div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <a href="{{ url('/admin/articulos/index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-info">Modificar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
