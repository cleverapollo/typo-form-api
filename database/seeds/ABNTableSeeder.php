<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Question;
use App\Models\Response;

class ABNTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $answerTypes = array(
            'Abn',
            'BusinessName',
            'EntityName',
            'EntityTypeName',
            'Message'
        );
        $questions = Question::where('question_type_id', 13)->get();
        foreach ($questions as $question) {
            $responses = $question->responses()->get();
            foreach ($responses as $response) {
                try {
                    $value = json_decode($response->response)->Abn;
                    $query = urlencode(str_replace(' ', '', $value));
                    $handle = curl_init();
                    curl_setopt_array($handle, [
                        CURLOPT_RETURNTRANSFER => 1,
                        CURLOPT_URL => 'https://abr.business.gov.au/json/AbnDetails.aspx?abn=' . $query . '&callback=callback&guid=9c1fe65f-650b-4ea8-838c-aa03d946db12',
                        CURLOPT_HTTPHEADER => [
                            'Content-Type: application/x-www-form-urlencoded'
                        ]
                    ]);
                    $data = curl_exec($handle);
                    $response_value = json_decode(substr($data, 9, -1));
                    $form = $response->form()->get();
                    $question_id = $question->id;
                    $form->responses()->where('question_id', $question_id)->delete();
                    foreach ($answerTypes as $answerType) {
                        $answer = $question->answers()->where('answer', $answerType)->first();
                        if ($answer) {
                            $response = $response_value->{$answerType};
                            if (is_array($response)) {
                                if (count($response)) {
                                    $response = $response[0];
                                } else {
                                    $response = '';
                                }
                            }
                            $form->responses()->create([
                                'question_id' => $question_id,
                                'response' => $response,
                                'answer_id' => $answer->id,
                                'order' => 1
                            ]);
                        }
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                }
            }
        }
    }
}
