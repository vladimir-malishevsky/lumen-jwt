<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use App\Http\Controllers\Controller;

use App\Models\User;

class AuthController extends Controller
{
  public function __construct() {
    $this->middleware("auth", ["except" => ["login", "register"]]);
  }
  /**
   * Attempt to register a new user to the API.
   *
   * @param Request $request
   * @return Response
   */
  public function register(Request $request)
  {
    // Are the proper fields present?
    $this->validate($request, [
      "name" => "required|string|between:2,100",
      "email" => "required|string|email|max:100|unique:users",
      "password" => "required|string|min:6",
    ]);
    try {
      $user = new User;
      $user->name = $request->input("name");
      $user->email = $request->input("email");
      $plainPassword = $request->input("password");
      $user->password = app("hash")->make($plainPassword);
      $user->save();
      return response()->json(["user" => $user, "message" => "CREATED"], 201);
    } catch (\Exception $e) {
      return response()->json(["message" => "User Registration Failed!"], 409);
  }
}
  /**
   * Attempt to authenticate the user and retrieve a JWT.
   * Note: The API is stateless. This method _only_ returns a JWT. There is not an
   * indicator that a user is logged in otherwise (no sessions).
   *
   * @param Request $request
   * @return Response
   */
  public function login(Request $request)
  {
    // Are the proper fields present?
    $this->validate($request, [
      "email" => "required|string",
      "password" => "required|string",
    ]);
    $credentials = $request->only(["email", "password"]);
    if (! $token = Auth::attempt($credentials)) {
      // Login has failed
      return response()->json(["message" => "Unauthorized"], 401);
    }
    return $this->respondWithToken($token);
}
  /**
   * Log the user out (Invalidate the token). Requires a login to use as the
   * JWT in the Authorization header is what is invalidated
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function logout() {
    auth()->logout();
    return response()->json(["message" => "User successfully signed out"]);
  }
  /**
   * Refresh the current token.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function refresh() {
    return $this->respondWithToken( auth()->refresh() );
  }
  /**
   * Helper function to format the response with the token.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  private function respondWithToken($token)
  {
    return response()->json([
      "token" => $token,
      "token_type" => "bearer",
      "expires_in" => Auth::factory()->getTTL() * 60
    ], 200);}
  }