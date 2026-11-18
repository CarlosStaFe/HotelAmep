@php
    $estadoColor = match ($estado) {
        'Reservada' => '#D2E014',
        'Confirmada' => '#F29727',
        'Ocupada' => '#B82E27',
        'Liberada' => '#A69F9F',
        default => '#0CB371',
    };
@endphp
<span class="badge" style="background-color: {{ $estadoColor }}; color: {{ $estado === 'Reservada' ? '#212529' : '#fff' }};">{{ $estado ?? 'N/A' }}</span>
