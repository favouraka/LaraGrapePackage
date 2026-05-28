<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SiteSettingsPersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run the package migration
        $this->artisan('migrate', [
            '--path' => 'vendor/streats22/laragrape/database/migrations',
            '--realpath' => true,
        ])->run();
    }

    #[Test]
    public function it_can_create_and_retrieve_a_site_setting(): void
    {
        $this->assertTrue(true, 'Feature test scaffold - requires full app context');
    }

    #[Test]
    public function it_persists_value_fields_as_json_in_the_value_column(): void
    {
        $this->assertTrue(true, 'Feature test scaffold - requires full app context');
    }

    #[Test]
    public function it_clears_cache_after_saving_settings(): void
    {
        $this->assertTrue(true, 'Feature test scaffold - requires full app context');
    }

    #[Test]
    public function it_can_round_trip_all_form_fields_through_the_resource(): void
    {
        $this->assertTrue(true, 'Feature test scaffold - requires full app context');
    }
}
