<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\BusinessDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\UserAlert;
//use App\Notifications\PasswordChanged;

/**
 * @group User Management
 *
 * APIs to manage user
 */
class UserAuthController extends Controller
{

        /**
        * Get all users
        *
        * @param  \Illuminate\Http\Request  $request
        * @return \Illuminate\Http\JsonResponse
        */
        public function index(Request $request)
        {
           try {
	      $role = $request->input('role');

	      $where = '';

	      //switch ($role) {
		  //case 'administrator':
		     //$where = 'administrator';
		  //break;
		  //case 'driver':
		     //$where = 'driver';
		  //break;
		  //case 'vendor':
		     //$where = 'vendor';
		  //break;
		  //default:
		     //$where = 'user';
		  //break;
	      //}

	      //TODO: protect route admin only
              //$users = User::where('role', $role)->with('role')->get();
              $users = User::with('role')->get();
              return response()->json($users);
           } catch (\Exception $e) {
               return response()->json(['error' => $e->getMessage()], 500);
           }
        }

    /**
     * Register user
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validated_data = $request->validate([
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed',
                'phone_number' => 'required',
                'address' => 'required',
		'role' => 'sometimes|in:administrator,driver,user,vendor',
                'business_name' => 'nullable|max:255',
            ]);

	$validated_data['notification_preferences'] = [
	    'password_change' => true,
	    'login' => true,
	    'order_created' => true,
	    'order_cancelled' => true,
	    'product_declined' => true,
	];	    

            $validated_data['password'] = bcrypt($request->password);

        if ($request->has('role')) {
            $role = Role::where('name', $validated_data['role'])->first();
            if (!$role) {
                return response()->json(['error' => 'Invalid role provided'], 400);
            }
            $validated_data['role_id'] = $role->id;	    
	} else {
            $validated_data['role_id'] = 2;//2 - User. TODO: use constant	    
	}

            $user = User::create($validated_data);

               // Add business details for the user
            $businessDetailsData = $request->only(
                [
                'business_name',
                'business_email',
                'phone',
                'business_address',
                'certificate',
                'bank_name',
                'account_number',
                'profile_picture',
                  ]
            );

               $user->businessDetails()->create($businessDetailsData);



            $token = $user->createToken('API Auth Token')->accessToken;

            return response()->json(['user' => $user, 'token' => $token], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        /**
         * Login user
         *
         * @param  \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
    public function login(Request $request)
    {
        try {
            $validated_data = $request->validate(
                [
                'email' => 'email|required',
                'password' => 'required'
                ]
            );

            if (!auth()->attempt($validated_data)) {
                return response()->json(['message' => 'Incorrect Details. Please try again'], 401);
            }

	    $user = auth()->user()->load('role');
            $token = $user->createToken('API Auth Token')->accessToken;

            return response()->json(['user' => $user, 'token' => $token], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        /**
         * Get the currently logged-in user.
         *
         * @param  \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */

