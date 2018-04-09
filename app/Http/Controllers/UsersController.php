<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

use Validator;
use App\User;
use App\Friend;
use Hash;

class UsersController extends Controller
{
    public function store_details(Request $request)
    {
        // Validate data
        $validator = Validator::make($request->all(), [
            'user_password' => 'required|string',
            'user_fname' => 'required|string',
            'user_lname' => 'string',
        ], [
            'user_password.required' => 'Password is required',
            'user_fname.required' => 'User name is required',
        ]);

        // Stop if validation fails
        if ($validator->fails())
        {
            $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
            return response($response, 422);
        }

        // Authenticate user
        $auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

        // Store all inputs
        $inputs = $request->all();

        // Store the user details
        $auth_user->user_password = Hash::make($inputs['user_password']);
        $auth_user->user_fname = $inputs['user_fname'];
        $auth_user->user_lname = $inputs['user_lname'];
        $auth_user->user_isregistered = 1;
        $auth_user->save();

        // Response
        $response = ['message' => 'User details stored successfully'];
        return response($response, 200);
    }

    public function login(Request $request)
    {

      // Validate data
      $validator = Validator::make($request->all(),[
        'user_mobile' => 'required|digits:10|string|exists:users,user_mobile',
        'user_password' => 'required|string',
      ], [
        'user_mobile.required' => 'Mobile number is required to log you in',
        'user_mobile.size' => 'Invalid Mobile number',
        'user_mobile.exists' => 'Mobile number does not exist',
        'user_password.required' => 'Enter the password to log you in',
      ]);

      // If validator fails
      if ($validator->fails())
      {
        $response = ['message' => $validator->messages()->all()[0], 'Validations' => $validator->messages()->all()];
        return response($response, 422);
      }

      // Store all input
      $inputs = $request->all();

      // Get the user
      $user = User::where('user_mobile', $inputs['user_mobile'])->first();

      // Check the password
      if (Hash::check($inputs['user_password'], $user->user_password) == false)
      {
        $response = ['message' => 'Your password does not match with this mobile number'];
        return response($response, 422);
      }

      // Fetch friends of the user
      $friends = Friend::where('user_id', $user->id)->get();

      // Send valid user data on success
      $response = ['token' => $user->user_auth_token, 'user' => $user, 'friends' => $friends];
      return response($response, 200);
    }

    public function store_location(Request $request)
    {
        // Validate data
        $validator = Validator::make($request->all(), [
            'location' => 'required|string',
        ], [
            'location.required' => 'Location is required',
        ]);

        // Stop if validator fails
        if ($validator->fails())
        {
            $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
            return response($response,422);
        }

        // Authenticate user
        $auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

        // Store all inputs
        $inputs = $request->all();

        // Store the location into the database
        $auth_user->user_current_location = $inputs['location'];
        $auth_user->save();

        // Return a success response
        $response = ['message' => 'Location stored successfully'];
        return response($response, 200);
    }
}
