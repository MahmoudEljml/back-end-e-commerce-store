<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Twilio\Rest\Client;


class UsersContoller extends Controller
{
    public function GetUsers()
    {
        return User::all();
    }
    // Get Auth User
    public function authUser()
    {
        return Auth::user();
    }

    // Get Specific User
    public function getUser($id)
    {
        return User::findOrFail($id);
    }

    // Add User

    public function addUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required'
        ]);
        $user = DB::table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);
        return response()->json([
            'user' => $user,
        ], 200);
    }

    // Edit User
    public function editUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required',
        ]);
        $userExist = User::where('email', $request->email)->where('id', '!=', $id)->first();
        if ($userExist) {
            return response()->json([
                'message' => 'Email already exist',
            ], 400);
        } else {
            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->save();
            return response()->json([
                'user' => $user,
            ], 200);
        }
    }

    // Delete User
    public function destroy($id)
    {
        return User::findOrFail($id)->delete();
    }

    // add phone
    public function addPhone(Request $request, $id)
    {
        $request->validate([
            'phone' => 'required',
        ]);
        $user = User::findOrFail($id);

        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioWhatsAppNumber = 'whatsapp:' . env('TWILIO_WHATSAPP_NUMBER');
        $recipientNumber = 'whatsapp:+2' . $request->phone;
        $otp = rand(1000, 9999);
        $message = "Verify code: $otp";

        $twilio = new Client($twilioSid, $twilioToken);
        try {
            $twilio->messages->create(
                $recipientNumber,
                [
                    "from" => $twilioWhatsAppNumber,
                    "body" => $message,
                ]
            );

            $user->phone = $request->phone . "-otp->" . $otp;
            $user->save();
            return response()->json([
                'message' => 'WhatsApp message sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptPhoneNumber(Request $request, $id)
    {
        $request->validate([
            'otp' => 'required',
        ]);
        $user = User::findOrFail($id);

        if (!str_contains($user->phone, '-otp->')) {
            return response()->json(['message' => 'Phone verified'], 400);
        } else {
            $phone = explode('-otp->', $user->phone)[0];
            $otp = explode('-otp->', $user->phone)[1];
        }

        if ($otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP'], 401);
        }
        $user->phone = $phone;
        $user->save();
        return response()->json([
            'message' => 'Phone number accepted successfully',
        ]);

    }

}
