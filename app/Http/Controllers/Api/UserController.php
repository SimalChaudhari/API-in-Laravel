<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Mail\InviteCreated;
use Mail;

class UserController extends BaseController
{
    protected $apiKey;
    protected $list;

    public function __construct()
    {
        $this->apiKey = setting('mailchimp.apikey');
        $this->list = setting('mailchimp.list_id') ? setting('mailchimp.list_id') : '';
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
                    $mailchimp = new MailChimp($this->apiKey);
                    $result = Mail::to($request->email)->send(new InviteCreated([]));
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
                'avata' => 'required',
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
    
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['verify_pin'] = Str::random(6);
            $user = User::create($input);

            $success['token'] =  $user->createToken('Api')->accessToken;
            $success['name'] =  $user->name;
    
            return $this->sendResponse($success, 'User register successfully.');
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
                'token' => 'required',
            ]);

            $invite = User::where('token', $request->token)->first();
            if($invite){
                $invite->token = '';
                $invite->save();
                return $this->sendResponse($success, 'User verified successfully.');
            }else{
                return $this->sendError('Invalid Request!', 'Invalid Token!'); 
            }
        } catch (\Exception $e) {
            return $this->sendError('Invalid Request!', $e->getMessage());  
        }
    }

    public function resend() {
        if (auth()->user()->hasVerifiedEmail()) {
            return $this->sendError('Invalid Request!', 'Email already verified'); 
        }
    
        return $this->sendResponse([], 'Email verification link sent on your email id');
    }
}
