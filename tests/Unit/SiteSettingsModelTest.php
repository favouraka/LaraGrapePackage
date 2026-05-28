<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SiteSettingsModelTest extends TestCase
{
    private array $modelFields = ['label', 'key', 'type', 'group', 'description', 'sort_order'];

    #[Test]
    public function correctly_separates_model_fields_from_value_fields(): void
    {
        $formData = [
            'label' => 'Site General',
            'key' => 'site_general',
            'type' => 'text',
            'group' => 'general',
            'description' => 'General site settings',
            'sort_order' => 1,
            'site_name' => 'My Site',
            'site_tagline' => 'Best site ever',
        ];

        $valueData = [];
        $cleanedData = [];

        foreach ($formData as $field => $val) {
            if (!in_array($field, $this->modelFields)) {
                $valueData[$field] = $val;
            } else {
                $cleanedData[$field] = $val;
            }
        }

        $this->assertEquals([
            'label' => 'Site General',
            'key' => 'site_general',
            'type' => 'text',
            'group' => 'general',
            'description' => 'General site settings',
            'sort_order' => 1,
        ], $cleanedData);

        $this->assertEquals([
            'site_name' => 'My Site',
            'site_tagline' => 'Best site ever',
        ], $valueData);
    }

    #[Test]
    public function encodes_value_fields_to_json(): void
    {
        $valueData = [
            'site_name' => 'Test Site',
            'header_logo_text' => 'TestCo',
            'footer_content' => '© 2026 TestCo',
        ];

        $json = json_encode($valueData);

        $this->assertJson($json);
        $this->assertStringContainsString('Test Site', $json);
        $this->assertStringContainsString('TestCo', $json);
    }

    #[Test]
    public function decodes_json_back_to_form_fields(): void
    {
        $json = json_encode([
            'site_name' => 'My Site',
            'header_logo_text' => 'MyLogo',
        ]);

        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertEquals('My Site', $decoded['site_name']);
        $this->assertEquals('MyLogo', $decoded['header_logo_text']);
    }

    #[Test]
    public function preserves_all_value_fields_through_encode_decode_cycle(): void
    {
        $original = [
            'site_name' => 'Test',
            'site_tagline' => 'Tagline',
            'site_description' => 'Description here',
            'contact_email' => 'test@example.com',
            'contact_phone' => '+123****7890',
            'header_logo_text' => 'Logo',
            'header_background_color' => '#ffffff',
            'header_text_color' => '#000000',
            'footer_logo_text' => 'FooterLogo',
            'footer_background_color' => '#1f2937',
            'footer_text_color' => '#ffffff',
            'footer_content' => '© 2026',
            'seo_title' => 'SEO Title',
            'seo_keywords' => 'test, seo',
            'seo_description' => 'SEO desc',
            'social_facebook' => 'https://facebook.com/test',
            'social_twitter' => 'https://twitter.com/test',
            'social_linkedin' => 'https://linkedin.com/in/test',
            'enable_cache' => true,
            'enable_debug' => false,
        ];

        $json = json_encode($original);
        $decoded = json_decode($json, true);

        $this->assertEquals($original, $decoded);
    }

    #[Test]
    public function handles_empty_value_gracefully(): void
    {
        $this->assertNull(json_decode('null', true));
        $this->assertNull(json_decode('', true));
        $this->assertIsArray(json_decode('{}', true));
        $this->assertEmpty(json_decode('{}', true));
    }

    #[Test]
    public function handles_boolean_fields_correctly(): void
    {
        $valueData = [
            'enable_cache' => true,
            'enable_debug' => false,
            'header_sticky' => true,
            'footer_show_social' => false,
        ];

        $decoded = json_decode(json_encode($valueData), true);

        $this->assertTrue($decoded['enable_cache']);
        $this->assertFalse($decoded['enable_debug']);
        $this->assertTrue($decoded['header_sticky']);
        $this->assertFalse($decoded['footer_show_social']);
    }

    #[Test]
    public function rejects_model_fields_from_being_put_in_value(): void
    {
        $formData = [
            'label' => 'Test',
            'key' => 'test_key',
            'site_name' => 'My Site',
        ];

        $modelFields = ['label', 'key', 'type', 'group', 'description', 'sort_order'];
        $valueData = [];

        foreach ($formData as $field => $val) {
            if (!in_array($field, $modelFields)) {
                $valueData[$field] = $val;
            }
        }

        $this->assertArrayNotHasKey('label', $valueData);
        $this->assertArrayNotHasKey('key', $valueData);
        $this->assertArrayHasKey('site_name', $valueData);
    }

    #[Test]
    public function round_trips_complex_settings_data(): void
    {
        $formData = [
            'label' => 'Site General',
            'key' => 'site_general',
            'type' => 'json',
            'group' => 'general',
            'sort_order' => 0,
            'site_name' => 'Acme Corp',
            'site_tagline' => 'Building the future',
            'contact_email' => 'hello@acme.com',
            'header_logo_text' => 'Acme',
            'header_background_color' => '#0f172a',
            'header_text_color' => '#ffffff',
            'footer_content' => '© Acme Corp',
            'footer_background_color' => '#0f172a',
            'footer_text_color' => '#94a3b8',
            'social_twitter' => '@acme',
            'social_linkedin' => 'acme-corp',
        ];

        // Simulate mutateFormDataBeforeSave:
        $modelFields = ['label', 'key', 'type', 'group', 'description', 'sort_order'];
        $valueData = [];
        foreach ($formData as $field => $val) {
            if (!in_array($field, $modelFields)) {
                $valueData[$field] = $val;
            }
        }
        $json = json_encode($valueData);

        // Simulate mutateFormDataBeforeFill:
        $decoded = json_decode($json, true);

        $this->assertEquals('Acme Corp', $decoded['site_name']);
        $this->assertEquals('#0f172a', $decoded['header_background_color']);
        $this->assertEquals('hello@acme.com', $decoded['contact_email']);
        $this->assertEquals('Building the future', $decoded['site_tagline']);
    }
}
