<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_admin_module_links()
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        // Assert presence of manager/admin module links by href
        $response->assertSee('href="' . route('objectives.team') . '"', false);
        $response->assertSee('href="' . route('objectives.approvals') . '"', false);
        $response->assertSee('href="' . route('idps.index') . '"', false);
        $response->assertSee('href="' . route('users.index') . '"', false);
        $response->assertSee('href="' . route('departments.index') . '"', false);
    }

    public function test_non_super_admin_does_not_see_admin_module_links()
    {
        $user = User::factory()->create(['role' => 'employee']);

        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);

        $response->assertDontSee('href="' . route('objectives.team') . '"', false);
        $response->assertDontSee('href="' . route('objectives.approvals') . '"', false);
        $response->assertDontSee('href="' . route('objectives.department') . '"', false);
        $response->assertDontSee('href="' . route('objectives.board.index') . '"', false);
    }
}
