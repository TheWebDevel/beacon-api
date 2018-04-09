<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests;
use App\User;
use App\Group;
use App\GroupUser;
use App\Friend;

use Validator;
/*use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Notification;*/

class GroupsController extends Controller
{
/*public function fcm()
{
$server_key = 'AIzaSyCsdwRiZADpQ5-ZqXtjzD0RcvXzhhlwP1Y';
$client = new Client();
$client->setApiKey($server_key);
$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

$message = new Message();
$message->setPriority('high');
$message->addRecipient(new Device('_YOUR_DEVICE_TOKEN_'));
$message
  ->setNotification(new Notification('FCM', 'succcess'))
  ->setData(['key' => 'value'])
;

$response = $client->send($message);
var_dump($response->getStatusCode());
var_dump($response->getBody()->getContents());
}*/

    public function group_store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_slug' => 'required|string',
            'group_name' => 'required|string',
        ], [
            'user_slug.required' => 'User slug is required',
            'group_name,required' => 'Group name is required',
        ]);

        // If validator fails
        if ($validator->fails())
        {
            $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
  		    return response($response, 422);
        }

    // Fetch the user
    $auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

    // Store all inputs
    $inputs = $request->all();

    // Create group
    $group = new Group;
    $group->group_slug = str_random(16);
    $group->name = $inputs['group_name'];
    $group->save();



    $friends = explode(',' , $inputs['user_slug']);

    // Fetch the users
    $users = User::whereIn('user_slug', $friends)->get();



    foreach ($users as $user)
    {
        $user_friend = Friend::where('user_id', $auth_user->id)->where('friend_id', $user->id)->count();
        if ($user_friend != 0)
        {
            $group_new = new GroupUser;
            $group_new->group_slug = $group->group_slug;
            $group_new->user_id = $user->id;
            $group_new->group_id = $group->id;
            $group_new->group_name = $inputs['group_name'];
            $group_new->group_role = 'User';
            $group_new->user_fname = $user->user_fname;
            $group_new->user_lname = $user->user_lname;
            $group_new->save();
        }
    }

    $admin = new GroupUser;
    $admin->group_slug = $group->group_slug;
    $admin->user_id = $auth_user->id;
    $admin->group_id = $group->id;
    $admin->group_name = $inputs['group_name'];
    $admin->group_role = 'Admin';
    $admin->user_fname = $auth_user->user_fname;
    $admin->user_lname = $auth_user->user_lname;
    $admin->save();


    $response = ['message' => 'Group created successfully'];
    return response($response, 200);
}

public function group_index($group_slug, Request $request)
{
    // Retrieve slug from url
    $request->request->add(['group_slug' => $group_slug]);

    // Validate data
	$validator = Validator::make($request->all(),[
		'group_slug' => 'required|string|exists:groups,group_slug',
	], [
		'group_slug.required' => 'Slug is required',
		'group_slug.exists' => 'Oops! Group is not registered',
	]);

	// Stop if validation fails
    if ($validator->fails())
  	{
  		$response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
  		return response($response, 422);
  	}

	// Authenticate User
	$auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

	// Store all inputs
	$inputs = $request->all();

    // Fetch the users of the group
    $group = Group::where('group_slug', $inputs['group_slug'])->first();

    $group_user = GroupUser::where('group_id', $group->id)->get();

    // Time before 1 hours
    $time_start = date("Y-m-d H:i:s", strtotime("-1 hours"));


    foreach ($group_user as $user)
    {
        $location = User::where('id', $user->user_id)->first();
        if($user->user_isin == 1 && $time_start <= $location->updated_at)
        {
            $user->location = $location->user_current_location;
            $user->user_updated_at = $location->updated_at;
            $user->save();
        }
        else {
            $user->location = null;
            $user->user_updated_at = $location->updated_at;
            $user->save();
        }
    }

    $index = GroupUser::where('group_id', $group->id)->having('user_id', '!=', $auth_user->id)->get();

    $response = ['group users' => $index];
    return response($response, 200);
  }

  public function add_users($group_slug, Request $request)
  {
      // Retrieve slug from url
      $request->request->add(['group_slug' => $group_slug]);

      // Validate data
  	$validator = Validator::make($request->all(),[
  		'group_slug' => 'required|string|exists:groups,group_slug',
        'user_slug' => 'required|string',
  	], [
  		'group_slug.required' => 'Slug is required',
  		'group_slug.exists' => 'Oops! Group is not registered',
  	]);

  	// Stop if validation fails
      if ($validator->fails())
    	{
    		$response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
    		return response($response, 422);
    	}

  	// Authenticate User
  	$auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

  	// Store all inputs
  	$inputs = $request->all();

    //Fetch the group from group table
    $group = Group::where('group_slug', $inputs['group_slug'])->first();

    $users = explode(',' , $inputs['user_slug']);

    $friends = User::whereIn('user_slug', $users)->get();

    $group_count = GroupUser::where('group_slug', $inputs['group_slug'])->get();

    if (count($group_count) > 10 )
    {
        $response = ['message' => 'Only 10 users is allowed for a group'];
        return response($response, 422);
    }
    else {

        foreach ($friends as $friend)
        {
            $old_users = GroupUser::where('group_slug', $inputs['group_slug'])->where('user_id', $friend->id)->count();
            if ($old_users == 0)
            {

                $group_new = new GroupUser;
                $group_new->group_slug = $group->group_slug;
                $group_new->user_id = $friend->id;
                $group_new->group_id = $group->id;
                $group_new->group_name = $group->name;
                $group_new->group_role = 'User';
                $group_new->user_fname = $friend->user_fname;
                $group_new->user_lname = $friend->user_lname;
                $group_new->save();

            }
        }
    }

    $response = ['message' => 'New users added successfully'];
    return response($response, 200);
  }

  public function user_isin($group_slug, Request $request)
  {
      // Retrieve slug from url
      $request->request->add(['group_slug' => $group_slug]);

      // Validate data
  	  $validator = Validator::make($request->all(),[
  	       'group_slug' => 'required|string|exists:groups,group_slug',
           'user_isin' => 'required|digits_between:0,1'
  	  ], [
  		   'group_slug.required' => 'Slug is required',
  		   'group_slug.exists' => 'Oops! Group is not registered',
           'user_isin.required' => 'Location allowed data is required',
           'user_isin.digits_betweed' => 'Invalid location data'
  	  ]);

  	  // Stop if validation fails
      if ($validator->fails())
      {
          $response = ['message' => $validator->messages()->all()[0], 'validations' => $validator->messages()->all()];
    	  return response($response, 422);
       }

  	   // Authenticate User
  	   $auth_user = User::where('id', $request->server('PHP_AUTH_USER'))->first();

  	   // Store all inputs
  	   $inputs = $request->all();

       $group_user = GroupUser::where('user_id', $auth_user->id)->where('group_slug',$inputs['group_slug'])->first();

       $group_user->user_isin = $inputs['user_isin'];
       $group_user->save();

       $response = ['message' => 'Location data changed successfully'];
       return response($response, 200);

  }

}
