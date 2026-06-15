@extends('layouts.app')
@section('title', 'Editar proveedor')
@section('page')
    @include('purchases.suppliers.form', [
        'action' => route('purchases.suppliers.update', $supplier),
        'method' => 'PUT',
    ])
@endsection
