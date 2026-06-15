@extends('layouts.app')
@section('title', 'Nueva cuenta de tesorería')
@section('page')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('treasury.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="mb-0"><i class="bi bi-bank text-primary me-2"></i>Nueva cuenta</h1>
            <p class="text-muted mb-0 small">Tesorería</p>
        </div>
    </div>

    @include('treasury.form')
</div>
@endsection
