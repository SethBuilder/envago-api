<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Schedule;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private function makeSchedule(int $seatsTotal = 10, int $seatsBooked = 0): Schedule
    {
        $user     = User::factory()->create();
        $provider = Provider::create([
            'user_id'       => $user->id,
            'business_name' => 'Test Provider',
            'status'        => 'approved',
        ]);
        $category = Category::firstOrCreate(['slug' => 'buggy'], ['name' => 'Buggy']);
        $activity = Activity::create([
            'provider_id'      => $provider->id,
            'category_id'      => $category->id,
            'name'             => 'Test Trip',
            'slug'             => 'test-trip',
            'price'            => 100.00,
            'pricing_type'     => 'per_person',
            'duration_minutes' => 120,
            'capacity'         => $seatsTotal,
            'city'             => 'Jeri',
            'state'            => 'CE',
            'is_active'        => true,
        ]);
        return Schedule::create([
            'activity_id'  => $activity->id,
            'date'         => now()->addDay()->toDateString(),
            'time'         => '09:00:00',
            'seats_total'  => $seatsTotal,
            'seats_booked' => $seatsBooked,
        ]);
    }

    private function mockStripe(string $clientSecret = 'pi_test_secret'): void
    {
        $this->mock(StripeService::class, function ($mock) use ($clientSecret) {
            $mock->shouldReceive('createPaymentIntent')
                ->andReturn((object) ['id' => 'pi_test_123', 'client_secret' => $clientSecret]);
        });
    }

    private function validPayload(Schedule $schedule, array $overrides = []): array
    {
        return array_merge([
            'schedule_id'    => $schedule->id,
            'seats'          => 2,
            'guest_name'     => 'João Silva',
            'guest_email'    => 'joao@example.com',
            'guest_whatsapp' => '88999999999',
        ], $overrides);
    }

    public function test_happy_path_creates_booking_and_returns_client_secret(): void
    {
        $schedule = $this->makeSchedule(seatsTotal: 10);
        $this->mockStripe('pi_test_secret_abc');

        $res = $this->postJson('/api/v1/bookings', $this->validPayload($schedule));

        $res->assertStatus(201)
            ->assertJsonPath('data.client_secret', 'pi_test_secret_abc')
            ->assertJsonPath('data.seats', 2)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonStructure(['data' => ['id', 'reference', 'guest_name', 'expires_at']]);

        $this->assertDatabaseHas('bookings', [
            'schedule_id' => $schedule->id,
            'seats'       => 2,
            'guest_name'  => 'João Silva',
            'guest_email' => 'joao@example.com',
            'status'      => 'pending',
        ]);

        $this->assertDatabaseHas('schedules', [
            'id'           => $schedule->id,
            'seats_booked' => 2,
        ]);
    }

    public function test_insufficient_seats_returns_422(): void
    {
        $schedule = $this->makeSchedule(seatsTotal: 2, seatsBooked: 1);

        $res = $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['seats' => 2]));

        $res->assertStatus(422);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_fully_booked_returns_422(): void
    {
        $schedule = $this->makeSchedule(seatsTotal: 4, seatsBooked: 4);

        $res = $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['seats' => 1]));

        $res->assertStatus(422);
    }

    public function test_missing_required_fields_returns_422(): void
    {
        $schedule = $this->makeSchedule();

        $this->postJson('/api/v1/bookings', ['schedule_id' => $schedule->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['seats', 'guest_name', 'guest_email']);
    }

    public function test_invalid_email_returns_422(): void
    {
        $schedule = $this->makeSchedule();

        $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['guest_email' => 'not-an-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['guest_email']);
    }

    public function test_invalid_schedule_id_returns_422(): void
    {
        $this->postJson('/api/v1/bookings', $this->validPayload($this->makeSchedule(), ['schedule_id' => 999999]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['schedule_id']);
    }

    public function test_seats_zero_or_negative_returns_422(): void
    {
        $schedule = $this->makeSchedule();

        $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['seats' => 0]))
            ->assertStatus(422);

        $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['seats' => -1]))
            ->assertStatus(422);
    }

    public function test_customer_answers_stored_with_booking(): void
    {
        $schedule = $this->makeSchedule();
        $this->mockStripe();

        $answers = [
            ['question_id' => 1, 'label' => 'Pousada para embarque', 'answer' => 'Pousada do Sol'],
        ];

        $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['customer_answers' => $answers]))
            ->assertStatus(201);

        $booking = Booking::first();
        $this->assertNotNull($booking->customer_answers);
        $this->assertEquals('Pousada do Sol', $booking->customer_answers[0]['answer']);
    }

    public function test_expired_pending_bookings_cleaned_up_before_seat_check(): void
    {
        $schedule = $this->makeSchedule(seatsTotal: 2);
        $this->mockStripe();

        // Create an expired pending booking that holds 2 seats
        Booking::create([
            'schedule_id'  => $schedule->id,
            'seats'        => 2,
            'total_price'  => 200,
            'guest_name'   => 'Ghost User',
            'guest_email'  => 'ghost@example.com',
            'reference'    => 'ENV-GHOST1',
            'status'       => 'pending',
            'payment_ref'  => 'pi_old',
            'expires_at'   => now()->subMinutes(5),
        ]);
        $schedule->increment('seats_booked', 2);

        // New booking should succeed because expired one gets cleaned up
        $res = $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['seats' => 2]));

        $res->assertStatus(201);
        $this->assertDatabaseCount('bookings', 1); // ghost was deleted, new one created
    }

    public function test_reference_has_correct_format(): void
    {
        $schedule = $this->makeSchedule();
        $this->mockStripe();

        $res = $this->postJson('/api/v1/bookings', $this->validPayload($schedule));

        $ref = $res->json('data.reference');
        $this->assertMatchesRegularExpression('/^ENV-[A-Z0-9]{6}$/', $ref);
    }

    public function test_total_price_is_seats_times_activity_price(): void
    {
        $schedule = $this->makeSchedule(); // price = 100
        $this->mockStripe();

        $res = $this->postJson('/api/v1/bookings', $this->validPayload($schedule, ['seats' => 3]));

        $res->assertStatus(201)->assertJsonPath('data.total_price', '300.00');
    }
}
