<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientService
{
    /**
     * Build the next client code, e.g. WPC-0001, WPC-0002.
     */
    public function generateClientId(): string
    {
        $lastClient = Client::orderByDesc('id')->first();

        $nextNumber = $lastClient
            ? ((int) substr($lastClient->client_id, 4)) + 1
            : 1;

        return 'WPC-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a client together with its nominee, any uploaded documents,
     * and a Client Portal login (every new client gets one automatically).
     * Everything happens in one transaction: if any part fails, nothing is saved.
     *
     * @return array{client: Client, generated_password: string}
     */
    public function createClient(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $clientData = $data['client'];
            $clientData['client_id'] = $this->generateClientId();

            if ($data['profile_photo'] instanceof UploadedFile) {
                $clientData['profile_photo'] = $data['profile_photo']->store('client-photos', 'public');
            }

            $client = Client::create($clientData);

            $this->saveNominee($client, $data['nominee']);
            $this->saveDocuments($client, $data['document_types'], $data['document_files']);

            $generatedPassword = $this->generatePassword($client);

            return ['client' => $client, 'generated_password' => $generatedPassword];
        });
    }

    /**
     * Update a client together with its nominee and any newly uploaded documents.
     */
    public function updateClient(Client $client, array $data): Client
    {
        return DB::transaction(function () use ($client, $data) {
            $clientData = $data['client'];

            if ($data['profile_photo'] instanceof UploadedFile) {
                if ($client->profile_photo) {
                    Storage::disk('public')->delete($client->profile_photo);
                }

                $clientData['profile_photo'] = $data['profile_photo']->store('client-photos', 'public');
            }

            $client->update($clientData);

            $this->saveNominee($client, $data['nominee']);
            $this->saveDocuments($client, $data['document_types'], $data['document_files']);

            return $client;
        });
    }

    /**
     * Generate a brand new, strong Client Portal password for this client,
     * save it (hashed, via the model's 'hashed' cast), and hand back the
     * one-time plain-text copy so the admin can show/share it. Nothing else
     * ever stores or logs the plain text -- once this method returns, only
     * the hash remains in the database.
     *
     * Used both for a client's very first login (called from createClient
     * above) and for resetting an existing client's password -- it's the
     * same operation either way: whatever password was there before stops
     * working, and this new one is the only way in from now on.
     */
    public function generatePassword(Client $client): string
    {
        $plainPassword = Str::password(12);

        $client->password = $plainPassword;
        $client->save();

        return $plainPassword;
    }

    /**
     * "Delete" a client. Client now uses soft deletes, so this only hides
     * the record (sets deleted_at) -- the row, its profile photo, and its
     * documents all stay in place in case the deletion needs to be undone.
     * Nothing is permanently erased here.
     */
    public function deleteClient(Client $client): void
    {
        $client->delete();
    }

    /**
     * Delete a single document (used from the client edit page).
     */
    public function deleteDocument(ClientDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }

    /**
     * A client has at most one nominee, so we only create/update it when
     * the user actually filled in at least one nominee field.
     */
    private function saveNominee(Client $client, array $nomineeData): void
    {
        if (collect($nomineeData)->filter()->isEmpty()) {
            return;
        }

        $client->nominee()->updateOrCreate(['client_id' => $client->id], $nomineeData);
    }

    /**
     * Store any newly uploaded documents. Rows with no file selected are skipped.
     */
    private function saveDocuments(Client $client, array $types, array $files): void
    {
        foreach ($files as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $client->documents()->create([
                'document_type' => $types[$index] ?? 'Other',
                'file_path' => $file->store('client-documents', 'public'),
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }
}
