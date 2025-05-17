<x-filament::page>
    <div class="space-y-6">
        @foreach ($subjectsWithMaterials as $subject) <!-- Gunakan properti yang sudah didefinisikan -->
            <x-filament::card>
                <h2 class="text-xl font-bold text-primary-600 flex items-center gap-2">
                    <x-heroicon-o-book-open class="w-5 h-5 text-primary-500" />
                    {{ $subject->name }}
                </h2>
                <ul class="list-disc list-inside mt-2 text-sm text-gray-700">
                    @forelse ($subject->materials as $material)
                        <li>
                            <strong>{{ $material->title }}</strong> ({{ strtoupper($material->file_type) }})
                            <br>
                            <a href="{{ Storage::url($material->file_path) }}" class="text-blue-600 hover:underline" target="_blank">Lihat File</a>
                            @if ($material->ai_summary_enabled && $material->summary)
                                <div class="mt-1 italic text-gray-500">
                                    Ringkasan: {{ $material->summary }}
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="text-gray-400">Belum ada materi</li>
                    @endforelse
                </ul>
            </x-filament::card>
        @endforeach
    </div>
</x-filament::page>
