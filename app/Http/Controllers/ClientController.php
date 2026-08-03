<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Services\ClientService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly PaymentService $paymentService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $clients = Client::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('client_id', 'like', "%{$search}%")
                        ->orWhere('cnic', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $client = new Client();

        return view('clients.create', compact('client'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        ['client' => $client, 'generated_password' => $generatedPassword] =
            $this->clientService->createClient($this->extractData($request));

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client added successfully. Their Client Portal login is ready below -- write it down now, it will not be shown again.')
            ->with('generated_password', $generatedPassword);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): View
    {
        $client->load(['nominee', 'documents', 'bookings' => function ($query) {
            $query->where('status', 'Active')->with(['plot.block', 'installmentPlan.installments']);
        }]);

        $payments = $client->payments()->with('booking.plot.block')->latest('payment_date')->get();
        $outstandingBalance = $this->paymentService->outstandingBalanceForClient($client);

        return view('clients.show', compact('client', 'payments', 'outstandingBalance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        $client->load(['nominee', 'documents']);

        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clientService->updateClient($client, $this->extractData($request));

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        try {
            $this->clientService->deleteClient($client);
        } catch (\DomainException $e) {
            return redirect()
                ->route('clients.show', $client)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    /**
     * Generate (or reset) this client's Client Portal login password.
     * The new password is shown once, on the redirect back to the client's
     * page -- it is never stored in plain text and never shown again after
     * this request.
     */
    public function generateLogin(Client $client): RedirectResponse
    {
        $generatedPassword = $this->clientService->generatePassword($client);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client Portal login ready -- write down the password below now, it will not be shown again.')
            ->with('generated_password', $generatedPassword);
    }

    /**
     * Remove a single uploaded document from a client.
     */
    public function destroyDocument(Client $client, ClientDocument $document): RedirectResponse
    {
        $this->clientService->deleteDocument($document);

        return redirect()
            ->route('clients.edit', $client)
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Reshape the validated request into the three pieces the service needs:
     * plain client fields, nominee fields, and the uploaded files.
     */
    private function extractData(StoreClientRequest|UpdateClientRequest $request): array
    {
        $validated = $request->validated();

        return [
            'client' => [
                'full_name' => $validated['full_name'],
                'father_name' => $validated['father_name'] ?? null,
                'cnic' => $validated['cnic'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'phone' => $validated['phone'],
                'whatsapp' => $validated['whatsapp'] ?? null,
                'email' => $validated['email'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? 'Pakistan',
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
            ],
            'profile_photo' => $request->file('profile_photo'),
            'nominee' => [
                'name' => $validated['nominee_name'] ?? null,
                'relationship' => $validated['nominee_relationship'] ?? null,
                'cnic' => $validated['nominee_cnic'] ?? null,
                'phone' => $validated['nominee_phone'] ?? null,
                'email' => $validated['nominee_email'] ?? null,
                'address' => $validated['nominee_address'] ?? null,
            ],
            'document_types' => $validated['document_type'] ?? [],
            'document_files' => $request->file('document_file') ?? [],
        ];
    }
}
