<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

use Validator;
use Hash;
use App\User;
use App\Friend;
class AuthenticationController extends Controller
{
    public function index()
    {
        $response = ['name' => 'Beacon', 'version' => '1.0'];
        return response($response, 200);
    }

    public function signup(Request $request)
    {
        // Validate data
        $validator = Validator::make($request->all(), [
            'user_mobile' => 'required|digits:10',
        ], [
            'user_mobile.required' => 'Mobile number is required',
            'user_mobile.digits' => 'Invalid mobile number',
        ]);

        // Stop if validation fails
        if ($validator->fails())
        {
            $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
            return response($response, 422);
        }

        // Store all inputs
        $inputs = $request->all();

        // Reject if the mobile number is registered
        $user = User::where('user_mobile', $inputs['user_mobile'])->first();

        if (count($user) ==0)
        {
            // Generate otp
            $access_code = rand(100000,999999);

            //store otp
            $new_user = new User;
            $new_user->user_slug = str_random(16);
            $new_user->user_mobile = $inputs['user_mobile'];
            $new_user->user_otp = Hash::make($access_code);
            $new_user->save();

            // Send otp as response
            $response = ['message' => $access_code];
            return response($response, 422);

        }


        elseif (($user->user_isverified == 1) && ($user->user_isregistered == 1))
        {
            $response = ['message' => 'The user is already registered'];
            return response($response, 422);
        }

        elseif (($user->user_isverified == 0) && ($user->user_isregistered == 0))
        {
            // Generate otp
            $access_code = rand(100000,999999);

            //store otp
            $user->user_slug = str_random(16);
            $user->user_otp = Hash::make($access_code);
            $user->save();

            // Send otp as response
            $response = ['message' => $access_code];
            return response($response, 422);
        }

        elseif (($user->user_isverified == 1) && ($user->user_isregistered == 0))
        {
            // Generate otp
            $access_code = rand(100000,999999);

            //store otp
            $user->user_slug = str_random(16);
            $user->user_otp = Hash::make($access_code);
            $user->save();

            // Send otp as response
            $response = ['message' => $access_code];
            return response($response, 200);
        }

    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
  		    'user_mobile' => 'required|digits:10|exists:users,user_mobile',
  			'user_otp' => 'required|string|size:6',
  		], [
  			'user_mobile.required' => 'Mobile number is required',
  			'user_mobile.digits' => 'Invalid mobile number',
  			'user_mobile.exists' => 'Mobile number not registered',
  			'user_otp.required' => 'Access code is required',
  			'user_otp.size' => 'Valid access code is required',
  		]);

  		// Stop if validation fails
  		if ($validator->fails())
  		{
  			$response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
  			return response($response, 422);
  		}

      // Store all inputs
      $inputs = $request->all();

      // Select the user
      $user = User::where('user_mobile', $inputs['user_mobile'])->first();

      if (count($user) == 0)
      {
          $response = ['message' => 'Invalid Mobile Number'];
          return response($response, 422);
      }

      // Verify access code
      if (!Hash::check($inputs['user_otp'], $user->user_otp))
      {
          $response = ['message' => 'Invalid Access Code'];
          return response($response, 422);
      }

      // Generate key
      $access_code = rand(100000,999999);

      // Store the access code
      $user->user_auth_token = Hash::make($access_code);
      $user->user_isverified = 1;
      $user->save();

      // Send response
      $response = ['message' => 'Mobile number verification successful', 'token' => $user->user_auth_token, 'user' => $user];
      return response($response, 200);
    }

    public function cookie(Request $request)
    {
        // Validate data
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'user_auth_token' => 'required|string',
        ], [
            'id.required' => 'User id is required',
            'id.exists' => 'User does not exist',
            'user_auth_token.required' => 'User authentication token is required',
        ]);

        // Stop if validation fails
        if ($validator->fails())
        {
            $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
            return response($response, 422);
        }

        // Store all inputs
        $inputs = $request->all();

        // Fetch the user
        $user = User::where('id', $inputs['id'])->first();

        // Reject if no user found
        if (count($user) == 0)
        {
            $response = ['message' => 'Invalid mobile number. Please use a registered mobile number'];
            return response($response, 422);
        }

        // Reject if the credentials is incorrect
        if ($user->user_auth_token != $inputs['user_auth_token'])
        {
            $response = ['message' => 'Invalid token data', 'token' => '', 'user' => ''];
            return response($response, 422);
        }

        // Fetch the friends id
        $friends = Friend::where('user_id', $inputs['id'])->get();

        // Send valid user data on success
        $response = ['token' => $user->user_auth_token, 'user' => $user, 'friends' => $friends];
        return response($response, 200);
    }
}
