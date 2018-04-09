<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests;
use GuzzleHttp;



use Validator;
use App\Friend;
use App\User;
use App\GroupUser;
use App\Group;


class FriendsController extends Controller
{
    public function store(Request $request)
    {
      // Validate data
      $validator = Validator::make($request->all(), [
        'user_mobile' => 'required|string',
      ], [
        'user_mobile.required' => 'User mobile is required',
      ]);

      // If validator fails
      if ($validator->fails())
      {
        $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
        return response($response, 422);
      }

      // Authenticate user
      $auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

      // Store all inputs
      $inputs = $request->all();

      // Storing in friends array
      $friend_mobile = explode(',', $inputs['user_mobile']);

      // Fetch the user from user table
      $friends = User::whereIn('user_mobile', $friend_mobile)->get();

      // Store it in friends table
      foreach ($friends as $friend)
      {

          $user_friend = Friend::where('user_id', $auth_user->id)->where('friend_id', $friend->id)->count();

          if ($user_friend == 0 && $friend->user_isregistered == 1 && $friend->user_mobile != $auth_user->user_mobile )
          {
              $friend_new = new Friend;
              $friend_new->user_id = $auth_user->id;
              $friend_new->friend_id = $friend->id;
              $friend_new->friend_slug = $friend->user_slug;
              $friend_new->friend_fname = $friend->user_fname;
              $friend_new->friend_lname = $friend->user_lname;
              $friend_new->save();
          }
      }

      $response = ['message' => 'Friend list is upto date'];
      return response($response, 200);
  }

      public function index(Request $request)
      {
          // Fetch the user
          $auth_user = User::where('id' ,$request->server('PHP_AUTH_USER'))->first();

          // Fetch the friends
          $friends = Friend::where('user_id', $auth_user->id)->get();

          // Fetch the groups
          $groups = GroupUser::where('user_id', $auth_user->id)->get();


          // Return it as response
          $response = ['friends' => $friends , 'groups' => $groups];
          return response($response, 200);
      }

      public function location($slug, Request $request)
      {
          // Retrieve slug from url
          $request->request->add(['slug' => $slug]);

          // Validate data
          $validator = Validator::make($request->all(), [
              'slug' => 'required|exists:users,user_slug',
          ], [
              'slug.required' => 'slug is required',
              'slug.exist' => 'User does not exist',
          ]);

          // Stop if validation fails
          if ($validator->fails())
          {
              $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
              return response($response, 422);
          }

          // Authenticate user
          $auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

          $inputs = $request->all();

          // Fetch the id from the user table
          $id = User::where('user_slug', $inputs['slug'])->first();

          // Check if the requested user is his friend
          $friend = Friend::where('user_id',$id->id)->where('friend_id', $auth_user->id)->first();


          // Check if friend enabled location for the user
          if ($friend->location_isallowed == 0)
          {
              $response = ['message' => 'Requested user is not sharing location to you at the moment'];
              return response($response, 422);
          }

          // Time before 1 hours
		  $time_start = date("Y-m-d H:i:s", strtotime("-1 hours"));

          // Fetch the location of the friend
          $location = User::where('user_slug', $inputs['slug'])->first();

          if ($time_start <= $location->updated_at)
          {
          $response = ['location' => $location->user_current_location, 'Time' => $location->updated_at];
          return response($response, 200);
          }
          else {
              $response = ['message' => 'Oops! Your friend is offline for more than an hour!'];
              return response($response, 422);
          }
      }

      public function allow_location($slug, Request $request)
      {
          // Retrieve slug from url
          $request->request->add(['slug' => $slug]);

          // Validate data
          $validator = Validator::make($request->all(), [
              'slug' =>'required|exists:users,user_slug',
              'location_isallowed' => 'required|numeric|digits_between:0,1',
          ], [
              'slug.required' => 'slug is required',
              'slug.exists' => 'User does not exist',
              'location_isallowed.numeric' => 'Invalid Location data',
              'location_isallowed.required' => 'Location allowed data is required',
              'location_isallowed.digits_between' => 'Location allowed data in invalid',
          ]);

          // Stop if validation fails
          if ($validator->fails())
          {
              $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
              return response($response, 422);
          }

          // Authenticate user
          $auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

          $inputs = $request->all();

          // Fetch the id from the user table
          $id = User::where('user_slug', $inputs['slug'])->first();


          // Check if the friend belong to user
          $friend = Friend::where('user_id', $auth_user->id)->where('friend_id', $id->id)->first();

          if (count($friend) == 0)
          {
              $response = ['message' => 'Invalid user'];
              return response($response, 422);
          }


          // Mark location is allowed according to input
          $friend->location_isallowed = $inputs['location_isallowed'];
          $friend->save();

          // Return a success response
          $response = ['message' => 'Location status updated'];
          return response($response, 200);
      }

      /*Testing for guzzle
      public function guzzle(Request $request)
      {
          require vendor\autoload.php;
          $messages = [ 'to' => [''], 'data' => ['FCM Notification']];

          $messages = json_encode($messages)

          $guzzles = new GuzzleHttp\Client([
              'headers' => ['Content-Type' => 'application/json' , 'Authorization key ' => 'AIzaSyCsdwRiZADpQ5-ZqXtjzD0RcvXzhhlwP1Y'],
              'body' => $messages,

          ]);

          $guzzle_response = $guzzles->request('POST', 'https://fcm.googleapis.com/fcm/send');
          if ($guzzle_response->getStatusCode() == 200)
          {
              $response = ['message' => 'Notification Sent'];
              return response($response, 200);
          }
      }*/



}
