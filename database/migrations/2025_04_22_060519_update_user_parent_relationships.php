<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Find the SC user to be parent of all captains
        $sc = User::where('role', 'sc')->first();
        if (!$sc) {
            // Create SC user if not exists
            $sc = User::create([
                'name' => 'System Coordinator',
                'email' => 'sc@example.com',
                'password' => bcrypt('password'),
                'role' => 'sc',
            ]);
        }

        // Step 2: Set SC as parent of all captains
        User::where('role', 'captain')
            ->update(['parent_id' => $sc->id]);

        // Step 3: For each captain, set them as parent of leaders
        $captains = User::where('role', 'captain')->get();
        
        // If there are no captains, create one
        if ($captains->count() === 0) {
            $captain = User::create([
                'name' => 'Default Captain',
                'email' => 'captain@example.com',
                'password' => bcrypt('password'),
                'role' => 'captain',
                'parent_id' => $sc->id,
            ]);
            $captains = collect([$captain]);
        }

        // Get all leaders
        $leaders = User::where('role', 'leader')->get();
        
        // If there are leaders but no relationship set yet
        if ($leaders->count() > 0) {
            // Distribute leaders among captains
            foreach ($leaders as $index => $leader) {
                $captainIndex = $index % $captains->count();
                $captain = $captains[$captainIndex];
                
                $leader->update(['parent_id' => $captain->id]);
            }
        }

        // Step 4: For each leader, set them as parent of members
        $leaders = User::where('role', 'leader')->get();
        
        // If there are no leaders, create one
        if ($leaders->count() === 0) {
            $leader = User::create([
                'name' => 'Default Leader',
                'email' => 'leader@example.com',
                'password' => bcrypt('password'),
                'role' => 'leader',
                'parent_id' => $captains->first()->id,
            ]);
            $leaders = collect([$leader]);
        }

        // Update all members to have a leader as parent
        $members = User::where('role', 'member')->get();
        
        if ($members->count() > 0) {
            // Distribute members among leaders
            foreach ($members as $index => $member) {
                $leaderIndex = $index % $leaders->count();
                $leader = $leaders[$leaderIndex];
                
                $member->update(['parent_id' => $leader->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data migration, we can't really reverse it properly
        // We could set all parent_id to null, but that would remove legitimate relationships
        // Better to handle this manually if needed
    }
};
