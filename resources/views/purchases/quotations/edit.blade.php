@extends('layouts.app')
@section('title', 'Editar Cotización')
@section('page')
    @php $quotationNumber = $quotation->quotation_number; @endphp
    @include('purchases.quotations.form')
@endsection
