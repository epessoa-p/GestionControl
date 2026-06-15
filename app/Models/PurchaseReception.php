<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class PurchaseReception extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'reception_number', 'purchase_order_id', 'warehouse_id',
        'reception_date', 'invoice_number', 'status', 'total', 'notes', 'created_by',
    ];

    protected $casts = [
        'reception_date' => 'date',
        'total'          => 'decimal:2',
        'deleted_at'     => 'datetime',
    ];

    const STATUSES      = ['borrador', 'confirmada', 'cancelada'];
    const STATUS_LABELS = ['borrador' => 'Borrador', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada'];
    const STATUS_COLORS = ['borrador' => 'secondary', 'confirmada' => 'success', 'cancelada' => 'danger'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceptionItem::class);
    }

    public function accountPayable(): HasOne
    {
        return $this->hasOne(AccountPayable::class, 'purchase_reception_id');
    }

    public static function generateReceptionNumber(int $companyId): string
    {
        $prefix = 'REC-' . now()->format('Ym') . '-';
        $last   = static::where('company_id', $companyId)
            ->where('reception_number', 'like', $prefix . '%')
            ->orderByDesc('reception_number')
            ->value('reception_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function confirm(): void
    {
        DB::transaction(function () {
            // 1. Actualizar stock de cada producto
            foreach ($this->items as $item) {
                $item->product->increment('current_stock', (float) $item->quantity_received);
                StockService::adjust($this->company_id, $this->warehouse_id, $item->product_id, (float) $item->quantity_received);

                // 2. Actualizar quantity_received en el item de la OC
                $orderItem = $item->purchaseOrderItem;
                $orderItem->increment('quantity_received', (float) $item->quantity_received);
            }

            // 3. Calcular total de la recepción
            $total = $this->items->sum(fn($i) => (float) $i->quantity_received * (float) $i->unit_price);
            $this->update(['status' => 'confirmada', 'total' => $total]);

            // 4. Actualizar progreso de la orden de compra
            $this->purchaseOrder->updateReceptionProgress();

            // 5. Crear cuenta por pagar si no existe ya para esta orden
            if (!$this->accountPayable()->exists()) {
                $supplier     = $this->purchaseOrder->supplier;
                $paymentDays  = $supplier->payment_terms ?? 0;
                $dueDate      = now()->addDays($paymentDays)->toDateString();
                $apNumber     = AccountPayable::generateApNumber($this->company_id);

                AccountPayable::create([
                    'company_id'            => $this->company_id,
                    'ap_number'             => $apNumber,
                    'supplier_id'           => $supplier->id,
                    'purchase_order_id'     => $this->purchase_order_id,
                    'purchase_reception_id' => $this->id,
                    'invoice_number'        => $this->invoice_number,
                    'invoice_date'          => $this->reception_date,
                    'due_date'              => $dueDate,
                    'amount'                => $total,
                    'amount_paid'           => 0,
                    'balance'               => $total,
                    'status'                => 'pendiente',
                    'created_by'            => $this->created_by,
                ]);
            }
        });
    }
}
