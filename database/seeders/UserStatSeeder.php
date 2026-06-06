<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Stat;
use App\Models\UserStat;

class UserStatSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $stats = Stat::all();

        foreach ($users as $user) {
            foreach ($stats as $stat) {
                UserStat::create([
                    'user_id' => $user->id,
                    'stat_id' => $stat->id,
                    'value' => 0, // Valor inicial
                ]);
            }
        }
    }
}
