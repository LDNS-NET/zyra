<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RadiusUser;
use Illuminate\Support\Facades\DB;

class RadiusController extends Controller
{
    public function auth(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = DB::connection('mysql_radius')
            ->table('radcheck')
            ->where('username', $request->username)
            ->where('attribute', 'Cleartext-Password')
            ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        if ($user->value !== $request->password) {
            return response()->json(['status' => 'reject', 'message' => 'Invalid credentials'], 401);
        }

        return response()->json(['status' => 'accept', 'message' => 'Access-Accept'], 200);
    }
}
