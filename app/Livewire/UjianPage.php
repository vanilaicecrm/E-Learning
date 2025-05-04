<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Package;
use App\Models\Ujian;
use App\Models\UjianAnswer;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class UjianPage extends Component
{
    public $package;
    public $ujian;
    public $questions;
    public $currentPackageQuestion;
    public $timeLeft;
    public $ujianAnswers;
    public $selectedAnswers = [];

    public function mount($packageId)
    {
        $this->package = Package::with('questions.question.options')->find($packageId);

        if ($this->package) {
            $this->questions = $this->package->questions;

            if ($this->questions->isNotEmpty()) {
                $this->currentPackageQuestion = $this->questions->first();
            }
        }

        $this->ujian = Ujian::where('user_id', Auth::id())
            ->where('package_id', $this->package->id)
            ->whereNull('finished_at')
            ->first();

        if (!$this->ujian) {
            $startedAt = now();
            $durationInSecond = $this->package->duration * 60;

            $this->ujian = Ujian::create([
                'user_id'    => Auth::id(),
                'package_id' => $this->package->id,
                'duration'   => $durationInSecond,
                'started_at' => $startedAt
            ]);

            foreach ($this->questions as $question) {
                UjianAnswer::create([
                    'ujian_id' => $this->ujian->id,
                    'question_id'     => $question->question_id,
                    'option_id'       => null,
                    'score'           => 0
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
        $this->ujianAnswers = UjianAnswer::where('ujian_id', $this->ujian->id)->get();
        $this->calculateTimeLeft();
    }

    protected function calculateTimeLeft()
    {
        $now = time();
        $startedAt = strtotime($this->ujian->started_at);
        $duration = $this->ujian->duration;

        $this->timeLeft = $this->ujian->finished_at ? 0 : max(0, $duration - ($now - $startedAt));
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
                'score'     => $score
            ]);
        }
        $this->selectedAnswers[$questionId] = $optionId;
        $this->ujianAnswers = UjianAnswer::where('ujian_id', $this->ujian->id)->get();
        $this->calculateTimeLeft();
        $this->dispatch('refreshComponent');
    }

    public function submit()
    {
        $this->ujian->update([
            'finished_at' => now()
        ]);

        $this->calculateTimeLeft();
        Notification::make()
            ->title('Sukses Menyimpan Jawaban')
            ->success()
            ->send();
    }
}
