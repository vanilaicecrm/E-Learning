<?php

// File: app/Livewire/Ujian.php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Package;
use App\Models\Question;
use App\Models\Ujian as UjianModel; // ✅ Alias model Ujian
use App\Models\UjianAnswer;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Auth;

class UjianPage extends Component // ✅ Ganti nama class dari `Ujian` ke `UjianPage`
{
    public $package;
    public $questions;
    public $ujian;
    public $currentPackageQuestion;
    public $timeLeft;
    public $ujianAnswers;
    public $selectedAnswers = [];

    public function mount($id)
    {
        $this->package = Package::with('questions.question.options')->find($id);
        if ($this->package) {
            $this->questions = $this->package->questions;
            if ($this->questions->isNotEmpty()) {
                $this->currentPackageQuestion = $this->questions->first();
            }
        }

        $this->ujian = UjianModel::where('user_id', Auth::id())
            ->where('package_id', $this->package->id)
            ->whereNull('finished_at')
            ->first();

        if (!$this->ujian) {
            $startedAt = now();
            $durationInSecond = $this->package->duration * 60;

            $this->ujian = UjianModel::create([
                'user_id' => Auth::id(),
                'package_id' => $this->package->id,
                'duration' => $durationInSecond,
                'started_at' => $startedAt,
            ]);

            foreach ($this->questions as $question) {
                UjianAnswer::create([
                    'ujian_id' => $this->ujian->id,
                    'question_id' => $question->question_id,
                    'option_id' => null,
                    'score' => 0
                ]);
            }
        }

        $this->ujianAnswers = UjianAnswer::where('ujian_id', $this->ujian->id)->get();

        foreach ($this->ujianAnswers as $answer) {
            $this->selectedAnswers[$answer->question_id] = $answer->option_id;
        }

        $this->calculateTimeLeft();
    }

    public function render()
    {
        return view('livewire.ujian');
    }

    public function goToQuestion($package_question_id)
    {
        $this->currentPackageQuestion = $this->questions->where('id', $package_question_id)->first();
    }

    public function calculateTimeLeft()
    {
        if ($this->ujian->finished_at) {
            $this->timeLeft = 0;
            return;
        }

        $now = time();
        $startedAt = strtotime($this->ujian->started_at);

        $sisaWaktu = $now - $startedAt;
        $this->timeLeft = $sisaWaktu < 0 ? 0 : max(0, $this->ujian->duration - $sisaWaktu);
    }

    public function saveAnswer($questionId, $optionId)
    {
        $option = QuestionOption::find($optionId);
        $score = $option->score ?? 0;

        $ujianAnswer = UjianAnswer::where('ujian_id', $this->ujian->id)
            ->where('question_id', $questionId)
            ->first();

        if ($ujianAnswer) {
            $ujianAnswer->update([
                'option_id' => $optionId,
                'score' => $score
            ]);
        }

        $this->ujianAnswers = UjianAnswer::where('ujian_id', $this->ujian->id)->get();

        $this->calculateTimeLeft();
    }
}
