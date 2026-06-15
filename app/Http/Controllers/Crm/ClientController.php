<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\ClientDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
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

    public function store(Request $request)
    {
        try {
            $validated = $this->validateClient($request);
            $companyId = $this->getCompanyId();

            DB::transaction(function () use ($validated, $companyId, $request) {
                $photoPath = $this->uploadPhoto($request);

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
                    'photo'           => $photoPath,
                    'latitude'        => $validated['latitude'] ?? null,
                    'longitude'       => $validated['longitude'] ?? null,
                    'created_by'      => auth()->id(),
                ]);

                $this->syncContacts($client, $request->input('contacts', []));
                $this->saveDocuments($request, $client);
            });

            return redirect()->route('crm.clients.index')->with('success', 'Cliente registrado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al crear cliente', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'No fue posible registrar el cliente.');
        }
    }

    public function show(Client $client)
    {
        $this->authorizeRecord($client);
        $client->load(['assignedTo', 'createdBy', 'contacts', 'documents']);

        return view('crm.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $this->authorizeRecord($client);
        $client->load(['contacts', 'documents']);

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

    public function update(Request $request, Client $client)
    {
        $this->authorizeRecord($client);

        try {
            $validated = $this->validateClient($request, $client->id);

            DB::transaction(function () use ($validated, $client, $request) {
                $photoPath = $this->uploadPhoto($request, $client);

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
                    'photo'           => $photoPath,
                    'latitude'        => $validated['latitude'] ?? null,
                    'longitude'       => $validated['longitude'] ?? null,
                ]);

                $this->syncContacts($client, $request->input('contacts', []));
                $this->saveDocuments($request, $client);
            });

            return redirect()->route('crm.clients.show', $client)->with('success', 'Cliente actualizado exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Error al actualizar cliente', [
                'user_id'   => auth()->id(),
                'client_id' => $client->id,
                'message'   => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'No fue posible actualizar el cliente.');
        }
    }

    public function destroyPhoto(Client $client)
    {
        $this->authorizeRecord($client);

        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
            $client->update(['photo' => null]);
        }

        return back()->with('success', 'Foto eliminada.');
    }

    public function destroyDocument(Client $client, ClientDocument $document)
    {
        $this->authorizeRecord($client);

        if ($document->client_id !== $client->id) {
            abort(404);
        }

        Storage::disk('public')->delete($document->filename);
        $document->delete();

        return back()->with('success', 'Documento eliminado.');
    }

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
            ]);
            return back()->with('error', 'No fue posible eliminar el cliente.');
        }
    }

    // ─── Métodos privados ─────────────────────────────────────

    private function validateClient(Request $request, ?int $excludeId = null): array
    {
        $companyId     = $this->getCompanyId();
        $docUniqueRule = \Illuminate\Validation\Rule::unique('clients', 'document_number')
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->ignore($excludeId);

        return $request->validate([
            'type'              => 'required|in:' . implode(',', Client::TYPES),
            'name'              => 'required|string|max:255',
            'commercial_name'   => 'nullable|string|max:255',
            'document_type'     => 'nullable|in:' . implode(',', Client::DOCUMENT_TYPES),
            'document_number'   => ['nullable', 'string', 'max:50', $docUniqueRule],
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:30',
            'mobile'            => 'nullable|string|max:30',
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:100',
            'country'           => 'nullable|string|max:100',
            'status'            => 'required|in:' . implode(',', Client::STATUSES),
            'source'            => 'nullable|in:' . implode(',', Client::SOURCES),
            'assigned_to'       => 'nullable|exists:users,id',
            'notes'             => 'nullable|string',
            'photo'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'documents.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
        ]);
    }

    private function uploadPhoto(Request $request, ?Client $client = null): ?string
    {
        if (!$request->hasFile('photo')) {
            return $client?->photo;
        }

        if ($client?->photo) {
            Storage::disk('public')->delete($client->photo);
        }

        return $request->file('photo')->store('clients/photos', 'public');
    }

    private function saveDocuments(Request $request, Client $client): void
    {
        if (!$request->hasFile('documents')) {
            return;
        }

        foreach ($request->file('documents') as $type => $file) {
            if (!$file || !in_array($type, ClientDocument::TYPES)) {
                continue;
            }

            // Reemplazar si ya existe uno del mismo tipo
            $existing = $client->documents()->where('type', $type)->first();
            if ($existing) {
                Storage::disk('public')->delete($existing->filename);
                $existing->delete();
            }

            $path = $file->store('clients/documents', 'public');

            ClientDocument::create([
                'client_id'     => $client->id,
                'type'          => $type,
                'filename'      => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ]);
        }
    }

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

    private function getCompanyId(): ?int
    {
        $user = auth()->user();
        return $user->is_super_admin
            ? ($user->getCurrentCompany()?->id ?? request('company_id'))
            : $user->getCurrentCompany()?->id;
    }

    private function authorizeRecord(Client $client): void
    {
        if (!auth()->user()->is_super_admin && $client->company_id !== auth()->user()->getCurrentCompany()?->id) {
            abort(403);
        }
    }
}
