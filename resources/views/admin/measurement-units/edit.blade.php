@extends('layouts.app')

@section('title', 'Editar unidad de medida')

@section('page')
@include('admin.measurement-units.form', ['action' => route('measurement-units.update', $measurementUnit), 'method' => 'PUT', 'measurementUnit' => $measurementUnit])
@endsection
