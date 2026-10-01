<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kontak & Lokasi Rental
    |--------------------------------------------------------------------------
    |
    | Dipakai di halaman "Tentang Kami". Nomor WhatsApp ditulis dalam format
    | internasional tanpa tanda baca (62...) agar bisa langsung dipakai wa.me.
    |
    */

    'name' => env('CONTACT_NAME', 'Bagoes Cafe'),

    'whatsapp' => env('CONTACT_WHATSAPP', '6285168939414'),

    'whatsapp_display' => env('CONTACT_WHATSAPP_DISPLAY', '+62 851-6893-9414'),

    'place_name' => env('CONTACT_PLACE_NAME', 'Graha Yowana Suci Art and Community Hub'),

    'maps_url' => env('CONTACT_MAPS_URL', 'https://maps.app.goo.gl/VrR2dR2nKyinBjBC7'),

    'maps_embed' => env('CONTACT_MAPS_EMBED', 'https://www.google.com/maps?q=-8.6590781,115.2145647&hl=id&z=17&output=embed'),

];
