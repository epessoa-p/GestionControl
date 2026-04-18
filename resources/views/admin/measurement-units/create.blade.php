@extends('layouts.app')

@section('title', 'Nueva unidad de medida')

@section('page')
@include('admin.measurement-units.form', ['action' => route('measurement-units.store'), 'method' => 'POST', 'measurementUnit' => null])
@endsection