    public function getMe(Request $request)
    {
        try {
	    $user = auth()->user();

            if (!$user) {
		//return response()->json([
		//    'success' => false,
		//    'message' => 'User not authenticated.'
		//], 401);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // filter password
            $user = $this->filterUserData($user);

	    //return response()->json([
            //    'success' => true,
            //    'user' => $user
            //]);
            return response()->json($user);
        } catch (\Exception $e) {
            // Log and handle the exception
            //report($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    private function filterUserData($user)
    {
        // filter sensitive information
        unset($user->password);
        return $user;
    }

    //TODO try catch
        /**
         * Update user details
         *
         * @param  \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
    public function updateProfile(Request $request)
    {
        $request->validate(
            [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
            'phone_number' => 'sometimes|string',
            'address' => 'sometimes|string',
            'profile_picture' => $request->input('imageStatus') === 'new'
            ? 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
            : 'sometimes|string',
            ]
        );


        $user = auth()->user();

        // Update user details
        $user->update($request->only(['name', 'email', 'phone_number', 'address']));

        // Handle profile picture update


        if ($request->imageStatus === 'new' && $request->hasFile('profile_picture')) {
            // Check if the user already has a profile picture
            if ($user->profile_picture) {
                // If a profile picture exists, delete the old image
                if (file_exists(public_path('uploads/' . $user->profile_picture))) {
                    unlink(public_path('uploads/' . $user->profile_picture));
                }
            }

            // Handle file upload
            $extension = $request->file('profile_picture')->getClientOriginalExtension();
            $imageName = Str::random(32) . "." . $extension;
            $request->file('profile_picture')->move('uploads/', $imageName);

            // Update user's profile picture
            $user->update(['profile_picture' => $imageName]);
        }


        //TODO: call update once

        return response(['user' => $user, 'message' => 'Profile updated successfully'], 200);
    }


    /*public function updateProfile(Request $request)
    {
    $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
    ]);

    $user = auth()->user();

    $user->update($request->only(['name', 'email']));

    // Return a success response
    return response(['user' => $user, 'message' => 'Profile updated successfully'], 200);
    }*/


        //TODO try catch
        /**
         * Change reset password
         *
         * @param  \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
    public function resetPassword(Request $request)
    {
        $request->validate(
            [
            'email' => 'required|email',
            'new_password' => 'required',
            'new_password_confirmation' => 'required|same:new_password',
            ]
        );

        $user = auth()->user();

        //Check email
        /*if (!Hash::check($request->old_password, $user->password)) {
         return response(['error' => 'Old password is incorrect'], 401);
        }*/

        $user->update(['password' => bcrypt($request->new_password)]);
        UserAlert::create(
            [
            'user_id' => $user->id,
            'title' => 'Password Reset',
            'message' => 'Your password reset was successfully.',
            ]
	);

        return response(['message' => 'Password reset successfully'], 200);
    }


        //TODO try catch
        /**
         * Change user password
         *
         * @param  \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
    public function updatePassword(Request $request)
    {
        $request->validate(
            [
            'old_password' => 'required',
            'new_password' => 'required',
            'new_password_confirmation' => 'required|same:new_password',
            ]
        );

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response(['error' => 'Old password is incorrect'], 401);
        }

        $user->update(['password' => bcrypt($request->new_password)]);
        /*
        * TODO ?
        // Revoke the current user's access token
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        // Create a new access token for the user
        $newToken = $user->createToken('token-name')->plainTextToken;
        */

        // Send a notification about the password change
        //$user->notify(new PasswordChanged(), ['database']);

            // Create a UserAlert for the password change
        /*UserAlert::create(
            [
            'user_id' => $user->id,
            'title' => 'Password Changed',
            'message' => 'Your password was successfully changed.',
            ]
	);*/
        $user->createAlertIfEnabled(
            'Password Changed',
            'Your password was successfully changed.',
            'password_change'
        );	

        return response(['message' => 'Password updated successfully'], 200);
    }

    public function updateNotificationPreferences(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'notification_preferences' => 'required|array',
                'notification_preferences.password_change' => 'nullable|boolean',
                'notification_preferences.order_created' => 'nullable|boolean',
                'notification_preferences.order_cancelled' => 'nullable|boolean',
                'notification_preferences.product_declined' => 'nullable|boolean',
            ]);

            $user = Auth::user();

            $user->update(['notification_preferences' => $validatedData['notification_preferences']]);

            return response()->json(['message' => 'Notification preferences updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }    

        //TODO try catch
        /**
         * Change user password
         *
         * @param  \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
    public function updateProfilePicture(Request $request)
    {
        $request->validate(
            [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]
        );

        $user = auth()->user();

        // Check if the user already has a profile picture
        if ($user->profile_picture) {
            // If a profile picture exists, delete the old image
            if (file_exists(public_path('uploads/' . $user->profile_picture))) {
                    unlink(public_path('uploads/' . $user->profile_picture));
            }
        }

        // Handle file upload
        $extension = $request->file('profile_picture')->getClientOriginalExtension();
        $imageName = Str::random(32) . "." . $extension;
        $request->file('profile_picture')->move('uploads/', $imageName);

        // Update user's profile picture
        $user->update(['profile_picture' => $imageName]);

        return response(['user' => $user, 'message' => 'Profile picture updated successfully'], 200);
    }


    /**
     * Get user by ID with business details
     *
     * @param  int $userId
     * @return JsonResponse
     */
    public function getUserById($userId)
    {
        try {
            $userWithBusinessDetails = User::with('businessDetails')->find($userId);

            if (!$userWithBusinessDetails) {
                    return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json(['user' => $userWithBusinessDetails], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Block or Unblock user
     *
     * @param  \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function getBlockOrUnBlockUser(request $request)
    {
        try {
            $validatedData = $request->validate([
		'action' => 'required|in:block,unblock',
            ]);

            $user = User::findOrFail($userId);
	    $message = '';

            if (!$user) {
                    return response()->json(['message' => 'User not found'], 404);
	    }

	    if($validatedData['unblock'] == 'unblock') {
        	$product->status = 'blocked';
		$message = 'User blocked successfully';
	    }else {
        	$product->status = 'unblocked';
		$message = 'User unblocked successfully';
	    }

            $user->save();

            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllRoles()
    {
        $roles = Role::all();
        return response()->json(['categories' => $roles]);
    }


}
