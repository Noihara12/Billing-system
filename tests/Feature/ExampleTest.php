<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_land_on_the_unit_list(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Daftar Unit PlayStation');
    }
}
