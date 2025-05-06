<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Buat pertanyaan
            $question = Question::create([
                'question' => $row['question'],
                'explanation' => $row['explanation'] ?? null,
            ]);

            // Loop opsi 1–4
            for ($i = 1; $i <= 4; $i++) {
                $optionKey = 'option_' . $i;
                $scoreKey = 'score_' . $i;

                if (!empty($row[$optionKey])) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $row[$optionKey],
                        'score' => $row[$scoreKey] ?? 0,
                    ]);
                }
            }
        }
    }
}
