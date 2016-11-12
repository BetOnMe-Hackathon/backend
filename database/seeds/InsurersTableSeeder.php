<?php

use App\Models\Insurer;
use Illuminate\Database\Seeder;

class InsurersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Insurer::create([
            'email' => 'martins.sipenko+insurer1@gmail.com',
        ]);

        Insurer::create([
            'email' => 'martins.sipenko+insurer2@gmail.com',
        ]);

        Insurer::create([
            'email' => 'martins.sipenko+insurer3@gmail.com',
        ]);
    }
}
