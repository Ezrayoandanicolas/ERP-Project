<?php

namespace App\Traits;

use Livewire\Livewire;
use Filament\Panel;

trait FilamentLivewireRouteBinder
{
    public function registerLivewireRoutes(Panel $panel)
    {
        $panelId = $panel->getId();

        // Cek apakah Filament otomatis membuat route Livewire untuk panel ini
        $updateRouteName = "filament.{$panelId}.livewire.update";
        $uploadRouteName = "filament.{$panelId}.livewire.upload-file";

        // Jika route ini tidak ada, jangan lakukan apa-apa
        if (!route($updateRouteName, [], false)) {
            return;
        }

        // Override Livewire route agar sesuai panel
        Livewire::setUpdateRoute(fn($handle) =>
            route($updateRouteName, ['handle' => $handle])
        );

        Livewire::setUploadRoute(fn() =>
            route($uploadRouteName)
        );
    }
}
