@extends('layouts.app')
@section('title', 'Editar Orden de Compra')
@section('page')
    @php $orderNumber = $order->order_number; @endphp
    @include('purchases.orders.form')
@endsection
