@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pasajeros</li>
            <li class="breadcrumb-item active" aria-current="page">Crear Pasajero</li>
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
                    <form action="{{ url('/admin/pasajeros/create') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label for="tipo_documento" class="form-label">Tipo de documento <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Seleccione tipo de documento</option>
                                    <option value="DNI" {{ old('tipo_documento', 'DNI') === 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="CI" {{ old('tipo_documento') === 'CI' ? 'selected' : '' }}>CI</option>
                                    <option value="PAS" {{ old('tipo_documento') === 'PAS' ? 'selected' : '' }}>PAS</option>
                                    <option value="LE" {{ old('tipo_documento') === 'LE' ? 'selected' : '' }}>LE</option>
                                </select>
                                @error('tipo_documento')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="documento" class="form-label">Documento <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="documento" name="documento"
                                    placeholder="Ingrese documento del pasajero" value="{{ old('documento') }}" required>
                                @error('documento')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-5 mb-5">
                                <label for="apelynombre" class="form-label">Apellido y Nombres <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="apelynombre" name="apelynombre"
                                    placeholder="Ingrese apellido y nombre del pasajero" value="{{ old('apelynombre') }}" required>
                                @error('apelynombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="telefono" class="form-label">Teléfono <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="telefono" name="telefono"
                                    placeholder="Ingrese teléfono del pasajero" value="{{ old('telefono') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label for="domicilio" class="form-label">Domicilio <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="domicilio" name="domicilio"
                                    placeholder="Ingrese domicilio del proveedor" value="{{ old('domicilio') }}" required>
                                @error('domicilio')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-2">
                                <div class="form-group">
                                    <label for="provincia">Provincia <b style="color: red;">(*)</b></label>
                                    <select class="form-control" id="provincia" name="provincia" required>
                                        <option selected disabled>Elige provincia...</option>
                                        <option value="6">BUENOS AIRES</option>
                                        <option value="2">CABA</option>
                                        <option value="10">CATAMARCA</option>
                                        <option value="22">CHACO</option>
                                        <option value="26">CHUBUT</option>
                                        <option value="18">CORRIENTES</option>
                                        <option value="14">CORDOBA</option>
                                        <option value="30">ENTRE RIOS</option>
                                        <option value="34">FORMOSA</option>
                                        <option value="38">JUJUY</option>
                                        <option value="42">LA PAMPA</option>
                                        <option value="46">LA RIOJA</option>
                                        <option value="50">MENDOZA</option>
                                        <option value="54">MISIONES</option>
                                        <option value="58">NEUQUEN</option>
                                        <option value="62">RIO NEGRO</option>
                                        <option value="70">SAN JUAN</option>
                                        <option value="74">SAN LUIS</option>
                                        <option value="66">SALTA</option>
                                        <option value="78">SANTA CRUZ</option>
                                        <option value="82">SANTA FE</option>
                                        <option value="86">SANTIAGO DEL ESTERO</option>
                                        <option value="94">TIERRA DEL FUEGO</option>
                                        <option value="90">TUCUMAN</option>
                                    </select>
                                    @error('provincia')
                                        <small style="color: red">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <div class="form-group">
                                    <label for="localidad">Localidad <b style="color: red;">(*)</b></label>
                                    <select class="form-control" id="localidad" name="localidad" required>
                                    </select>
                                    @error('localidad')
                                        <small style="color: red">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <div class="form-group">
                                    <label for="cod_postal_id">Cod.Postal <b style="color: red;">(*)</b></label>
                                    <select class="form-control" id="cod_postal_id" name="cod_postal_id" required>
                                    </select>
                                    @error('cod_postal_id')
                                        <small style="color: red">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4">
                                <label for="email" class="form-label">Email <b style="color: red;">(*)</b></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Ingrese email del pasajero" value="{{ old('email') }}" required>
                            </div>
                        </div>
                    <div class="row">
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
            const provinciaSelect = document.getElementById('provincia');
            const localidadSelect = document.getElementById('localidad');
            const codPostalSelect = document.getElementById('cod_postal_id');

            const oldProvincia = @json(old('provincia'));
            const oldLocalidad = @json(old('localidad'));
            const oldCodPostal = @json(old('cod_postal_id'));

            const setDefaultOption = (select, text) => {
                select.innerHTML = `<option value="">${text}</option>`;
            };

            const cargarLocalidades = async (idProv, localidadSeleccionada = '') => {
                setDefaultOption(localidadSelect, 'Seleccione localidad');
                setDefaultOption(codPostalSelect, 'Seleccione codigo postal');

                if (!idProv) {
                    return;
                }

                try {
                    const endpoint = "{{ route('localidades.provincia', ['idProv' => '__ID__']) }}".replace('__ID__', encodeURIComponent(idProv));
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const localidades = await response.json();

                    localidades.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item.id_local;
                        option.textContent = item.localidad;

                        if (localidadSeleccionada && String(localidadSeleccionada) === String(item.id_local)) {
                            option.selected = true;
                        }

                        localidadSelect.appendChild(option);
                    });

                    if (localidadSelect.value) {
                        await cargarCodigosPostales(localidadSelect.value, oldCodPostal);
                    }
                } catch (error) {
                    console.error('Error al cargar localidades:', error);
                }
            };

            const cargarCodigosPostales = async (idLocal, codPostalSeleccionado = '') => {
                setDefaultOption(codPostalSelect, 'Seleccione codigo postal');

                if (!idLocal) {
                    return;
                }

                try {
                    const endpoint = "{{ route('localidades.codigos_postales', ['idLocal' => '__ID__']) }}".replace('__ID__', encodeURIComponent(idLocal));
                    const response = await fetch(endpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const codigos = await response.json();

                    codigos.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.cod_postal;

                        if (codPostalSeleccionado && String(codPostalSeleccionado) === String(item.id)) {
                            option.selected = true;
                        }

                        codPostalSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error al cargar codigos postales:', error);
                }
            };

            provinciaSelect.addEventListener('change', function() {
                cargarLocalidades(this.value);
            });

            localidadSelect.addEventListener('change', function() {
                cargarCodigosPostales(this.value);
            });

            if (oldProvincia) {
                provinciaSelect.value = oldProvincia;
                cargarLocalidades(oldProvincia, oldLocalidad);
            } else {
                setDefaultOption(localidadSelect, 'Seleccione localidad');
                setDefaultOption(codPostalSelect, 'Seleccione codigo postal');
            }
        });
    </script>
@stop
