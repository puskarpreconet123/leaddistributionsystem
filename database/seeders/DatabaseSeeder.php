<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lead;
use App\Models\LeadField;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $agent1 = User::create([
            'name' => 'Agent Alice',
            'email' => 'agent1@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $agent2 = User::create([
            'name' => 'Agent Bob',
            'email' => 'agent2@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // 2. Seed Default Lead Fields
        $fields = [
            ['key' => 'full_name', 'label' => 'Full Name', 'is_visible' => true],
            ['key' => 'email', 'label' => 'Email', 'is_visible' => true],
            ['key' => 'phone', 'label' => 'Phone', 'is_visible' => true],
            ['key' => 'company', 'label' => 'Company', 'is_visible' => true],
            ['key' => 'source', 'label' => 'Source', 'is_visible' => true],
        ];

        foreach ($fields as $field) {
            LeadField::create($field);
        }

        // 3. Seed Sample Leads with Dynamic JSON Data
        $leadsData = [
            [
                'status' => 'new',
                'assigned_to' => null,
                'data' => [
                    'full_name' => 'John Doe',
                    'email' => 'john@doe.com',
                    'phone' => '+1 (555) 019-2834',
                    'company' => 'Acme Corp',
                    'source' => 'Website Ingestion',
                    'notes' => 'Looking for enterprise plan details.',
                ],
            ],
            [
                'status' => 'contacted',
                'assigned_to' => $agent1->id,
                'data' => [
                    'full_name' => 'Sarah Connor',
                    'email' => 'sarah@skynet.com',
                    'phone' => '+1 (555) 039-4481',
                    'company' => 'Resistance Inc',
                    'source' => 'Manual Entry',
                    'notes' => 'Scheduled demo call for next Tuesday.',
                ],
            ],
            [
                'status' => 'converted',
                'assigned_to' => $agent1->id,
                'data' => [
                    'full_name' => 'Bruce Wayne',
                    'email' => 'bruce@wayne.com',
                    'phone' => '+1 (555) 911-3444',
                    'company' => 'Wayne Enterprises',
                    'source' => 'CSV Upload',
                    'notes' => 'Signed contract for annual premium service.',
                ],
            ],
            [
                'status' => 'lost',
                'assigned_to' => $agent2->id,
                'data' => [
                    'full_name' => 'Clark Kent',
                    'email' => 'clark@dailyplanet.com',
                    'phone' => '+1 (555) 345-2121',
                    'company' => 'Daily Planet',
                    'source' => 'Website Ingestion',
                    'notes' => 'No budget for CRM integration currently.',
                ],
            ],
            [
                'status' => 'new',
                'assigned_to' => $agent2->id,
                'data' => [
                    'full_name' => 'Peter Parker',
                    'email' => 'peter@oscorp.org',
                    'phone' => '+1 (555) 789-0909',
                    'company' => 'Daily Bugle',
                    'source' => 'API Webhook',
                    'notes' => 'Inquired about photography services discount.',
                ],
            ],
        ];

        foreach ($leadsData as $lead) {
            Lead::create($lead);
        }
    }
}
