<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Termo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceApplication;
class AgentController extends Controller
{
    public function verifyMachine(Request $request){
        $data=$request->json()->all();

        $device= Device::updateOrCreate([
            ['hostaname' => $data['hostname'],
            [
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'ip_address' => $request->ip(),
                'last_seen_at' => now(),
            ]]
        ]);
        
        $termo=Termo::where('hostname',$data['hostname'])
        ->where('cpf',$data['cpf'])
        ->first();
        if($termo){
        return response()->json(["allowed"=>True,"action"=>"block"]);
        }else{
            return response()->json(["allowed"=>False,"action"=>"block"]);
        }

      


        

    }



    public function getApplications(Request $request){
        $data=$request->all();
        foreach($data['applications'] as $app){
          $application= new DeviceApplication;
          $application->name=$app['Name'];
          $appliaciton->version=$app['Version'];
          $aplication->save();
        }
        
        return response()->json($data);
    }


    public function getUserInformation(){
         $applications=$data['applications'];
        foreach($appliactions as $application){
            Log::debug($appliaciton);
        }
    }


    public function sendWorkingHours(){
        
    }


}
