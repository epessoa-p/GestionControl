<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Personal;

trait InteractsWithCashSession
{
    /** Caja registradora activa asignada al usuario logueado, si existe. */
    protected function userCashRegister(?int $companyId): ?CashRegister
    {
        if (!$companyId) {
            return null;
        }
        $personal = Personal::where('user_id', auth()->id())
            ->where('company_id', $companyId)
            ->first();
        if (!$personal) {
            return null;
        }
        return CashRegister::where('assigned_personal_id', $personal->id)
            ->where('company_id', $companyId)
            ->where('active', true)
            ->first();
    }

    /** Sesión de caja ABIERTA del usuario logueado, si la tiene. */
    protected function userOpenSession(?int $companyId): ?CashSession
    {
        return $this->userCashRegister($companyId)?->activeSession();
    }
}
