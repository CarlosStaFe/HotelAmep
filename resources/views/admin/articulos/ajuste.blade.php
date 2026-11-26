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
                                <label for="subrubro_id" class="form-label">Subrubro</label>
                                <input type="text" class="form-control" id="subrubro_id" name="subrubro_id" value="{{ $articulo->subrubro->nombre }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $articulo->nombre }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label for="tipo" class="form-label">Tipo de Movimiento <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="tipo" name="tipo" required>
                                    <option value="">Seleccione tipo</option>
                                    <option value="Entrada" {{ old('tipo') === 'Entrada' ? 'selected' : '' }}>Entrada</option>
                                    <option value="Salida" {{ old('tipo') === 'Salida' ? 'selected' : '' }}>Salida</option>
                                </select>
                                @error('tipo') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="cantidad" class="form-label">Cantidad <b style="color: red;">(*)</b></label>
                                <input class="form-control" type="number" id="cantidad" name="cantidad" min="0.001" step="0.001" value="{{ old('cantidad') }}" required>
                                @error('cantidad') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="punto" class="form-label">Punto <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="punto" name="punto" required>
                                    <option value="">Seleccione punto</option>
                                    <option value="Deposito" data-sin-stock="{{ $stocksPorPunto['Deposito'] == 0 ? '1' : '0' }}" {{ old('punto', 'Deposito') === 'Deposito' ? 'selected' : '' }}>Deposito</option>
                                    <option value="Comedor" data-sin-stock="{{ $stocksPorPunto['Comedor'] == 0 ? '1' : '0' }}" {{ old('punto', 'Deposito') === 'Comedor' ? 'selected' : '' }}>Comedor</option>
                                    <option value="Bar" data-sin-stock="{{ $stocksPorPunto['Bar'] == 0 ? '1' : '0' }}" {{ old('punto', 'Deposito') === 'Bar' ? 'selected' : '' }}>Bar</option>
                                    <option value="Recepción" data-sin-stock="{{ $stocksPorPunto['Recepción'] == 0 ? '1' : '0' }}" {{ old('punto', 'Deposito') === 'Recepción' ? 'selected' : '' }}>Recepción</option>
                                    <option value="Producción" data-sin-stock="{{ $stocksPorPunto['Producción'] == 0 ? '1' : '0' }}" {{ old('punto', 'Deposito') === 'Producción' ? 'selected' : '' }}>Producción</option>
                                </select>
                                @php
                                    $puntoSeleccionado = old('punto', 'Deposito');
                                    $sinStockSeleccionado = in_array($puntoSeleccionado, ['Deposito', 'Comedor', 'Bar', 'Recepción', 'Producción'], true)
                                        && $stocksPorPunto[$puntoSeleccionado] == 0;
                                @endphp
                                <small id="stockAviso" class="text-warning" role="status" @if (!$sinStockSeleccionado) hidden @endif>El stock a ajustar en este punto es 0.</small>
                                @error('punto') <small class="text-danger">{{ $message }}</small> @enderror
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
                                    <option value="Producción" {{ old('motivo') === 'Producción' ? 'selected' : '' }}>Producción</option>
                                </select>
                                @error('motivo') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div>
                            <div class="mb-5">
                                <label for="observacion" class="form-label">Observación del ajuste <b style="color: red;">(*)</b></label>
                                <textarea class="form-control" id="observacion" name="observacion" required>{{ old('observacion') }}</textarea>
                                <small>El tipo, el motivo y la observación se guardan juntos, con un máximo de 100 caracteres.</small>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const punto = document.getElementById('punto');
            const aviso = document.getElementById('stockAviso');

            function actualizarAviso() {
                const opcion = punto.options[punto.selectedIndex];
                aviso.hidden = !opcion || opcion.dataset.sinStock !== '1';
            }

            punto.addEventListener('change', actualizarAviso);
            actualizarAviso();
        });
    </script>
@stop
