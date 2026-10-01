<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_location_and_whatsapp_contact(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee(config('contact.place_name'))
            ->assertSee(config('contact.whatsapp_display'))
            ->assertSee('https://wa.me/'.config('contact.whatsapp'), false)
            ->assertSee(config('contact.maps_url'), false)
            ->assertSee('09:00');
    }
}
