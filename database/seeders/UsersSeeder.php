<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vytvorenie admin používateľa
        User::create([
            'name' => 'Administrátor',
            'email' => 'admin@inventory.sk',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Vytvorenie 10 bežných používateľov
        $users = [
            [
                'name' => 'Mária Novákova',
                'email' => 'maria.novakova@inventory.sk',
                'role' => 'inventorisator'
            ],
            [
                'name' => 'Peter Svoboda',
                'email' => 'peter.svoboda@inventory.sk',
                'role' => 'group_leader'
            ],
            [
                'name' => 'Jana Veselá',
                'email' => 'jana.vesela@inventory.sk',
                'role' => 'commission_member'
            ],
            [
                'name' => 'Tomáš Krajčír',
                'email' => 'tomas.krajcir@inventory.sk',
                'role' => 'commission_chairman'
            ],
            [
                'name' => 'Eva Horáková',
                'email' => 'eva.horakova@inventory.sk',
                'role' => 'group_leader'
            ],
            [
                'name' => 'Martin Tóth',
                'email' => 'martin.toth@inventory.sk',
                'role' => 'inventorisator'
            ],
            [
                'name' => 'Lucia Krátka',
                'email' => 'lucia.kratka@inventory.sk',
                'role' => 'inventory_manager'
            ],
            [
                'name' => 'Michal Dlhý',
                'email' => 'michal.dlhy@inventory.sk',
                'role' => 'commission_member'
            ],
            [
                'name' => 'Zuzana Malá',
                'email' => 'zuzana.mala@inventory.sk',
                'role' => 'inventorisator'
            ],
            [
                'name' => 'Ján Veľký',
                'email' => 'jan.velky@inventory.sk',
                'role' => 'group_leader'
            ]
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), // Všetci majú rovnaké heslo pre testovanie
                'role' => $userData['role'],
            ]);
        }

        $this->command->info('Vytvorených 11 používateľov (1 admin + 10 bežných)');
        $this->command->info('Admin: admin@inventory.sk / admin123');
        $this->command->info('Ostatní: {email} / password123');
    }
}
