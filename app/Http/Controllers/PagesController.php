<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
class PagesController extends Controller
{
    public function getPresignedUrl(Request $request){
        $match =preg_split("/[.]/",$request->fileName);
        $extension = '.'.$match[count($match)-1];
        // dd($extension);
        $s = strtoupper(md5(uniqid(rand(),true))); 
        $guidText = 
            substr($s,0,8) . '-' . 
            substr($s,8,4) . '-' . 
            substr($s,12,4). '-' . 
            substr($s,16,4). '-' . 
            substr($s,20);
            // dd(env('AWS_ACCESS_KEY_ID'));
        $s3Client = new S3Client([
            'region'=>env('AWS_DEFAULT_REGION'),
            'version'=>'2006-03-01',
            'credentials'=>[
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY')
            ]
        ]);
        $formInputs = ['acl' => 'public-read'];
        $options = [
            ['acl' => 'public-read'],
            ['bucket' => env('AWS_BUCKET')],
            ['starts-with', '$key', 'tmp/'],
            ["starts-with", '$Content-Type', "image/"],
        ];
        $expires = '+2 hours';
        $postObject = new \Aws\S3\PostObjectV4(
            $s3Client,
            env('AWS_BUCKET'),
            $formInputs,
            $options,
            $expires
        );
        $formAttributes = $postObject->getFormAttributes();
        $formInputs = $postObject->getFormInputs();
        dd($formInputs);
        // $cmd = $s3Client->getCommand('GetObject',[
        //     'Bucket' => ,
        //     'Key' => 'tmp/hsvO8U41nd46TAEqHaBMFXxirihuBwW9faFKaVWA.jpeg',
        //     'body'=>'Hello World!'
        // ]);
        // $signedUrl = $s3Client->createPresignedRequest($cmd,'+30 minutes');
        // $response = (object)[];
        // // dd($signedUrl);
        // $response->url = (string)$signedUrl->getUri();
        // // $response->key = $guidText;
        // return response()->json($response);
    }
    public function uploadFile(Request $request){
        $files=[];
        foreach ($request->all() as $key => $item) {
            dd($request->{$key});
            if($request->hasFile($key)){
               $files[]= Storage::disk('s3')->putFile('tmp', $request->{$key}, 'public');
            //    $files[]= $request->file($key)->store('tmp','s3');
            }
        return $files;
        
        }
    }
}