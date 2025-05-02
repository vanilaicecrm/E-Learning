<div>
    <style>
       .active-question  {
            border: 2px solid darkblue;
       }

       .no-hover:hover {
            background-color: transparent !important;
            color: inherit !important;
       }
    </style>
  <div class="container mt-4">
    <div class="row">
        <h4>{{$package->name}}</h4>
        <div class="col-md-8">
            <div id="question-container">
                <div class="card question-card">
                    <div class="countdown-timer mb-4 text-success" id="countdown">
                        Waktu tersisa: <span id="time">00:00:00</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{$currentPackageQuestion  ->question->question}}</p>
                        @foreach($currentPackageQuestion->question->options as $item)
                        <div class="form-check">
                            <input class="form-check-input"
                            wire:model="selectedAnswers.{{$currentPackageQuestion->question_id}}"
                            wire:click="saveAnswer({{$currentPackageQuestion->question_id}}, {{$item->id}})" 
                            type="radio" 
                            name="question" 
                            value="{{$item->id}}"
                            @if($ujianAnswers->isEmpty() || !$ujianAnswers->contains('option_id', $item->id))
                            @else
                                checked
                            @endif>
                            <label class="form-check-label">{{$item->option_text}}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card question-navigation">
                <div class="card-body">
                    <h5 class="card-title">Navigasi</h5>
                    <div class="btn-group-flex" role="group">
                    @foreach($questions as $index => $item)
                        @php
                            $isAnswered = isset($selectedAnswers[$item->question_id]) && !is_null($selectedAnswers[$item->question_id]);
                            $isActive = $currentPackageQuestion->question_id == $item->question_id;
                        @endphp
                         <div class="col-2 mb-2">
                            <button 
                                type="button" 
                                wire:click="goToQuestion({{$index}})" 
                                class="btn {{ $isAnswered ? 'btn-primary' : 'btn-outline-primary no-hover' }}" {{$isActive ? 'question'}}>{{$index+1}}</button>
                         </div>    
                    @endforeach
                    </div>
                    <button type="button" class="btn btn-primary mt-3 w-100">Submit</button>
                </div>
            </div>
        </div>
    </div>
  </div>
  <script>
  document.addEvenListener('DOMContentLoaded', function(){
    let timeLeft = {{$timeLeft}};
    startCountdown(timeLeft, document.getElementById('time'));
  });
    function startCountdown(duration, display) {
        let timer = duration, minute, seconds;
        setInterval(function() {
            hours = parseInt(timer / 3600, 10);
            minutes = parseInt((timer % 3600) / 60, 10);
            seconds = parseInt(timer % 60, 10);

            hours = hours < 10 ? "0" + hours : hours;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = hours + ":" +minutes + ":" + seconds;

            if (--timer < 0) {
                timer = 0;
            }
        }, 100);
    }

    window.onload = function() {
        const duration = 5 * 60;
        const display = document.querySelector('#time');
        startCountdown(duration, display);
    }
  </script>
</div>
