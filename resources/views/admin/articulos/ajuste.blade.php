@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Artículos</li>
            <li class="breadcrumb-item active" aria-current="page">Ajuste de Artículo</li>
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
                    <form action="{{ url('/admin/articulos/' . $articulo->id . '/grabar-ajuste') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ctrocosto_id" class="form-label">Rubro</label>
                                <input type="text" class="form-control" id="rubro_id" name="rubro_id" value="{{ $articulo->rubro->nombre }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $articulo->nombre }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label for="tipo" class="form-label">Tipo de Movimiento <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="tipo" name="tipo" required>
                                    <option value="">Seleccione tipo</option>
                                    <option value="Entrada">Entrada</option>
                                    <option value="Salida">Salida</option>
                                </select>
                                @error('tipo') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="cantidad" class="form-label">Cantidad <b style="color: red;">(*)</b></label>
                                <input class="form-control" type="number" id="cantidad" name="cantidad" min="0.001" step="0.001" value="{{ old('cantidad') }}" required>
                                @error('cantidad') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="motivo" class="form-label">Motivo <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="motivo" name="motivo" required>
                                    <option value="">Seleccione motivo</option>
                                    <option value="Ajuste" {{ old('motivo', 'Ajuste') === 'Ajuste' ? 'selected' : '' }}>Ajuste</option>
                                    <option value="Rotura" {{ old('motivo') === 'Rotura' ? 'selected' : '' }}>Rotura</option>
                                    <option value="Perdida" {{ old('motivo') === 'Perdida' ? 'selected' : '' }}>Perdida</option>
                                    <option value="Vencido" {{ old('motivo') === 'Vencido' ? 'selected' : '' }}>Vencido</option>
                                    <option value="Desgaste" {{ old('motivo') === 'Desgaste' ? 'selected' : '' }}>Desgaste</option>
                                    <option value="Baja" {{ old('motivo') === 'Baja' ? 'selected' : '' }}>Baja</option>
                                </select>
                                @error('motivo') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div>
                            <div class="mb-5">
                                <label for="observacion" class="form-label">Observación del ajuste <b style="color: red;">(*)</b></label>
                                <textarea class="form-control" id="observacion" name="observacion" required>{{ old('observacion') }}</textarea>
                                @error('observacion') <small class="text-danger">{{ $message }}</small> @enderror
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
