<?php

namespace App\Http\Middleware;

use Closure;
use Validator;

use App\User;

class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Assign all inputs
        $request->request->add(['user_id' => $request->server('PHP_AUTH_USER')]);
        $request->request->add(['user_auth_token' => $request->server('PHP_AUTH_PW')]);

        // Validate the request
        $validator = Validator::make($request->all(), [
          'user_id' => 'required',
          'user_auth_token' => 'required',
        ], [
          'user_id.required' => 'Missing ID',
          'user_auth_token.required' => 'Missing Token'
        ]);

        // If validator fails
        if ($validator->fails())
        {
          $validation = $validator->messages()->all();
          $response['message'] = 'Authentication error';
          $response['validation'] = $validator;
          return response($response, 422);
        }

        // Store all inputs
        $inputs = $request->all();

        // Get the user count
        $user_count = User::where('id', $inputs['user_id'])->where('user_auth_token', $inputs['user_auth_token'])->count();

        // If there are no user
        if ($user_count == 0)
        {
          $response['message'] = 'You are not authorised to make this action';
          return response($response, 403);
        }

        return $next($request);

    }
}
