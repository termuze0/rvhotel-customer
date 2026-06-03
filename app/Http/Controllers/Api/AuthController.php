<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{User, CustomerProfile, HotelProfile};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, DB, Validator};

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required|unique:users',
            'role' => 'required|in:customer,hotel',
            // Profile fields
            'first_name' => 'required_if:role,customer',
            'hotel_name' => 'required_if:role,hotel',
            'address' => 'required_if:role,hotel',
        ]);

        if ($v->fails()) return response()->json($v->errors(), 422);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
            ]);

            if ($request->role === 'customer') {
                $user->customerProfile()->create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                ]);
            } else {
                $user->hotelProfile()->create([
                    'hotel_name' => $request->hotel_name,
                    'address' => $request->address,
                ]);
            }

            return response()->json([
                'token' => $user->createToken('api_token')->plainTextToken,
                'message' => 'Account created successfully'
            ]);
        });
    }

    public function login(Request $request)
{
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    $profile = $user->role === 'hotel'
        ? $user->hotelProfile
        : $user->customerProfile;

    return response()->json([
        'token' => $user->createToken('api_token')->plainTextToken,
        'role' => $user->role,
        'profile' => [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $profile->avatar,
            'loyalty_pts' => $profile->loyalty_pts,
            'created_at' => $profile->created_at,
            'updated_at' => $profile->updated_at,
        ]
    ]);
}

public function updateProfile(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'customer') {
        return response()->json([
            'success' => false,
            'message' => 'Only customers can update this profile'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'first_name' => 'nullable|string|max:100',
        'last_name'  => 'nullable|string|max:100',
        'phone'      => 'nullable|string|unique:users,phone,' . $user->id,
        'avatar'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $profile = $user->customerProfile;

    // Update user table
    if ($request->filled('phone')) {
        $user->phone = $request->phone;
        $user->save();
    }

    // Update profile table
    if ($request->filled('first_name')) {
        $profile->first_name = $request->first_name;
    }

    if ($request->filled('last_name')) {
        $profile->last_name = $request->last_name;
    }

    // Upload avatar
    if ($request->hasFile('avatar')) {

        // delete old image
        if ($profile->avatar &&
            Storage::disk('public')->exists($profile->avatar)) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $path = $request->file('avatar')
            ->store('avatars', 'public');

        $profile->avatar = $path;
    }

    $profile->save();

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => [
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'phone' => $user->phone,
            'avatar' => $profile->avatar
                ? asset('storage/' . $profile->avatar)
                : null,
        ]
    ]);
}
}