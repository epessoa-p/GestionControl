<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Entry;
use App\Models\Departure;
use App\Models\CashSession;
use App\Models\CashMovement;
use App\Models\PettyCash;
use App\Models\PettyCashMovement;
use App\Models\Product;
use App\Models\Production;
use App\Models\Promoter;
use App\Models\Sale;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $sales = Sale::with(['promoter', 'details.product', 'createdBy'])
            ->where('company_id', $companyId)
            ->whereBetween('sale_date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->latest('sale_date')
            ->get();

        if ($request->get('export') === 'xlsx') {
            return $this->exportSales($sales, $from, $to);
        }

        return view('reports.sales', compact('sales', 'from', 'to'));
    }

    public function inventory(Request $request)
    {
        $companyId = $this->getCompanyId();
        $products = Product::where('company_id', $companyId)->where('active', true)->orderBy('name')->get();

        if ($request->get('export') === 'xlsx') {
            return $this->exportInventory($products);
        }

        return view('reports.inventory', compact('products'));
    }

    public function commissions(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $commissions = Commission::with(['promoter', 'sale'])
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest()
            ->get();

        $grouped = $commissions->groupBy('promoter_id')->map(function ($items) {
            return [
                'promoter' => $items->first()->promoter,
                'total' => $items->sum('amount'),
                'pending' => $items->where('status', 'pending')->sum('amount'),
                'paid' => $items->where('status', 'paid')->sum('amount'),
                'count' => $items->count(),
            ];
        });

        if ($request->get('export') === 'xlsx') {
            return $this->exportCommissions($grouped, $from, $to);
        }

        return view('reports.commissions', compact('grouped', 'from', 'to'));
    }

    public function cashMovements(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $sessions = CashSession::with(['cashRegister', 'personal', 'movements'])
            ->whereHas('cashRegister', fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('opened_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->latest('opened_at')
            ->get();

        if ($request->get('export') === 'xlsx') {
            return $this->exportCashMovements($sessions, $from, $to);
        }

        return view('reports.cash-movements', compact('sessions', 'from', 'to'));
    }

    public function production(Request $request)
    {
        $companyId = $this->getCompanyId();
        $from = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));

        $productions = Production::with(['product', 'costs', 'materials.product'])
            ->where('company_id', $companyId)
            ->whereBetween('production_date', [$from, $to])
            ->latest('production_date')
            ->get();

        if ($request->get('export') === 'xlsx') {
            return $this->exportProduction($productions, $from, $to);
        }

        return view('reports.production', compact('productions', 'from', 'to'));
    }

    // ─── Excel Export Methods ───

    private function exportSales($sales, $from, $to)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ventas');

        $this->setHeader($sheet, ['#', 'Número', 'Fecha', 'Cliente', 'Promotor', 'Método pago', 'Subtotal', 'Impuesto', 'Descuento', 'Total', 'Estado']);

        $row = 2;
        foreach ($sales as $i => $sale) {
            $sheet->fromArray([
                $i + 1,
                $sale->sale_number,
                $sale->sale_date->format('d/m/Y'),
                $sale->client_name ?: '-',
                $sale->promoter?->name ?: '-',
                Sale::PAYMENT_LABELS[$sale->payment_method] ?? $sale->payment_method,
                $sale->subtotal,
                $sale->tax,
                $sale->discount,
                $sale->total,
                Sale::STATUS_LABELS[$sale->status] ?? $sale->status,
            ], null, "A{$row}");
            $row++;
        }

        $this->autoSize($sheet, 'K');
        return $this->download($spreadsheet, "ventas_{$from}_{$to}.xlsx");
    }

    private function exportInventory($products)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventario');

        $this->setHeader($sheet, ['#', 'SKU', 'Producto', 'Categoría', 'Unidad', 'Costo', 'Precio', 'Stock actual', 'Stock mínimo', 'Valorización']);

        $row = 2;
        foreach ($products as $i => $product) {
            $sheet->fromArray([
                $i + 1,
                $product->sku,
                $product->name,
                $product->category,
                $product->unit,
                $product->cost,
                $product->price,
                $product->current_stock,
                $product->min_stock,
                $product->current_stock * $product->cost,
            ], null, "A{$row}");
            $row++;
        }

        $this->autoSize($sheet, 'J');
        return $this->download($spreadsheet, 'inventario_' . now()->format('Ymd') . '.xlsx');
    }

    private function exportCommissions($grouped, $from, $to)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Comisiones');

        $this->setHeader($sheet, ['Promotor', 'Ventas', 'Total comisión', 'Pendiente', 'Pagado']);

        $row = 2;
        foreach ($grouped as $data) {
            $sheet->fromArray([
                $data['promoter']?->name ?: '-',
                $data['count'],
                $data['total'],
                $data['pending'],
                $data['paid'],
            ], null, "A{$row}");
            $row++;
        }

        $this->autoSize($sheet, 'E');
        return $this->download($spreadsheet, "comisiones_{$from}_{$to}.xlsx");
    }

    private function exportCashMovements($sessions, $from, $to)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Movimientos de caja');

        $this->setHeader($sheet, ['Caja', 'Personal', 'Apertura', 'Cierre', 'Monto apertura', 'Monto cierre', 'Esperado', 'Diferencia', 'Estado']);

        $row = 2;
        foreach ($sessions as $session) {
            $sheet->fromArray([
                $session->cashRegister?->name,
                $session->personal?->full_name,
                $session->opened_at?->format('d/m/Y H:i'),
                $session->closed_at?->format('d/m/Y H:i') ?: '-',
                $session->opening_amount,
                $session->closing_amount ?? '-',
                $session->expected_amount ?? '-',
                $session->difference ?? '-',
                $session->status === 'open' ? 'Abierta' : 'Cerrada',
            ], null, "A{$row}");
            $row++;
        }

        $this->autoSize($sheet, 'I');
        return $this->download($spreadsheet, "caja_{$from}_{$to}.xlsx");
    }

    private function exportProduction($productions, $from, $to)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Producción');

        $this->setHeader($sheet, ['Lote', 'Producto', 'Cantidad', 'Fecha', 'Costo total', 'Estado']);

        $row = 2;
        foreach ($productions as $prod) {
            $sheet->fromArray([
                $prod->batch_number,
                $prod->product?->name,
                $prod->quantity_produced,
                $prod->production_date->format('d/m/Y'),
                $prod->total_cost,
                Production::STATUS_LABELS[$prod->status] ?? $prod->status,
            ], null, "A{$row}");
            $row++;
        }

        $this->autoSize($sheet, 'F');
        return $this->download($spreadsheet, "produccion_{$from}_{$to}.xlsx");
    }

    // ─── Helpers ───

    private function setHeader($sheet, array $headers): void
    {
        $sheet->fromArray($headers, null, 'A1');
        $lastCol = chr(64 + count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e1e1e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    private function autoSize($sheet, string $lastCol): void
    {
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function download(Spreadsheet $spreadsheet, string $filename)
    {
        $temp = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($temp);
        $spreadsheet->disconnectWorksheets();

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin ? ($user->getCurrentCompany()?->id ?? request('company_id')) : $user->getCurrentCompany()?->id;
    }
}
