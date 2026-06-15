@extends('layouts.app')
@section('title', 'Nueva Solicitud de Compra')
@section('page')
    @php
        $reqCompanyId = auth()->user()?->getCurrentCompany()?->id ?? 0;
        $requestNumber = \App\Models\PurchaseRequest::generateRequestNumber($reqCompanyId);
    @endphp
    @include('purchases.requests.form')
@endsection
