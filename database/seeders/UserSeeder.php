<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admins
        $admins = [
            ['username' => 'Modi', 'password' => 'Wara@lyon2026', 'role' => 'admin'],
            ['username' => 'Dante', 'password' => 'Dante@tmc2026', 'role' => 'admin'],
            ['username' => 'Kmex', 'password' => 'Bigk@2026', 'role' => 'admin'],
            ['username' => 'Ballo', 'password' => 'Hm@ballo2026', 'role' => 'admin'],
            ['username' => 'Cisse', 'password' => '23m@2026', 'role' => 'admin'],
            ['username' => 'Yaya', 'password' => 'Yalatif@2026', 'role' => 'admin'],
            ['username' => 'Youba', 'password' => 'Youbs@2026', 'role' => 'admin'],
        ];

        // Clients avec leur mapping vers les clients
        $clientUsers = [
            ['username' => 'Gda', 'password' => 'Team@com2026', 'role' => 'client', 'client_name' => 'Gda'],
            ['username' => 'Tmc', 'password' => 'Tmc@gda2026', 'role' => 'client', 'client_name' => 'Tmc'],
            ['username' => 'Motors', 'password' => 'Motors@haval2026', 'role' => 'client', 'client_name' => 'Motors'],
        ];

        // Créer les admins
        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['username' => $admin['username']],
                [
                    'password' => Hash::make($admin['password']),
                    'role' => $admin['role'],
                ]
            );
        }

        // Créer ou trouver les clients et lier les utilisateurs
        foreach ($clientUsers as $clientUser) {
            // Créer ou trouver le client
            $client = Client::firstOrCreate(
                ['nom_entreprise' => $clientUser['client_name']],
                ['nom_entreprise' => $clientUser['client_name']]
            );

            // Créer ou mettre à jour l'utilisateur avec le client_id
            User::updateOrCreate(
                ['username' => $clientUser['username']],
                [
                    'password' => Hash::make($clientUser['password']),
                    'role' => $clientUser['role'],
                    'client_id' => $client->id,
                ]
            );
        }
    }
}
