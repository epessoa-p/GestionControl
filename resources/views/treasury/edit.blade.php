@extends('layouts.app')
@section('title', 'Editar — ' . $account->name)
@section('page')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('treasury.show', $account) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="mb-0"><i class="bi bi-pencil text-primary me-2"></i>Editar cuenta</h1>
            <p class="text-muted mb-0 small">{{ $account->name }}</p>
        </div>
    </div>

    @include('treasury.form')
</div>
@endsection
