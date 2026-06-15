@extends('layouts.app')
@section('title', 'Nuevo proveedor')
@section('page')
    @include('purchases.suppliers.form', [
        'supplier' => null,
        'action'   => route('purchases.suppliers.store'),
        'method'   => 'POST',
    ])
@endsection
