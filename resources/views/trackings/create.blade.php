@extends('layouts.app')
@section('title', 'Nuevo seguimiento')
@section('page')
@include('trackings.form', ['action' => route('trackings.store'), 'method' => 'POST', 'tracking' => null])
@endsection
