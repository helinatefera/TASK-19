<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Enums\FulfillmentType;
use App\Enums\RestrictionLevel;
use App\Models\Campaign;
use App\Models\CreditScore;
use App\Models\RewardTier;
use App\Models\Role;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\VenueProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users
        $users = $this->createUsers();

        // Create sample campaign
        $campaign = $this->createCampaign($users['creator1']);

        // Create reward tiers for the campaign
        $this->createRewardTiers($campaign);

        // Create sample venue program
        $venueProgram = $this->createVenueProgram($users['mod1']);

        // Create time slot for the venue program
        $this->createTimeSlot($venueProgram);

        // Create credit scores for all users
        $this->createCreditScores($users);
    }

    private function createUsers(): array
    {
        $userDefinitions = [
            'admin' => [
                'username' => 'admin',
                'email' => 'admin@civiccrowd.test',
                'password' => 'Admin123!@#',
                'display_name' => 'System Admin',
                'roles' => ['admin'],
            ],
            'staff1' => [
                'username' => 'staff1',
                'email' => 'staff1@civiccrowd.test',
                'password' => 'Staff123!@#',
                'display_name' => 'Staff Member',
                'roles' => ['staff'],
            ],
            'mod1' => [
                'username' => 'mod1',
                'email' => 'mod1@civiccrowd.test',
                'password' => 'Mod123!@#',
                'display_name' => 'Moderator One',
                'roles' => ['moderator'],
            ],
            'creator1' => [
                'username' => 'creator1',
                'email' => 'creator1@civiccrowd.test',
                'password' => 'Creator123!@#',
                'display_name' => 'Campaign Creator',
                'roles' => ['creator'],
            ],
            'user1' => [
                'username' => 'user1',
                'email' => 'user1@civiccrowd.test',
                'password' => 'User123!@#',
                'display_name' => 'Regular User',
                'roles' => ['user'],
            ],
            'user2' => [
                'username' => 'user2',
                'email' => 'user2@civiccrowd.test',
                'password' => 'User123!@#',
                'display_name' => 'Another User',
                'roles' => ['user'],
            ],
        ];

        $users = [];

        foreach ($userDefinitions as $key => $definition) {
            $user = User::updateOrCreate(
                ['username' => $definition['username']],
                [
                    'email' => $definition['email'],
                    'password' => Hash::make($definition['password']),
                    'display_name' => $definition['display_name'],
                    'email_verified_at' => now(),
                ],
            );

            // Assign roles
            $roleIds = Role::whereIn('name', $definition['roles'])->pluck('id');
            $user->roles()->sync($roleIds);

            $users[$key] = $user;
        }

        return $users;
    }

    private function createCampaign(User $creator): Campaign
    {
        return Campaign::updateOrCreate(
            ['slug' => 'community-garden-fund'],
            [
                'creator_id' => $creator->id,
                'title' => 'Community Garden Fund',
                'description' => 'Help us build a community garden that brings neighbors together and provides fresh produce for local families.',
                'risk_disclosure' => 'Funds may not be fully utilized if permits are delayed.',
                'target_amount' => 2500000, // $25,000.00 in cents
                'pledged_amount' => 0,
                'currency' => 'USD',
                'status' => CampaignStatus::Fundraising,
                'visibility' => CampaignVisibility::Online,
                'duration_days' => 30,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ],
        );
    }

    private function createRewardTiers(Campaign $campaign): void
    {
        RewardTier::updateOrCreate(
            ['campaign_id' => $campaign->id, 'title' => 'Seed Supporter'],
            [
                'description' => 'A thank-you postcard from the garden and your name on the supporter wall.',
                'price' => 2500, // $25.00 in cents
                'quantity_total' => 100,
                'quantity_claimed' => 0,
                'estimated_delivery_at' => now()->addMonths(3),
                'fulfillment_type' => FulfillmentType::Physical,
                'sort_order' => 1,
            ],
        );

        RewardTier::updateOrCreate(
            ['campaign_id' => $campaign->id, 'title' => 'Garden Patron'],
            [
                'description' => 'A dedicated garden plot for one season plus a starter seed kit.',
                'price' => 10000, // $100.00 in cents
                'quantity_total' => 50,
                'quantity_claimed' => 0,
                'estimated_delivery_at' => now()->addMonths(4),
                'fulfillment_type' => FulfillmentType::Physical,
                'sort_order' => 2,
            ],
        );
    }

    private function createVenueProgram(User $moderator): VenueProgram
    {
        return VenueProgram::updateOrCreate(
            ['slug' => 'weekend-concert-series'],
            [
                'title' => 'Weekend Concert Series',
                'description' => 'A series of weekend concerts featuring local artists and bands.',
                'status' => CampaignStatus::Published,
                'visibility' => CampaignVisibility::Online,
                'location' => 'Central Park Amphitheater',
                'created_by' => $moderator->id,
            ],
        );
    }

    private function createTimeSlot(VenueProgram $venueProgram): void
    {
        TimeSlot::updateOrCreate(
            [
                'programable_type' => VenueProgram::class,
                'programable_id' => $venueProgram->id,
                'starts_at' => now()->addWeek()->setTime(19, 0),
            ],
            [
                'ends_at' => now()->addWeek()->setTime(22, 0),
                'seat_capacity' => 50,
                'seats_booked' => 0,
            ],
        );
    }

    private function createCreditScores(array $users): void
    {
        foreach ($users as $user) {
            CreditScore::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'score' => 1000,
                    'no_show_count' => 0,
                    'chargeback_count' => 0,
                    'refund_count' => 0,
                    'violation_count' => 0,
                    'restriction_level' => RestrictionLevel::None,
                ],
            );
        }
    }
}
