@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Artículos</li>
            <li class="breadcrumb-item active" aria-current="page">Eliminar Artículo</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h2 class="card-title"><b>Eliminar Artículo</b></h2>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <div class="mb-3">
                        <label for="rubro_id" class="form-label">Rubro</label>
                        <input type="text" class="form-control" id="rubro_id" name="rubro_id"
                            value="{{ $articulo->rubro->nombre ?? 'Sin rubro' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                            value="{{ $articulo->nombre }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock"
                            value="{{ $articulo->stock }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="precio" name="precio"
                            value="{{ $articulo->precio }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="activo" class="form-label">Estado</label>
                        <input type="text" class="form-control" id="activo" name="activo"
                            value="{{ $articulo->activo ? 'Activo' : 'Inactivo' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="comanda" class="form-label">Comanda</label>
                        <input type="text" class="form-control" id="comanda" name="comanda"
                            value="{{ $articulo->comanda ? 'Sí' : 'No' }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea class="form-control" id="observacion" name="observacion" rows="2" readonly>{{ $articulo->observacion }}</textarea>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <form action="{{ url('/admin/articulos/' . $articulo->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <a href="{{ url('/admin/articulos/index') }}" class="btn btn-secondary">Cancelar</a>
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
