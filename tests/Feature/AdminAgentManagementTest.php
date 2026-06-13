<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAgentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function createAgent($name = 'Agent Alice', $email = 'agent@test.com')
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);
    }

    public function test_admin_can_create_new_agent()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post('/agents', [
                'name' => 'New Agent',
                'email' => 'newagent@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('users', [
            'name' => 'New Agent',
            'email' => 'newagent@test.com',
            'role' => 'user',
        ]);
    }

    public function test_non_admin_cannot_create_agent()
    {
        $agent = $this->createAgent();

        $response = $this->actingAs($agent)
            ->post('/agents', [
                'name' => 'Another Agent',
                'email' => 'another@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_agent_details_and_password()
    {
        $admin = $this->createAdmin();
        $agent = $this->createAgent('Alice', 'alice@test.com');

        $response = $this->actingAs($admin)
            ->put("/agents/{$agent->id}", [
                'name' => 'Alice Updated',
                'email' => 'alice_updated@test.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(302);
        
        $agent->refresh();
        $this->assertEquals('Alice Updated', $agent->name);
        $this->assertEquals('alice_updated@test.com', $agent->email);
        $this->assertTrue(auth()->attempt(['email' => 'alice_updated@test.com', 'password' => 'newpassword123']));
    }

    public function test_admin_can_update_agent_details_without_updating_password()
    {
        $admin = $this->createAdmin();
        $agent = $this->createAgent('Alice', 'alice@test.com');

        $response = $this->actingAs($admin)
            ->put("/agents/{$agent->id}", [
                'name' => 'Alice Updated',
                'email' => 'alice_updated@test.com',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertStatus(302);
        
        $agent->refresh();
        $this->assertEquals('Alice Updated', $agent->name);
        $this->assertEquals('alice_updated@test.com', $agent->email);
        $this->assertTrue(auth()->attempt(['email' => 'alice_updated@test.com', 'password' => 'password']));
    }

    public function test_admin_can_delete_agent_and_leads_are_unassigned()
    {
        $admin = $this->createAdmin();
        $agent = $this->createAgent();

        $lead = Lead::create([
            'status' => 'new',
            'assigned_to' => $agent->id,
            'data' => ['name' => 'Bob'],
        ]);

        $response = $this->actingAs($admin)
            ->delete("/agents/{$agent->id}");

        $response->assertStatus(302);
        
        $this->assertDatabaseMissing('users', ['id' => $agent->id]);
        
        // Lead should still exist but be unassigned (assigned_to = null)
        $lead->refresh();
        $this->assertNull($lead->assigned_to);
    }
}
