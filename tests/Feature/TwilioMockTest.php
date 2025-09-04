<?php

namespace Tests\Feature;

use Tests\TestCase;

class TwilioMockTest extends TestCase
{
    public function test_sms_endpoint_returns_success_response(): void
    {
        $response = $this->get('/_dev/sms');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'ok',
            'sid',
            'to',
            'from',
            'body',
            'status',
        ]);

        $data = $response->json();
        $this->assertTrue($data['ok']);
        $this->assertEquals('+15551234567', $data['to']);
        $this->assertEquals('Hola desde Twilio Mock', $data['body']);
    }

    public function test_sms_endpoint_with_custom_phone_number(): void
    {
        $customPhone = '+1234567890';
        $response = $this->get("/_dev/sms?to={$customPhone}");

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['ok']);
        $this->assertEquals($customPhone, $data['to']);
        $this->assertEquals('Hola desde Twilio Mock', $data['body']);
    }
}
