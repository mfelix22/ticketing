<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CourierTodayPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CourierTodayPageTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(CourierTodayPage::class)
            ->assertStatus(200);
    }
}
