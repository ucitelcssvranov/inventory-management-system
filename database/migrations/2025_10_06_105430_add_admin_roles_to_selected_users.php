<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class AddAdminRolesToSelectedUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Zmeniť rolu na 'admin' pre vybraných používateľov
        $adminEmails = [
            'snajder@css-vranov.sk',         // Peter Šnajder
            'JVarga@css-vranov.sk',          // Varga Jozef
            'info@css-vranov.sk',            // CSŠ Vranov
            'riaditel@css-vranov.sk',        // Riaditeľ CSŠ Vranov
            'zastupca@css-vranov.sk'         // Zástupca CSŠ Vranov
        ];

        foreach ($adminEmails as $email) {
            User::where('email', $email)->update(['role' => 'admin']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Vrátiť rolu na 'ucitel' pre týchto používateľov
        $adminEmails = [
            'snajder@css-vranov.sk',
            'JVarga@css-vranov.sk',
            'info@css-vranov.sk',
            'riaditel@css-vranov.sk',
            'zastupca@css-vranov.sk'
        ];

        foreach ($adminEmails as $email) {
            User::where('email', $email)->update(['role' => 'ucitel']);
        }
    }
}
