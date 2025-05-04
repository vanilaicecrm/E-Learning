<div class="grid grid-cols-1 md:grid-cols-3 gap-4"> 
    <div class="md:col-span-2 bg-white shadow-md rounded-lg p-6">
        @if($ujian->finished_at == null)
            <div class="text-center mb-4">
                <h2 class="text-xl font-bold mb-2">Sisa Waktu</h2>
                @if($timeLeft >= 0)
                    <div id="time" class="text-gray-700 text-lg">00:00:00</div>
                @else
                    <div class="text-gray-700 text-lg">HABIS</div>
                @endif
            </div>
        @endif

        <h2 class="text-2xl font-bold mb-4">{{ $package->name }}</h2>
        <p class="text-gray-700">{{ $currentPackageQuestion->question->question }}</p>

        <div class="mx-4">
            @foreach($currentPackageQuestion->question->options as $item)
                @php
                    $answer = $ujianAnswers->where('question_id', $currentPackageQuestion->question_id)->first();
                    $selected = $answer ? $answer->option_id == $item->id : false; 
                @endphp

                <label class="block mb-2">
                    <input 
                        id="option_{{$currentPackageQuestion->question_id}}_{{ $item->id }}"
                        type="radio"
                        name="option_{{$currentPackageQuestion->question_id}}"
                        class="mr-2"
                        wire:click="saveAnswer({{ $currentPackageQuestion->question_id }}, {{ $item->id }})"
                        value="{{ $item->id }}"
                        @if($ujian->finished_at != null || $timeLeft <= 0) disabled @endif
                        @if($selected) checked @endif>
                    {{ $item->option_text }}
                </label>
            @endforeach
        </div>
    </div>

    <div class="md:col-span-1 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Navigasi Soal</h2>
        <p class="text-gray-700 mb-4">Pilih tombol di bawah ini untuk berganti soal</p>
        @foreach($questions as $index => $item)
            @php
                $isAnswered = isset($selectedAnswers[$item->question_id]) && !is_null($selectedAnswers[$item->question_id]);
                $isActive = $currentPackageQuestion->question->id === $item->question_id;
            @endphp

            <x-filament::button
                wire:click="goToQuestion({{ $item->id }})"
                class="mt-2"
                color="{{ $isActive ? 'warning' : ($isAnswered ? 'success' : 'gray') }}"
            >
                {{ $index+1 }}
            </x-filament::button>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
        @endforeach

        @if($ujian->finished_at == null)
            <x-filament::button wire:click="submit" class="btn w-full bg-blue-500 text-white py-2 rounded mt-3" onclick="return confirm('Apakah anda yakin ingin mengirim jawaban ini?')">Submit</x-filament::button> 
        @endif
    </div>

    @if($ujian->finished_at != null)
        <div class="bg-green-100 border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
            <a href="{{ url('/admin/ujians')}}" class="underline">Lihat Hasil Pengerjaan</a>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($ujian->finished_at == null && $timeLeft >= 0)
            let timeLeft = '{{ $timeLeft }}';
            startCountdown(parseInt(timeLeft), document.getElementById('time'));
        @endif

        window.addEventListener('refreshComponent', () => {
            Livewire.emit('refreshComponent');
        });
    });

    function startCountdown(duration, display) {
        let timer = duration;
        const interval = setInterval(function () {
            let hours = parseInt(timer / 3600, 10);
            let minutes = parseInt((timer % 3600) / 60, 10);
            let seconds = parseInt(timer % 60, 10);

            hours = hours < 10 ? "0" + hours : hours;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            display.textContent = hours + ":" + minutes + ":" + seconds;

            if (--timer < 0) {
                clearInterval(interval);
                display.textContent = "00:00:00";
                alert('Waktu ujian telah habis!');
                Livewire.emit('refreshComponent');
            }
        }, 1000);
    }
</script>
