<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DynamicLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_ingest_dynamic_leads()
    {
        $payload = [
            'name' => 'John Carter',
            'phone_number' => '+123456789',
            'estimated_budget' => '50000',
        ];

        $response = $this->postJson('/api/leads', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Lead registered and pending manual assignment.'
                 ]);

        // Check if lead and fields exist in db
        $this->assertDatabaseHas('lead_fields', ['key' => 'name']);
        $this->assertDatabaseHas('lead_fields', ['key' => 'phone_number']);
        $this->assertDatabaseHas('lead_fields', ['key' => 'estimated_budget']);

        $lead = Lead::first();
        $this->assertNotNull($lead);
        $this->assertEquals('John Carter', $lead->data['name']);
        $this->assertEquals('50000', $lead->data['estimated_budget']);
        $this->assertNull($lead->assigned_to);
    }

    public function test_admin_can_toggle_column_visibility()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        LeadField::create(['key' => 'col_a', 'label' => 'Col A', 'is_visible' => true]);
        LeadField::create(['key' => 'col_b', 'label' => 'Col B', 'is_visible' => true]);

        $response = $this->actingAs($admin)
            ->post('/leads/fields/visibility', [
                'visible_fields' => ['col_a'],
            ]);

        $response->assertStatus(302);
        
        $this->assertTrue(LeadField::where('key', 'col_a')->first()->is_visible);
        $this->assertFalse(LeadField::where('key', 'col_b')->first()->is_visible);
    }

    public function test_admin_can_manually_assign_lead()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $agent = User::create([
            'name' => 'Agent',
            'email' => 'agent@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $lead = Lead::create([
            'status' => 'new',
            'data' => ['name' => 'Bob'],
        ]);

        $response = $this->actingAs($admin)
            ->post("/leads/{$lead->id}/assign", [
                'assigned_to' => $agent->id,
            ]);

        $response->assertStatus(302);
        
        $lead->refresh();
        $this->assertEquals($agent->id, $lead->assigned_to);
    }

    public function test_agent_can_only_see_their_leads()
    {
        LeadField::create(['key' => 'name', 'label' => 'Name', 'is_visible' => true]);

        $agent1 = User::create([
            'name' => 'Agent 1',
            'email' => 'agent1@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $agent2 = User::create([
            'name' => 'Agent 2',
            'email' => 'agent2@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $lead1 = Lead::create([
            'status' => 'new',
            'assigned_to' => $agent1->id,
            'data' => ['name' => 'Lead 1'],
        ]);

        $lead2 = Lead::create([
            'status' => 'new',
            'assigned_to' => $agent2->id,
            'data' => ['name' => 'Lead 2'],
        ]);

        // Access dashboard as agent 1
        $response = $this->actingAs($agent1)->get('/dashboard');
        
        $response->assertStatus(200)
                 ->assertSee('Lead 1')
                 ->assertDontSee('Lead 2');
    }

    public function test_admin_can_upload_csv()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $csvContent = "Full Name,Email Address,Budget\n" .
                      "Tony Stark,tony@stark.com,999999\n" .
                      "Steve Rogers,steve@rogers.com,100\n";

        $file = UploadedFile::fake()->createWithContent('leads.csv', $csvContent);

        $response = $this->actingAs($admin)
            ->post('/leads/upload', [
                'csv_file' => $file,
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('lead_fields', ['key' => 'full_name']);
        $this->assertDatabaseHas('lead_fields', ['key' => 'email_address']);
        $this->assertDatabaseHas('lead_fields', ['key' => 'budget']);

        $this->assertEquals(2, Lead::count());
        $lead = Lead::where('data->full_name', 'Tony Stark')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('999999', $lead->data['budget']);
    }

    public function test_admin_can_bulk_delete_leads()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $lead1 = Lead::create(['status' => 'new', 'data' => ['name' => 'Lead A']]);
        $lead2 = Lead::create(['status' => 'new', 'data' => ['name' => 'Lead B']]);
        $lead3 = Lead::create(['status' => 'new', 'data' => ['name' => 'Lead C']]);

        $this->assertEquals(3, Lead::count());

        $response = $this->actingAs($admin)
            ->delete('/leads/bulk-delete', [
                'ids' => implode(',', [$lead1->id, $lead2->id]),
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
        
        $this->assertEquals(1, Lead::count());
        $this->assertDatabaseMissing('leads', ['id' => $lead1->id]);
        $this->assertDatabaseMissing('leads', ['id' => $lead2->id]);
        $this->assertDatabaseHas('leads', ['id' => $lead3->id]);
    }

    public function test_non_admin_cannot_bulk_delete_leads()
    {
        $agent = User::create([
            'name' => 'Agent',
            'email' => 'agent@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $lead1 = Lead::create(['status' => 'new', 'data' => ['name' => 'Lead A']]);
        $lead2 = Lead::create(['status' => 'new', 'data' => ['name' => 'Lead B']]);

        $response = $this->actingAs($agent)
            ->delete('/leads/bulk-delete', [
                'ids' => implode(',', [$lead1->id, $lead2->id]),
            ]);

        $response->assertStatus(403);
        $this->assertEquals(2, Lead::count());
    }
}
