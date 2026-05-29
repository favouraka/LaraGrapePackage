<?php

namespace LaraGrape\Filament\Resources\SiteSettingsResource\Pages;

use LaraGrape\Filament\Resources\SiteSettingsResource;
use LaraGrape\Models\SiteSettings;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class LaraCreateSiteSettings extends CreateRecord
{
    protected static string $resource = SiteSettingsResource::class;

    /**
     * Before creating, collapse individual form fields into the JSON `value` column.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Fields that belong to the model directly
        $modelFields = ['label', 'key', 'type', 'group', 'description', 'sort_order'];

        // Collect everything else into the value JSON
        $valueData = [];
        foreach ($data as $field => $val) {
            if (!in_array($field, $modelFields)) {
                $valueData[$field] = $val;
                unset($data[$field]);
            }
        }

        $data['value'] = $valueData;

        return $data;
    }

    /**
     * After create, clear the settings cache so the new values take effect immediately.
     */
    protected function afterCreate(): void
    {
        Cache::forget('site_settings_all');

        SiteSettings::clearCache();
    }
}
