@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
            <li class="breadcrumb-item active" aria-current="page">Crear Usuario</li>
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
                    <form action="{{ url('/admin/usuarios/create') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label for="role" class="form-label">Rol <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Seleccione Rol</option>
                                    @foreach (\Spatie\Permission\Models\Role::where('guard_name', 'web')->orderBy('name')->get() as $role)
                                        <option value="{{ $role->name }}"
                                            {{ old('role') === $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="nombre" class="form-label">Nombre Usuario <b style="color: red;">(*)</b></label>
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    placeholder="Ingrese nombre del usuario" value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <b style="color: red;">(*)</b></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Ingrese el email del usuario" value="{{ old('email') }}" required>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="password" class="form-label">Contraseña <b style="color: red;">(*)</b></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Ingrese una contraseña" required>
                                    <button type="button" class="btn btn-outline-secondary" id="generate-password"
                                        title="Generar contraseña aleatoria">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar contraseña <b style="color: red;">(*)</b></label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Repita la contraseña" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="activo" class="form-label">Estado <b style="color: red;">(*)</b></label>
                                <select class="form-control" id="activo" name="activo" required>
                                    <option value="1" {{ old('activo', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    <div class="row">
                    </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <a href="{{ url('/admin/usuarios/index') }}" class="btn btn-secondary">Cancelar</a>
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
        document.getElementById('generate-password').addEventListener('click', function () {
            const characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
            let password = '';

            for (let index = 0; index < 12; index++) {
                password += characters.charAt(Math.floor(Math.random() * characters.length));
            }

            document.getElementById('password').value = password;
            document.getElementById('password_confirmation').value = password;
        });
    </script>
@stop
