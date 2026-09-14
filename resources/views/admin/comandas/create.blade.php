@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Comandas</li>
            <li class="breadcrumb-item active" aria-current="page">Cargar Consumo</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-9">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ url('/admin/comandas/create') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="habitacion" class="form-label">Habitación <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="habitacion" name="habitacion"
                                    placeholder="Ingrese número de habitación" value="{{ old('habitacion') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pasajero" class="form-label">Pasajero <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="pasajero_id" name="pasajero_id" required>
                                    <option value="">Seleccione pasajero</option>
                                </select>
                            </div>
                        </div>
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock"
                                placeholder="Ingrese stock del artículo" value="{{ old('stock') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio"
                                placeholder="Ingrese precio del artículo" value="{{ old('precio') }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="activo" class="form-label">Estado <b style="color: red;">(*)</b></label>
                            <select class="form-control" id="activo" name="activo" required>
                                <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-5">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="2"
                                placeholder="Ingrese observación del artículo">{{ old('observacion') }}</textarea>
                        </div>
                    </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ url('/admin/articulos/index') }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
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
            const habitacionInput = document.getElementById('habitacion');
            const pasajeroSelect = document.getElementById('pasajero_id');
            const oldHabitacion = @json(old('habitacion'));
            const oldPasajero = @json(old('pasajero_id'));

            const setDefault = () => {
                pasajeroSelect.innerHTML = '<option value="">Seleccione pasajero</option>';
            };

            const cargarPasajeros = async (habitacion, pasajeroSeleccionado = '') => {
                setDefault();

                if (!habitacion) {
                    return;
                }

                try {
                    const endpoint = "{{ route('pasajeros.habitacion', ['habitacion' => '__HAB__']) }}".replace('__HAB__', encodeURIComponent(habitacion));
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const pasajeros = await response.json();

                    pasajeros.forEach((pasajero) => {
                        const option = document.createElement('option');
                        option.value = pasajero.id;
                        option.textContent = pasajero.nombre;

                        if (pasajeroSeleccionado && String(pasajeroSeleccionado) === String(pasajero.id)) {
                            option.selected = true;
                        }

                        pasajeroSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error al cargar pasajeros por habitacion:', error);
                }
            };

            habitacionInput.addEventListener('change', function() {
                cargarPasajeros(this.value);
            });

            habitacionInput.addEventListener('blur', function() {
                if (this.value) {
                    cargarPasajeros(this.value);
                }
            });

            if (oldHabitacion) {
                habitacionInput.value = oldHabitacion;
                cargarPasajeros(oldHabitacion, oldPasajero);
            }
        });
    </script>
@stop
