<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentMail extends Mailable{

    use Queueable, SerializesModels;

    public $comment;

    public function __construct($comment){

        $this->comment = $comment;

    }

    public function build(){

        return $this->subject('This is Testing Mail')
                    ->view('comment.comment');
    }
}
