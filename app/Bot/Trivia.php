<?php

namespace App\Bot;


use Illuminate\Support\Facades\Cache;

class Trivia
{
    public static $NEW_QUESTION = "new";
    public static $ANSWER = "answer";

    private $question;
    private $options;
    private $solution;

    public function __construct(array $data)
    {
        $this->question = $data["question"];
        $answer = $data["correct_answer"];
        $this->options = $data["incorrect_answers"];
        $this->options[] = $answer;
        shuffle($this->options);
        $this->solution = $answer;
    }

    public static function getNew()
    {
        //clear any past solutions left in the cache
        Cache::forget("solution");

        //make API call and decode result to get general-knowledge trivia question
        $ch = curl_init("https://opentdb.com/api.php?amount=1&category=9&type=multiple");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = json_decode(curl_exec($ch), true)["results"][0];

        return new Trivia($result);
    }

    public static function checkAnswer($answer)
    {
        $solution = Cache::get("solution");
        if ($solution == strtolower($answer)) {
            $response = "Correct!";
        } else {
            $response = "Wrong. Correct answer is $solution";
        }
        //clear solution
        Cache::forget("solution");
        return $response;
    }

    public function toMessage()
    {
        //compose message
        $response = "Question: $this->question.\nOptions:";
        $letters = ["a", "b", "c", "d"];
        foreach ($this->options as $i => $option) {
            $response .= "\n{$letters[$i]}: $option";
            if ($this->solution == $option) {
                Cache::forever("solution", $letters[$i]);
            }
        }

        return ["text" => $response];
    }
}
