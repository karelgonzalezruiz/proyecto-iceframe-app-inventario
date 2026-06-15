@extends('layouts.app')
@section('title', 'Editar producto · IceFrame')
@section('content')
<div class="page-header mb-3">
    <div class="page-pretitle">Catálogo</div>
    <h2 class="page-title">Editar: {{ $producto->nombre }}</h2>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('productos.update', $producto) }}">
            @csrf @method('PUT')
            @include('productos._form_fields')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-iceframe"><i class="ti ti-device-floppy me-1"></i> Actualizar</button>
                <a href="{{ route('productos.show', $producto) }}" class="btn btn-link">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
