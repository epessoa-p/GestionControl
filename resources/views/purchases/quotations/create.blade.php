@extends('layouts.app')
@section('title', 'Nueva Cotización')
@section('page')
    @php
        $quotCompanyId = auth()->user()?->getCurrentCompany()?->id ?? 0;
        $quotationNumber = \App\Models\PurchaseQuotation::generateQuotationNumber($quotCompanyId);
    @endphp
    @include('purchases.quotations.form')
@endsection
