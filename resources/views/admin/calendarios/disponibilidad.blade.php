@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Calendario</li>
            <li class="breadcrumb-item active" aria-current="page">Disponibilidad de Habitaciones</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <form action="{{ route('calendarios.store') }}" method="POST">
                @csrf
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Generar Disponibilidad</h3>
                    </div>
                    <br>
                    <div class="row mb-3">
                        <div class="ml-1 col-md-2">
                            <label for="habitacion_id">HABITACIÓN <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="habitacion_id" name="habitacion_id" required>
                                <option value="">Seleccione una habitación</option>
                                @foreach($habitaciones as $habitacion)
                                    <option value="{{ $habitacion->id }}" @selected(old('habitacion_id') == $habitacion->id)>
                                        {{ $habitacion->numero }} - {{ $habitacion->tipo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('habitacion_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_inicio">FECHA INICIO <b style="color: red;">(*)</b></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                   value="{{ old('fecha_inicio', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                            @error('fecha_inicio')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="fecha_fin">FECHA FIN <b style="color: red;">(*)</b></label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                                   value="{{ old('fecha_fin', date('Y-m-d', strtotime('+30 days'))) }}" min="{{ date('Y-m-d') }}" required>
                            @error('fecha_fin')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="estado">ESTADO <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="estado" name="estado" required>
                                @foreach(['Disponible', 'No Disponible', 'Reservada', 'Ocupada'] as $estado)
                                    <option value="{{ $estado }}" @selected(old('estado', 'Disponible') === $estado)>
                                        {{ $estado }}
                                    </option>
                                @endforeach
                            </select>
                            @error('estado')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 ml-1">
                            <div class="form-group">
                                <a href="{{ url('/admin/calendarios/disponibilidad') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Generar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
