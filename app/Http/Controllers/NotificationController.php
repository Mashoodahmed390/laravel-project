<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\CommentNotification;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\Client as mongodb;
use App\Mail\CommentMail;

class NotificationController extends Controller
{
    // $offerData = [
    //     'name' => 'BOGO',
    //     'body' => 'You received an offer.',
    //     'thanks' => 'Thank you',
    //     'offerText' => 'Check out the offer',
    //     'offerUrl' => url('/'),
    //     'offer_id' => 007
    // ];

    public function send_notification($data)
    {
        // $jwt = $request->bearerToken();
        // $key = "example_key";
        // $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        //$user = User::find($decoded->data->id);
        //dd($user);

        $curr_user = (new mongodb)->laravel_project->users->findOne(["_id" => new \MongoDB\BSON\ObjectId($data['curr_user_id'])]);     //User::find($data['curr_user_id']);
        $post_owner_id = (new mongodb)->laravel_project->posts->findOne(["_id"=> new \MongoDB\BSON\ObjectId($data['post'])]);   //  User::find($data['post_id']);
        $receiver = (new mongodb)->laravel_project->users->findOne(["_id"=>new \MongoDB\BSON\ObjectId($post_owner_id["user_id"])]);

        $data = [
            'name' => $curr_user->name,
            'body' => $data['body'],//$user->comment->body,
            'team' => 'Back-End Team Mashood',
            'url' => url('/api/get/post/'.$data['post']),
            'urlname' => 'Click to get to Post'

        ];
        //dd($receiver);
        //$receiver->notify(New CommentNotification($data));
        \Mail::to($receiver->email)->send(new CommentMail($data));
    }
}
