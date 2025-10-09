<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUsersAndAddTeachers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Dočasne zakážeme foreign key constraints
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Vymažeme všetkých existujúcich používateľov
        User::truncate();
        
        // Znovu povolíme foreign key constraints
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Pridáme nových učiteľov
        $teachers = [
            ['Peter Šnajder', 'snajder@css-vranov.sk'],
            ['Varga Jozef', 'JVarga@css-vranov.sk'],
            ['Daniela Tušimová', 'dtusimova@css-vranov.sk'],
            ['Slávka Kisilová', 'skisilova@css-vranov.sk'],
            ['Matúš Haňov', 'mhanov@css-vranov.sk'],
            ['Monika Klaciková', 'mklacikova@css-vranov.sk'],
            ['Zuzana Viňanská', 'zvinanska@css-vranov.sk'],
            ['Eva Grajcarová', 'egrajcarova@css-vranov.sk'],
            ['Majzlik Robert', 'RMajzlik@css-vranov.sk'],
            ['Lojan Peter', 'PLojan@css-vranov.sk'],
            ['Terezia Mašlanková', 'tmaslankova@css-vranov.sk'],
            ['Henrieta Kľučárová', 'hklucarova@css-vranov.sk'],
            ['Jaroslava Babejová', 'jbabejova@css-vranov.sk'],
            ['Vargova Maria', 'MVargova@css-vranov.sk'],
            ['Dzurjaninova Maria', 'MDzurjaninova@css-vranov.sk'],
            ['Vierka Pasulková', 'vpasulkova@css-vranov.sk'],
            ['Tatiana Majecherová', 'tmajcherova@css-vranov.sk'],
            ['Durikova Anna', 'ADurikova@css-vranov.sk'],
            ['CSŠ Vranov', 'info@css-vranov.sk'],
            ['Ivana Janočkova', 'ijanockova@css-vranov.sk'],
            ['Babej Marian', 'MBabej@css-vranov.sk'],
            ['Tusim Jozef', 'JTusim@css-vranov.sk'],
            ['Jedinak Viliam', 'VJedinak@css-vranov.sk'],
            ['Rudolf Kľučár', 'rklucar@css-vranov.sk'],
            ['Anna Belinska', 'abelinska@css-vranov.sk'],
            ['Maruška Galajdová', 'maruska.galajdova@css-vranov.sk'],
            ['Riaditeľ CSŠ Vranov', 'riaditel@css-vranov.sk'],
            ['Zástupca CSŠ Vranov', 'zastupca@css-vranov.sk']
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'name' => $teacher[0],
                'email' => $teacher[1],
                'password' => Hash::make('password123'), // Dočasné heslo
                'role' => 'ucitel',
                'email_verified_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // V prípade rollbacku nevracíme starých používateľov,
        // len vymažeme nových
        User::truncate();
    }
}
