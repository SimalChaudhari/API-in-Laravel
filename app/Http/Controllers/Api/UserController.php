<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Mail\InviteCreated;
use App\Mail\SendPin;
use Mail;

class UserController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * Invitation api
     *
     * @return \Illuminate\Http\Response
     */
    public function invite(Request $request)
    {
        try {
                $validator = Validator::make($request->all(),[
                    'email' => 'required|string|email|max:255',
                ]);

                if ($validator->fails()) {
                    return $this->sendError('Validation Error.', $validator->errors());       
                } else {
                    $result = Mail::to($request->email)->send(new InviteCreated());
                    return $this->sendResponse($result, 'Invitation has been Sent');
                }
        } catch (\Exception $e) {
            return $this->sendError('Invalid Request!', $e->getMessage());  
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        try {        
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'user_name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
    
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['verify_pin'] = mt_rand(100000, 999999);
            $input['user_role'] = 'user';
            Mail::to($request->email)->send(new SendPin($input['verify_pin']));
            $user = User::create($input);
            $success['token'] =  $user->createToken('Api')->accessToken;
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'User register successfully.! Please check your email for verification');

        } catch (\Exception $e) {
            return $this->sendError('Invalid Request!', $e->getMessage());  
        }
    }

     /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
                $user = Auth::user(); 
                $success['token'] =  $user->createToken('Api')->accessToken; 
                $success['name'] =  $user->name;
                return $this->sendResponse($success, 'User login successfully.');
            } else{ 
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
            }
        } catch (\Exception $e) {
            return $this->sendError('Invalid Request!', $e->getMessage());  
        } 
    }

    public function verify(Request $request)
    {
        try { 
            $validator = Validator::make($request->all(), [
                'verify_pin' => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }

            $invite = User::where('verify_pin', $request->verify_pin)->first();
            if($invite){
                $invite->verify_pin = '';
                // $invite->is_verified = true;
                $invite->save();
                return $this->sendResponse([], 'User verified successfully!');
            }else{
                return $this->sendError('Invalid Request!', 'Invalid Verification Pin!'); 
            }
        } catch (\Exception $e) {
            return $this->sendError('Invalid Request!', $e->getMessage());  
        }
    }

    // public function resend() {
    //     if (auth()->user()->hasVerifiedEmail()) {
    //         return $this->sendError('Invalid Request!', 'Email already verified'); 
    //     }
    
    //     return $this->sendResponse([], 'Email verification link sent on your email id');
    // }
}
