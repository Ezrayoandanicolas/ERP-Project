<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    // Pastikan role selector di-form terisi dari role user (jika belum di-handle)
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // kalau record ada, isi role field dari roles yang ada
        if ($this->record instanceof User && $this->record->roles()->exists()) {
            $data['role'] = $this->record->roles->first()->name;
        }

        return $data;
    }

    // Setelah disimpan, sinkron role & fields

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
