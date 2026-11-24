@extends('adminlte::page')

@section('content_header')
    <nav aria-label="breadcrumb" style="font-size: 14pt">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/admin') }}">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Comandas</li>
            <li class="breadcrumb-item active" aria-current="page">Listar Consumo</li>
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
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><b>Consumos registrados</b></h3>
                </div>
                <div class="card-body">
                    <table id="tablaComandas" class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr style="background-color: #007bff; color: white; text-align: center;">
                                <th>Nro.</th>
                                <th>Fecha y hora</th>
                                <th>Habitación</th>
                                <th>Punto de venta</th>
                                <th>Pasajero</th>
                                <th>Artículo</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Importe</th>
                                <th>Moza</th>
                                <th>Estado</th>
                                <th>Observación</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($comandas as $comanda)
                                <tr data-pasajero-id="{{ $comanda->pasajero_id }}">
                                    <td class="text-right">{{ $comanda->id }}</td>
                                    <td>{{ $comanda->fecha }}</td>
                                    <td>{{ $comanda->habitacion }}</td>
                                    <td>{{ $comanda->puntoVenta }}</td>
                                    <td>{{ $comanda->pasajero }}</td>
                                    <td>{{ $comanda->articulo }}</td>
                                    <td class="text-right">{{ $comanda->cantidad }}</td>
                                    <td class="text-right" data-order="{{ $comanda->precio }}">{{ number_format($comanda->precio, 2, '.', ',') }}</td>
                                    <td class="text-right" data-order="{{ $comanda->total }}">{{ number_format($comanda->total, 2, '.', ',') }}</td>
                                    <td>{{ $comanda->moza }}</td>
                                    <td>{{ $comanda->estado }}</td>
                                    <td>{{ $comanda->observacion }}</td>
                                    <td>{{ $comanda->usuario }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-12 mt-4">
                @if (session('comanda_cerrada'))
                    <div class="alert alert-success" role="status">{{ session('comanda_cerrada') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div id="comandaMensajeContenedor" hidden>
                    <button type="button" id="cerrarComandaMensaje" class="close" aria-label="Cerrar mensaje"><span aria-hidden="true">&times;</span></button>
                    <span id="comandaMensaje" role="status" aria-live="polite"></span>
                </div>
                <form action="{{ route('comandas.cerrar') }}" method="POST">
                    @csrf
                <div class="d-flex flex-wrap align-items-end mb-3">
                    <div class="mr-3">
                        <label for="habitacionCerrar">Habitación</label>
                        <select id="habitacionCerrar" name="habitacion" class="form-control">
                            <option value="">Seleccione habitación</option>
                            @foreach ($habitacionesAbiertas as $habitacion)
                                <option value="{{ $habitacion }}" {{ old('habitacion') === (string) $habitacion ? 'selected' : '' }}>{{ $habitacion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ml-3">
                        <label for="totalComandaAbierta">Total comanda</label>
                        <input type="text" id="totalComandaAbierta" class="form-control" readonly>
                    </div>
                    <div class="ml-3">
                        <label for="efectivo">Efectivo</label>
                        <input type="text" id="efectivo" name="efectivo" class="form-control" inputmode="decimal" aria-describedby="pagosMensaje" value="{{ old('efectivo') }}">
                    </div>
                    <div class="ml-3">
                        <label for="transferencia">Transferencia</label>
                        <input type="text" id="transferencia" name="transferencia" class="form-control" inputmode="decimal" aria-describedby="pagosMensaje" value="{{ old('transferencia') }}">
                    </div>
                    <div class="ml-3">
                        <label for="tarjeta">Tarjeta</label>
                        <input type="text" id="tarjeta" name="tarjeta" class="form-control" inputmode="decimal" aria-describedby="pagosMensaje" value="{{ old('tarjeta') }}">
                    </div>
                    <div class="ml-3">
                        <label for="total">Total Pagado</label>
                        <input type="text" id="total" name="total" class="form-control" value="0.00" readonly>
                    </div>
                    <div class="ml-3">
                        <button type="button" id="listarComanda" class="btn btn-info">Listar</button>
                    </div>
                    <div class="ml-3">
                        <button type="submit" id="cerrarComanda" class="btn btn-warning">Cerrar</button>
                    </div>
                </div>
                <small id="pagosMensaje" class="text-danger" role="status" aria-live="polite"></small>
                </form>
            </div>

        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            const compararHabitaciones = (a, b) => a.localeCompare(b, 'es', { numeric: true }) || (a < b ? -1 : a > b ? 1 : 0);
            $.fn.dataTable.ext.type.order['habitacion-asc'] = compararHabitaciones;
            $.fn.dataTable.ext.type.order['habitacion-desc'] = (a, b) => compararHabitaciones(b, a);
            const aCentavos = valor => {
                const texto = valor.trim().replace(/,/g, '');
                const negativo = texto.startsWith('-');
                const [entero, decimal = ''] = (negativo ? texto.slice(1) : texto).split('.');
                const centavos = BigInt(entero) * 100n + BigInt(decimal.padEnd(2, '0'));
                return negativo ? -centavos : centavos;
            };
            const formatearTotal = valor => {
                const absoluto = valor < 0n ? -valor : valor;
                const entero = String(absoluto / 100n).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                return `${valor < 0n ? '-' : ''}${entero}.${String(absoluto % 100n).padStart(2, '0')}`;
            };

            const tablaComandas = $('#tablaComandas').DataTable({
                pageLength: 10,
                columnDefs: [{ targets: 2, type: 'habitacion' }],
                orderFixed: { pre: [[2, 'asc']] },
                order: [[2, 'asc'], [1, 'asc'], [0, 'asc']],
                drawCallback: function() {
                    const tabla = this.api();
                    const grupos = new Map();
                    tabla.rows({ search: 'applied', order: 'applied' }).nodes().toArray().forEach(fila => {
                        const habitacion = fila.cells[2].textContent;
                        const grupo = grupos.get(habitacion) || { total: 0n };
                        grupo.total += aCentavos(fila.cells[8].textContent);
                        grupo.ultimaFila = fila;
                        grupos.set(habitacion, grupo);
                    });
                    tabla.rows({ page: 'current' }).nodes().toArray().forEach(fila => {
                        const habitacion = fila.cells[2].textContent;
                        const grupo = grupos.get(habitacion);
                        if (grupo.ultimaFila !== fila) return;
                        const total = grupo.total;
                        const renglon = document.createElement('tr');
                        renglon.className = 'table-info font-weight-bold';
                        const etiqueta = document.createElement('td');
                        etiqueta.colSpan = 8;
                        etiqueta.className = 'text-right';
                        etiqueta.textContent = `Total habitación ${habitacion}`;
                        const importe = document.createElement('td');
                        importe.className = 'text-right';
                        importe.textContent = formatearTotal(total);
                        const resto = document.createElement('td');
                        resto.colSpan = 4;
                        renglon.append(etiqueta, importe, resto);
                        fila.after(renglon);
                    });
                },
                language: {
                    emptyTable: 'No hay información',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ Registros',
                    infoEmpty: 'Mostrando 0 a 0 de 0 Registros',
                    infoFiltered: '(Filtrado de _MAX_ total Registros)',
                    lengthMenu: 'Mostrar _MENU_ Registros',
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                    search: 'Buscador:',
                    zeroRecords: 'Sin resultados encontrados',
                    paginate: {
                        first: 'Primero',
                        last: 'Último',
                        next: 'Siguiente',
                        previous: 'Anterior'
                    }
                },
                responsive: true,
                lengthChange: true,
                autoWidth: false
            });

            const habitacionCerrar = document.getElementById('habitacionCerrar');
            const totalComandaAbierta = document.getElementById('totalComandaAbierta');
            const mensaje = document.getElementById('comandaMensaje');
            const contenedorMensaje = document.getElementById('comandaMensajeContenedor');
            function actualizarTotalComanda() {
                const habitacion = habitacionCerrar.value;
                if (!habitacion) {
                    totalComandaAbierta.value = '';
                    return;
                }
                const filas = tablaComandas.rows({ search: 'none' }).nodes().toArray()
                    .filter(fila => fila.cells[2].textContent === habitacion && fila.cells[10].textContent === 'Abierta');
                const suma = filas.reduce((total, fila) => total + aCentavos(fila.cells[8].textContent), 0n);
                totalComandaAbierta.value = formatearTotal(suma);
            }
            habitacionCerrar.addEventListener('change', actualizarTotalComanda);
            actualizarTotalComanda();
            function mostrarMensaje(texto) {
                mensaje.textContent = texto;
                contenedorMensaje.className = 'alert alert-warning';
                contenedorMensaje.hidden = false;
            }
            document.getElementById('cerrarComandaMensaje').addEventListener('click', function() {
                contenedorMensaje.hidden = true;
                mensaje.textContent = '';
            });
            const listarComanda = document.getElementById('listarComanda');
            listarComanda.addEventListener('click', async function() {
                const habitacion = habitacionCerrar.value;
                if (!habitacion) {
                    mostrarMensaje('Seleccione una habitación.');
                    return;
                }
                if (typeof pdfMake === 'undefined' || typeof pdfMake.createPdf !== 'function') {
                    mostrarMensaje('No se pudo cargar la herramienta para generar el PDF.');
                    return;
                }
                const filas = tablaComandas.rows({ search: 'none' }).nodes().toArray()
                    .filter(fila => fila.cells[2].textContent === habitacion && fila.cells[10].textContent === 'Abierta')
                    .sort((a, b) => a.cells[4].textContent.localeCompare(b.cells[4].textContent, 'es')
                        || a.dataset.pasajeroId.localeCompare(b.dataset.pasajeroId, 'es', { numeric: true })
                        || a.cells[1].textContent.localeCompare(b.cells[1].textContent)
                        || Number(a.cells[0].textContent) - Number(b.cells[0].textContent));
                if (!filas.length) {
                    mostrarMensaje('No hay comandas abiertas para la habitación seleccionada.');
                    return;
                }
                contenedorMensaje.hidden = true;
                mensaje.textContent = '';
                let total = 0n;
                let totalPasajero = 0n;
                let pasajeroActual = null;
                let nombrePasajero = '';
                const cuerpo = [[
                    'Pasajero', 'Fecha y hora', 'Punto', 'Artículo', 'Cantidad', 'Precio', 'Importe', 'Moza', 'Observación',
                ].map(text => ({ text, bold: true }))];
                const agregarTotal = (etiqueta, importe) => {
                    cuerpo.push([
                        { text: etiqueta, colSpan: 6, alignment: 'right', bold: true }, {}, {}, {}, {}, {},
                        { text: formatearTotal(importe), alignment: 'right', bold: true }, {}, {},
                    ]);
                };
                filas.forEach(fila => {
                    if (pasajeroActual !== fila.dataset.pasajeroId) {
                        if (pasajeroActual !== null) agregarTotal(`Total pasajero: ${nombrePasajero} (ID ${pasajeroActual})`, totalPasajero);
                        pasajeroActual = fila.dataset.pasajeroId;
                        nombrePasajero = fila.cells[4].textContent;
                        totalPasajero = 0n;
                    }
                    const importeCentavos = aCentavos(fila.cells[8].textContent);
                    total += importeCentavos;
                    totalPasajero += importeCentavos;
                    cuerpo.push([4, 1, 3, 5, 6, 7, 8, 9, 11].map(indice =>
                        [7, 8].includes(indice) ? formatearTotal(aCentavos(fila.cells[indice].textContent)) : fila.cells[indice].textContent
                    ));
                });
                agregarTotal(`Total pasajero: ${nombrePasajero} (ID ${pasajeroActual})`, totalPasajero);
                agregarTotal(`Total habitación ${habitacion}`, total);
                listarComanda.disabled = true;
                let logo;
                try {
                    const respuesta = await fetch(@json(asset('assets/img/pinarverde.png')));
                    if (!respuesta.ok) throw new Error(`Error HTTP ${respuesta.status}`);
                    const imagen = await respuesta.blob();
                    logo = await new Promise((resolve, reject) => {
                        const lector = new FileReader();
                        lector.onload = () => resolve(lector.result);
                        lector.onerror = () => reject(lector.error);
                        lector.readAsDataURL(imagen);
                    });
                } catch (error) {
                    console.error('No se pudo cargar el logo para el PDF:', error);
                    mostrarMensaje('No se pudo cargar el logo del hotel para generar el PDF.');
                    listarComanda.disabled = false;
                    return;
                }
                listarComanda.disabled = false;
                const ahora = new Date();
                const fechaGeneracion = new Intl.DateTimeFormat('es-AR', {
                    dateStyle: 'short', timeStyle: 'medium', hour12: false,
                }).format(ahora);
                const fechaArchivo = [
                    ahora.getFullYear(),
                    String(ahora.getMonth() + 1).padStart(2, '0'),
                    String(ahora.getDate()).padStart(2, '0'),
                ].join('-');
                const habitacionArchivo = habitacion.replace(/[<>:"/\\|?*\x00-\x1f]/g, '-');
                pdfMake.createPdf({
                    pageSize: 'A4',
                    pageOrientation: 'portrait',
                    pageMargins: [25, 30, 25, 30],
                    content: [
                        {
                            columns: [
                                { image: logo, width: 85 },
                                { text: 'Hotel Pinar del Río', style: 'hotel', margin: [10, 12, 0, 0] },
                                { text: `Generado: ${fechaGeneracion}`, alignment: 'right', margin: [0, 12, 0, 0] },
                            ],
                            margin: [0, 0, 0, 10],
                        },
                        { text: `Consumos - Habitación ${habitacion}`, style: 'titulo' },
                        { table: { headerRows: 1, widths: ['*', 'auto', 'auto', '*', 'auto', 'auto', 'auto', 'auto', '*'], body: cuerpo } },
                    ],
                    styles: {
                        hotel: { fontSize: 13, bold: true },
                        titulo: { fontSize: 16, bold: true, margin: [0, 0, 0, 10] },
                    },
                    defaultStyle: { fontSize: 7 },
                }).download(`comanda_${habitacionArchivo}_${fechaArchivo}.pdf`);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pagos = ['efectivo', 'transferencia', 'tarjeta'].map(id => document.getElementById(id));
            const total = document.getElementById('total');
            const mensaje = document.getElementById('pagosMensaje');

            function formatearPago(pago) {
                const valor = pago.value.replace(/,/g, '');
                if ((pago.value !== '' && valor === '') || !/^\d*(?:\.\d*)?$/.test(valor)) return;
                const posicion = pago.selectionStart;
                const caracteres = posicion === null ? 0 : pago.value.slice(0, posicion).replace(/,/g, '').length;
                const [entero, decimal] = valor.split('.');
                pago.value = entero.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                    + (decimal === undefined ? '' : `.${decimal}`);
                if (document.activeElement === pago && posicion !== null) {
                    let nuevaPosicion = 0;
                    let vistos = 0;
                    while (nuevaPosicion < pago.value.length && vistos < caracteres) {
                        if (pago.value[nuevaPosicion] !== ',') vistos++;
                        nuevaPosicion++;
                    }
                    pago.setSelectionRange(nuevaPosicion, nuevaPosicion);
                }
            }

            function calcularTotal() {
                let suma = 0n;
                for (const pago of pagos) {
                    const valor = pago.value.replace(/,/g, '');
                    if ((pago.value !== '' && valor === '') || (valor !== '' && !/^\d{1,13}(?:\.\d{1,2})?$/.test(valor))) {
                        total.value = '';
                        mensaje.textContent = 'Ingrese importes positivos con hasta dos decimales.';
                        return;
                    }
                    if (valor === '') continue;
                    const [entero, decimal = ''] = valor.split('.');
                    suma += BigInt(entero) * 100n + BigInt(decimal.padEnd(2, '0'));
                }
                const entero = String(suma / 100n).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                total.value = `${entero}.${String(suma % 100n).padStart(2, '0')}`;
                mensaje.textContent = '';
            }

            pagos.forEach(pago => {
                formatearPago(pago);
                pago.addEventListener('input', function() {
                    formatearPago(pago);
                    calcularTotal();
                });
            });
            calcularTotal();
        });
    </script>
@stop