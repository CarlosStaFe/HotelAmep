@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Proveedores</li>
            <li class="breadcrumb-item active" aria-current="page">Crear Proveedor</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h2 class="card-title"><b>Completar el formulario</b></h2>
                </div>
                <div class="card-body" style="box-sizing: border-box; display: block;">
                    <form action="{{ url('/admin/proveedores/create') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="nombre" class="form-label">Nombre <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    placeholder="Ingrese nombre del proveedor" value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="razonSocial" class="form-label">Razón Social <b
                                        style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="razonSocial" name="razonSocial"
                                    placeholder="Ingrese razón social del proveedor" value="{{ old('razonSocial') }}"
                                    required>
                                @error('razonSocial')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="cuit" class="form-label">C.U.I.T. <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="cuit" name="cuit"
                                    placeholder="Ingrese C.U.I.T. del proveedor" value="{{ old('cuit') }}" required>
                                @error('cuit')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="form-group">
                                    <label for="tipoIva">Tipo I.V.A. <b style="color: red;">(*)</b></label>
                                    <select class="form-control" id="tipoIva" name="tipoIva" required>
                                        <option selected disabled>Elige una tipo de I.V.A...</option>
                                        <option value="Consumidor Final" {{ old('tipoIva') == 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
                                        <option value="Exento" {{ old('tipoIva') == 'Exento' ? 'selected' : '' }}>Exento</option>
                                        <option value="Monotributo" {{ old('tipoIva') == 'Monotributo' ? 'selected' : '' }}>Monotributo</option>
                                        <option value="No Responsable" {{ old('tipoIva') == 'No Responsable' ? 'selected' : '' }}>No Responsable</option>
                                        <option value="Resp. Inscripto" {{ old('tipoIva') == 'Resp. Inscripto' ? 'selected' : '' }}>Resp. Inscripto</option>
                                    </select>
                                    @error('tipoIva')
                                        <small style="color: red">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="domicilio" class="form-label">Domicilio <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="domicilio" name="domicilio"
                                    placeholder="Ingrese domicilio del proveedor" value="{{ old('domicilio') }}" required>
                                @error('domicilio')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
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
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="telefono" class="form-label">Teléfono <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="telefono" name="telefono" required
                                    placeholder="Ingrese teléfono del proveedor" value="{{ old('telefono') }}">
                                @error('telefono')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="email" class="form-label">Email <b style="color: red;">(*)</b></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    placeholder="Ingrese email del proveedor" value="{{ old('email') }}">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="activo" class="form-label">Estado <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="activo" name="activo" required>
                                    <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="observacion" class="form-label">Observación</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="2"
                                    placeholder="Ingrese observación del artículo">{{ old('observacion') }}</textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label for="echeck" class="form-label">Echeck</label>
                                <select class="form-control" id="echeck" name="echeck">
                                    <option value="0" {{ old('echeck', '0') === '0' ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('echeck') === '1' ? 'selected' : '' }}>Sí</option>
                                </select>
                                @error('echeck')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="cbu" class="form-label">CBU</label>
                                <input type="text" class="form-control" id="cbu" name="cbu"
                                    placeholder="Ingrese CBU del proveedor" value="{{ old('cbu') }}">
                                @error('cbu')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="cvu" class="form-label">CVU</label>
                                <input type="text" class="form-control" id="cvu" name="cvu"
                                    placeholder="Ingrese CVU del proveedor" value="{{ old('cvu') }}">
                                @error('cvu')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="alias" class="form-label">Alias</label>
                                <input type="text" class="form-control" id="alias" name="alias"
                                    placeholder="Ingrese alias del proveedor" value="{{ old('alias') }}">
                                @error('alias')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-1 mb-3">
                                <label for="dias" class="form-label">Días Pago <b style="color: red;">(*)</b></label>
                                <input type="number" class="form-control" id="dias" name="dias" required
                                    placeholder="Ingrese días del proveedor" value="{{ old('dias') }}">
                                @error('dias')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>                            
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ url('/admin/proveedores/index') }}"
                                        class="btn btn-secondary">Cancelar</a>
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
