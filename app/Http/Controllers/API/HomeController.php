<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Auth;
use App\Models\User;
use App\Models\Receptionist;
use HasApiTokens;
use Validator;
use Hash;

class HomeController extends BaseController
{
    public function logout(Request $request)
    {
        // Auth::logout();
        $user = Auth::user()->token();
        $user->revoke();
        return $this->sendResponse('Success.', ['success'=>'Logout Successfully']);
 
    }

    public function change_password(Request $request)
    {
      $validator = Validator::make($request->all(),[
          'current_password' => 'required',
          'new_password' => 'required|same:confirm_password|min:8',
          'confirm_password' => 'required',
      ]);
      if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors()->first());       
        }
        $user = Auth::user();

      if (!Hash::check($request->current_password,$user->password)) {
        return $this->sendError('error.', 'current password is Invalid');
      }
      $user->password = Hash::make($request->new_password);
      $user->save();
      return $this->sendResponse('message', 'Password changed successfully !');

    }


    public function profile(Request $request)
    {
        try
        {
            $user = User::findOrFail(Auth::id());
            $validator = Validator::make($request->all(),[
                'first_name' =>'required|string',
                'last_name' =>'required|string',
                'designation'=>'required|string',
				'image' => 'image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
            ]);
            
            if($validator->fails())
            {
                return $this->sendError($validator->errors()->first());
            }
            
            $profile = $user->image;
			if($request->hasFile('image')) 
			{
				$file = request()->file('image');
				$fileName = md5($file->getClientOriginalName() . time()) . "PayMefirst." . $file->getClientOriginalExtension();
				$file->move('uploads/users/', $fileName);  
				$profile = asset('uploads/users/'.$fileName);
			}
            
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->designation = $request->designation;
            $user->image = $profile;
            $user->save();
            return response()->json(['success'=>true,'message'=>'Profile Updated Successfully','user_info'=>$user]);
        }
        catch(\Eception $e)
        {
                return $this->sendError($e->getMessage());
        }   
    }

    public function receptionist_store(Request $request)
    {
        try
        {
            $receptionist = new Receptionist();
            $validator = Validator::make($request->all(),[
                'first_name' =>'required|string',
                'last_name' =>'required|string',
                'ssn'=>'required|string',
                'cross_link'=>'required|string',
                'image'=>'image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
				'photos.*'=>'image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
            ]);
            if($validator->fails())
            {
                return $this->sendError($validator->errors()->first());
            }
			
            $profile = null;
            $photos = [];

            if($request->file('image')){
				$file = $request->image;
                    $fileName = md5($file->getClientOriginalName().uniqid()).'smartaxxx-.'.$file->getClientOriginalExtension() ;
                    $file->move('uploads/receptionist/', $fileName);  
                    $profile = asset('uploads/receptionist/'.$fileName);                
            }   
			  if($request->photos){
                foreach ($request->photos as $key=> $files) {
                    $fileNamew = md5($files->getClientOriginalName().uniqid()).'smartaxxx-.'.$files->getClientOriginalExtension() ;
                    $files->move('uploads/receptionist/', $fileNamew);  
                    $photos[$key] = asset('uploads/receptionist/'.$fileNamew);
                }
            }  
            $receptionist->first_name = $request->first_name;
            $receptionist->last_name = $request->last_name;
            $receptionist->ssn = $request->ssn;
            $receptionist->cross_link = $request->cross_link;
            $receptionist->image = $profile;
			$receptionist->photos = json_encode($photos);
            $receptionist->user_id = Auth::user()->id;
            $receptionist->save();
            return response()->json(['success'=>true,'message'=>'Receptionist Created Successfully','receptionist'=>$receptionist]);
        }
        catch(\Eception $e)
        {
            return $this->sendError($e->getMessage());
        }   
    }
    
    public function receptionist()
    {
        try
        {
            $receptionist = Receptionist::all();
            return response()->json(['success'=>true,'receptionist'=>$receptionist]);
        }
        catch(\Eception $e)
        {
                return $this->sendError($e->getMessage());
        }
    }
    
    public function receptionist_detail($id)
    {
        try
        {
            $receptionist = Receptionist::with(['tax_payer','diligenceverification','internalaudit','irsstatus','paymentmethod','refferals','refundDispersements','refundInvoices'])->find($id);
            return response()->json(['success'=>true,'receptionist'=>$receptionist]);
        }
        catch(\Eception $e)
        {
                return $this->sendError($e->getMessage());
        }
    }
}
