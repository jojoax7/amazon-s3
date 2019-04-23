<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Illuminate\Support\Str;
class PagesController extends Controller
{
    public function getPresignedUrl(Request $request){
        //get extension file
        // $match =preg_split("/[.]/",$request->fileName);
        // $extension = '.'.$match[count($match)-1];
        //get random name
        // $hashName = Str::random(40).$extension;
        // how to use params:
        // https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.S3.PostObjectV4.html
        //set connection
        $s3Client = new S3Client([
            'region'=>env('AWS_DEFAULT_REGION'),
            'version'=>'latest',
            'credentials'=>[
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY')
            ]
        ]);
        //get bucket name
        $bucket=  env('AWS_BUCKET');
        // Associative array of form input fields
        $formInputs = [
            'acl' => 'public-read',//file permissions
            'key' => 'tmp/${filename}'//set new random name
        ];
        // Policy condition options
        $policy = [
            ['acl' => 'public-read'],//match file permissions
            ['bucket' =>$bucket],
            ['starts-with', '$key', 'tmp/'],//match with directory and name
            // ["starts-with", '$Content-Type', "image/"],//match tipe of media
            ['success_action_status'=>'201']
            
        ];
        // link time of expiration 
        $expires = '+1 hours';
        $postObject = new \Aws\S3\PostObjectV4(
            $s3Client,
            $bucket,
            $formInputs,
            $policy,
            $expires
        );
        //get form attributes
        $formAttributes = $postObject->getFormAttributes();
        //get form inputs
        $formInputs = $postObject->getFormInputs();
        //merge object
        $getPresignedURL = array_merge($formAttributes,$formInputs);
       
        return response()->json($getPresignedURL);
    }

    public function uploadFile(Request $request){
        //save files with use streams on php
        $files=[];
        foreach ($request->all() as $key => $item) {
            if($request->hasFile($key)){
               $files[]= Storage::disk('s3')->putFile('tmp', $request->{$key}, 'public');
            }
        return $files;
        
        }
    }
    public function move(){//example
        //initial file directory - final file directory
        Storage::disk('s3')->move('tmp/004BD1B0-6D8D-3F2D-18DC-9743B46FED36.jpg','system/c1912/t2809/004BD1B0-6D8D-3F2D-18DC-9743B46FED36.jpg');
        return 'Success';
    }
}
