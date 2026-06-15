@extends('layouts.app')
@section('title', 'Nueva Orden de Compra')
@section('page')
    @php
        $ordCompanyId = auth()->user()?->getCurrentCompany()?->id ?? 0;
        $orderNumber = \App\Models\PurchaseOrder::generateOrderNumber($ordCompanyId);
    @endphp
    @include('purchases.orders.form')
@endsection
