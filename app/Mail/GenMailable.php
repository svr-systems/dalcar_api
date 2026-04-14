<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenMailable extends Mailable
{
  use Queueable, SerializesModels;

  public $data;
  public $subject_text;
  public $view_name;
  public $attachments_data;

  public function __construct($data, $subject, $view, array $attachments = [])
  {
    $this->data = $data;
    $this->subject_text = $subject;
    $this->view_name = $view;
    $this->attachments_data = $attachments;
  }

  public function build()
  {
    $mail = $this
      ->subject($this->subject_text)
      ->view('email.' . $this->view_name, [
        'data' => $this->data,
      ]);

    foreach ($this->attachments_data as $attachment) {
      if (
        empty($attachment['data']) ||
        empty($attachment['name']) ||
        empty($attachment['mime'])
      ) {
        continue;
      }

      $mail->attachData(
        $attachment['data'],
        $attachment['name'],
        ['mime' => $attachment['mime']]
      );
    }

    return $mail;
  }
}