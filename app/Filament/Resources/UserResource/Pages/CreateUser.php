<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // Sebelum data dibuat, kita ubah payload sesuai kebutuhan
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // password sudah di-hash oleh field di resource, tapi kalau belum:
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    // Setelah record dibuat, sinkron role & pastikan store/outlet sesuai
    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        $role = $this->form->getState()['role'] ?? null;

        if ($role) {
            // set role via spatie
            $user->syncRoles([$role]);

            // pastikan field lain sesuai role
            if ($role === 'admin') {
                // admin: harus ada store_id, hapus outlet_id
                if (! $user->store_id) {
                    // jika tidak ada, biarkan null (validasi harus mencegah ini)
                }
                $user->outlet_id = null;
            } elseif ($role === 'cashier') {
                // cashier: harus ada outlet_id, hapus store_id
                $user->store_id = null;
            } else {
                // superadmin/member: hapus store/outlet
                $user->store_id = null;
                $user->outlet_id = null;
            }

            $user->save();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
