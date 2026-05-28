<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de Clientes (CRM)
 *
 * Gestiona el ciclo de vida completo de un cliente: creación, consulta,
 * edición, eliminación y gestión de contactos adicionales.
 * Aplica aislamiento por empresa en todas las operaciones.
 */
class ClientController extends Controller
{
    /**
     * Lista los clientes de la empresa activa con filtros por estado y búsqueda.
     */
    public function index(Request $request)
    {
        $companyId    = $this->getCompanyId();
        $activeStatus = $request->get('status', 'todos');
        $search       = $request->get('q');
        $assignedTo   = $request->get('assigned_to');

        $base = fn() => Client::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId));

        $query = $base()
            ->with(['assignedTo', 'createdBy'])
            ->when($activeStatus !== 'todos', fn($q) => $q->where('status', $activeStatus))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('commercial_name', 'like', "%{$search}%")
                   ->orWhere('document_number', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->orWhere('client_number', 'like', "%{$search}%")
                   ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when($assignedTo, fn($q) => $q->where('assigned_to', $assignedTo))
            ->latest();

        $clients = $query->paginate(15)->withQueryString();

        $counts = [
            'todos'     => $base()->count(),
            'activo'    => $base()->where('status', 'activo')->count(),
            'prospecto' => $base()->where('status', 'prospecto')->count(),
            'inactivo'  => $base()->where('status', 'inactivo')->count(),
            'bloqueado' => $base()->where('status', 'bloqueado')->count(),
        ];

        $users = User::whereHas('companies', fn($q) => $q->where('company_id', $companyId))
            ->orderBy('name')->get();

        return view('crm.clients.index', compact('clients', 'activeStatus', 'counts', 'users', 'search', 'assignedTo'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create()
    {
        $companyId    = $this->getCompanyId();
        $clientNumber = Client::generateClientNumber($companyId);
        $users        = User::whereHas('companies', fn($q) => $q->where('company_id', $companyId))
            ->orderBy('name')->get();

        return view('crm.clients.create', [
            'client'       => null,
            'clientNumber' => $clientNumber,
            'users'        => $users,
            'action'       => route('crm.clients.store'),
            'method'       => 'POST',
        ]);
    }

    /**
     * Almacena un nuevo cliente con sus contactos opcionales.
     * Usa transacción para garantizar consistencia.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateClient($request);
            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId, $request) {
                $client = Client::create([
                    'company_id'      => $companyId,
                    'client_number'   => Client::generateClientNumber($companyId),
                    'type'            => $validated['type'],
                    'name'            => $validated['name'],
                    'commercial_name' => $validated['commercial_name'] ?? null,
                    'document_type'   => $validated['document_type'] ?? null,
                    'document_number' => $validated['document_number'] ?? null,
                    'email'           => $validated['email'] ?? null,
                    'phone'           => $validated['phone'] ?? null,
                    'mobile'          => $validated['mobile'] ?? null,
                    'address'         => $validated['address'] ?? null,
                    'city'            => $validated['city'] ?? null,
                    'country'         => $validated['country'] ?? 'Ecuador',
                    'status'          => $validated['status'],
                    'source'          => $validated['source'] ?? null,
                    'assigned_to'     => $validated['assigned_to'] ?? null,
                    'notes'           => $validated['notes'] ?? null,
                    'created_by'      => auth()->id(),
                ]);

                $this->syncContacts($client, $request->input('contacts', []));
            });

            return redirect()->route('crm.clients.index')->with('success', 'Cliente registrado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear cliente', [
                'user_id' => auth()->id(),
                'input'   => $request->except('_token'),
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ]);
            return back()->withInput()->with('error', 'No fue posible registrar el cliente.');
        }
    }

    /**
     * Muestra el detalle de un cliente: info completa + contactos.
     */
    public function show(Client $client)
    {
        $this->authorizeRecord($client);
        $client->load(['assignedTo', 'createdBy', 'contacts']);

        return view('crm.clients.show', compact('client'));
    }

    /**
     * Muestra el formulario de edición de un cliente.
     */
    public function edit(Client $client)
    {
        $this->authorizeRecord($client);
        $client->load('contacts');

        $companyId = $client->company_id;
        $users     = User::whereHas('companies', fn($q) => $q->where('company_id', $companyId))
            ->orderBy('name')->get();

        return view('crm.clients.edit', [
            'client' => $client,
            'users'  => $users,
            'action' => route('crm.clients.update', $client),
            'method' => 'PUT',
        ]);
    }

    /**
     * Actualiza los datos del cliente y sincroniza sus contactos.
     */
    public function update(Request $request, Client $client)
    {
        $this->authorizeRecord($client);

        try {
            $validated = $this->validateClient($request, $client->id);

            DB::transaction(function () use ($validated, $client, $request) {
                $client->update([
                    'type'            => $validated['type'],
                    'name'            => $validated['name'],
                    'commercial_name' => $validated['commercial_name'] ?? null,
                    'document_type'   => $validated['document_type'] ?? null,
                    'document_number' => $validated['document_number'] ?? null,
                    'email'           => $validated['email'] ?? null,
                    'phone'           => $validated['phone'] ?? null,
                    'mobile'          => $validated['mobile'] ?? null,
                    'address'         => $validated['address'] ?? null,
                    'city'            => $validated['city'] ?? null,
                    'country'         => $validated['country'] ?? 'Ecuador',
                    'status'          => $validated['status'],
                    'source'          => $validated['source'] ?? null,
                    'assigned_to'     => $validated['assigned_to'] ?? null,
                    'notes'           => $validated['notes'] ?? null,
                ]);

                $this->syncContacts($client, $request->input('contacts', []));
            });

            return redirect()->route('crm.clients.show', $client)->with('success', 'Cliente actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar cliente', [
                'user_id'   => auth()->id(),
                'client_id' => $client->id,
                'message'   => $e->getMessage(),
                'file'      => $e->getFile() . ':' . $e->getLine(),
            ]);
            return back()->withInput()->with('error', 'No fue posible actualizar el cliente.');
        }
    }

    /**
     * Búsqueda de clientes para autocompletado (devuelve JSON).
     * Utilizado en el formulario de ventas para vincular un cliente registrado.
     * Responde a GET /crm/clients/search?q=...
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $q         = trim($request->get('q', ''));
        $companyId = $this->getCompanyId();

        $clients = Client::where('company_id', $companyId)
            ->whereIn('status', ['activo', 'prospecto'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                       ->orWhere('commercial_name', 'like', "%{$q}%")
                       ->orWhere('document_number', 'like', "%{$q}%")
                       ->orWhere('phone', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%")
                       ->orWhere('client_number', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(8)
            ->get();

        return response()->json($clients->map(fn($c) => [
            'id'            => $c->id,
            'display_name'  => $c->display_name,
            'client_number' => $c->client_number,
            'type'          => $c->type,
            'document'      => $c->document_number,
            'phone'         => $c->phone,
            'email'         => $c->email,
            'status'        => $c->status,
        ]));
    }

    /**
     * Elimina (soft delete) un cliente.
     */
    public function destroy(Client $client)
    {
        $this->authorizeRecord($client);

        try {
            $client->delete();
            return redirect()->route('crm.clients.index')->with('success', 'Cliente eliminado exitosamente.');
        } catch (\Throwable $e) {
            Log::error('Error al eliminar cliente', [
                'user_id'   => auth()->id(),
                'client_id' => $client->id,
                'message'   => $e->getMessage(),
                'file'      => $e->getFile() . ':' . $e->getLine(),
            ]);
            return back()->with('error', 'No fue posible eliminar el cliente.');
        }
    }

    // ─── Métodos privados ─────────────────────────────────────

    /**
     * Valida los campos del formulario de cliente.
     * Excluye el documento del cliente actual en la validación de unicidad.
     */
    private function validateClient(Request $request, ?int $excludeId = null): array
    {
        $companyId   = $this->getCompanyId();
        $docUniqueRule = \Illuminate\Validation\Rule::unique('clients', 'document_number')
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->ignore($excludeId);

        return $request->validate([
            'type'            => 'required|in:' . implode(',', Client::TYPES),
            'name'            => 'required|string|max:255',
            'commercial_name' => 'nullable|string|max:255',
            'document_type'   => 'nullable|in:' . implode(',', Client::DOCUMENT_TYPES),
            'document_number' => ['nullable', 'string', 'max:50', $docUniqueRule],
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'mobile'          => 'nullable|string|max:30',
            'address'         => 'nullable|string|max:500',
            'city'            => 'nullable|string|max:100',
            'country'         => 'nullable|string|max:100',
            'status'          => 'required|in:' . implode(',', Client::STATUSES),
            'source'          => 'nullable|in:' . implode(',', Client::SOURCES),
            'assigned_to'     => 'nullable|exists:users,id',
            'notes'           => 'nullable|string',
        ]);
    }

    /**
     * Sincroniza los contactos de un cliente: elimina los anteriores y crea los nuevos.
     * Solo procesa filas con nombre informado.
     */
    private function syncContacts(Client $client, array $contacts): void
    {
        $client->contacts()->delete();

        foreach ($contacts as $contact) {
            if (empty($contact['name'])) {
                continue;
            }
            ClientContact::create([
                'client_id'  => $client->id,
                'name'       => $contact['name'],
                'position'   => $contact['position'] ?? null,
                'email'      => $contact['email'] ?? null,
                'phone'      => $contact['phone'] ?? null,
                'is_primary' => !empty($contact['is_primary']),
                'notes'      => $contact['notes'] ?? null,
            ]);
        }
    }

    /**
     * Obtiene el ID de la empresa activa según el tipo de usuario.
     */
    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    /**
     * Verifica que el cliente pertenece a la empresa activa.
     * Lanza 403 si no coincide para evitar acceso entre empresas.
     */
    private function authorizeRecord(Client $client): void
    {
        if (!auth()->user()->is_super_admin && $client->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
