<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    //

    public function __construct()
    {
        $sent = false;
    }
    public function receive(Request $request)
    {
        $data = $request->all();
        //get the user’s id
        error_log("data" . json_encode($data));
        // echo json_encode($data);
        $id = $data["entry"][0]["messaging"][0]["sender"]["id"];



        // if (!empty($data["entry"][0]["messaging"][0]["message"])) {
        //     //$this->sendTextMessage($id, "Hello");
        //     $this->sendReply($id, "hello Hello");
        // }


        if (
            !empty($data["entry"][0]["messaging"][0]["message"]["attachments"]) &&
            $data["entry"][0]["messaging"][0]["message"]["attachments"]["type"] == "location"
        ) {
            //$this->sendTextMessage($id, "Hello");
            $this->sendReply($id, "user sends location");
            $this->sendReply($id, "user is in lat= " . $data["entry"][0]["messaging"][0]["message"]["attachments"]["payload"]["coordinates"]["lat"] .  "and long is = " . $data["entry"][0]["messaging"][0]["message"]["attachments"]["payload"]["coordinates"]["long"]);
        }

        // if (!empty($data["entry"][0]["messaging"][0]["read"]["watermark"])) {
        //        // $this->sendTextMessage($id, "ya 7aggar");
        //         $this->sendReply($id, "haha 9ritou lmessage");
        //         sleep(10000);
        // }
    }

    private function sendTextMessage($recipientId, $messageText)
    {
        $messageData = [
            "recipient" => [
                "id" => $recipientId
            ],
            "message" => [
                "text" => $messageText
            ]
        ];
        error_log("recipientId" . $recipientId);
        $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token=' . env("PAGE_ACCESS_TOKEN"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        curl_exec($ch);
    }


    private function getStatusTextMessage($recipientId, $senderId)
    {
        $messageData = [
            "sender" => [
                "id" => $senderId
            ],
            "recipient" => [
                "id" => $recipientId
            ]


        ];


        $ch = curl_init('https://graph.facebook.com/v6.0/me/message_reads?access_token=' . env("PAGE_ACCESS_TOKEN"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        $output = curl_exec($ch);
        error_log($output);
    }



    private function prepareReply($fb_id)
    {
        $post_data = ["recipient" => ["id" => $fb_id,], "sender_action" => "MARK_SEEN"];
        $this->sendMessage($post_data);
        $post_data = ["recipient" => ["id" => $fb_id,], "sender_action" => "TYPING_ON"];
        $this->sendMessage($post_data);
    }
    private function sendMessage($post_data)
    {

        $access_token = env('PAGE_ACCESS_TOKEN');
        $url = "https://graph.facebook.com/v2.6/me/messages?access_token={$access_token}";
        $headers = array(
            "Content-Type: application/json"
        );
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($curl);
        error_log($output);
    }
    private function endReply($fb_id)
    {
        $post_data = ["recipient" => ["id" => $fb_id,], "sender_action" => "TYPING_OFF"];
        $this->sendMessage($post_data);
    }

    private function sendReply($facebook_id, $text)
    {
        $this->prepareReply($facebook_id);
        $post_data =
            [
                "recipient" => [
                    "id" => $facebook_id,
                ],
                "message" => [
                    "text" => $text,
                ]
            ];
        $this->sendMessage($post_data);
        $this->endReply($facebook_id);
    }
}
