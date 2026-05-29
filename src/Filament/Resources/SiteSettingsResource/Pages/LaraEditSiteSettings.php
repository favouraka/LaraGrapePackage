<?php

namespace LaraGrape\Filament\Resources\SiteSettingsResource\Pages;

use LaraGrape\Filament\Resources\SiteSettingsResource;
use LaraGrape\Models\SiteSettings;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class LaraEditSiteSettings extends EditRecord
{
    protected static string $resource = SiteSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Before filling the form, decode the JSON `value` column into individual form fields.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        if ($record && $record->value && is_string($record->value)) {
            $decoded = json_decode($record->value, true);
            if (is_array($decoded)) {
                $data = array_merge($data, $decoded);
            }
        } elseif ($record && is_array($record->value)) {
            $data = array_merge($data, $record->value);
        }

        return $data;
    }

    /**
     * Before saving, collapse individual form fields back into the JSON `value` column.
     */
    protected function mutateFormDataBeforeSave(array $data): array
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
     * After save, clear the settings cache so the new values take effect immediately.
     */
    protected function afterSave(): void
    {
        Cache::forget('site_settings_all');

        SiteSettings::clearCache();
    }
}
