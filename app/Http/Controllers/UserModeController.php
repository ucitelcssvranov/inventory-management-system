<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserModeController extends Controller
{
    /**
     * Prepnutie admina do user mode
     */
    public function switchToUserMode()
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Nemáte oprávnenie na prepnutie režimu.');
        }

        session(['admin_user_mode' => true]);
        
        return redirect()->back()->with('success', 'Prepnuté do režimu bežného používateľa. Teraz máte obmedzené oprávnenia.');
    }

    /**
     * Prepnutie späť do admin mode
     */
    public function switchToAdminMode()
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', 'Nemáte oprávnenie na prepnutie režimu.');
        }

        session()->forget('admin_user_mode');
        
        return redirect()->back()->with('success', 'Prepnuté späť do administrátorského režimu.');
    }

    /**
     * Získanie aktuálneho režimu používateľa
     */
    public function getCurrentMode()
    {
        $user = auth()->user();
        
        return response()->json([
            'is_admin' => $user->isAdmin(),
            'is_in_user_mode' => $user->isInUserMode(),
            'has_admin_privileges' => $user->hasAdminPrivileges(),
            'current_mode' => $user->isInUserMode() ? 'user' : 'admin'
        ]);
    }
}