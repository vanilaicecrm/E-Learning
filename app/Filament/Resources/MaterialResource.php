<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Models\Material;
use App\Models\Subject;
use App\Services\GeminiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Curriculum Management';
    protected static ?string $modelLabel = 'Teaching Material';
    protected static ?string $navigationLabel = 'Teaching Materials';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Material Information')
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(Subject::query()->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->columnSpan(1)
                            ->reactive(),

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('File Upload')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Material File')
                            ->required()
                            ->directory('materials')
                            ->disk('public')
                            ->preserveFilenames()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            ])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->previewable()
                            ->openable()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $ext = strtolower($state->getClientOriginalExtension());
                                    $allowed = ['pdf', 'docx', 'pptx'];
                                    
                                    if (!in_array($ext, $allowed)) {
                                        Notification::make()
                                            ->title('Format File Tidak Didukung')
                                            ->body('Hanya file PDF, DOCX, dan PPTX yang diperbolehkan')
                                            ->danger()
                                            ->send();
                                        return;
                                    }
                                    
                                    // Additional check for image-based PDFs
                                    if ($ext === 'pdf') {
                                        $tempPath = $state->getRealPath();
                                        try {
                                            $text = Pdf::getText($tempPath);
                                            if (empty(trim($text))) {
                                                Notification::make()
                                                    ->title('PDF Mungkin Berisi Gambar')
                                                    ->body('File PDF ini mungkin scan/gambar. Ringkasan otomatis mungkin memerlukan OCR.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        } catch (\Exception $e) {
                                            Log::error("PDF Preview Error: " . $e->getMessage());
                                        }
                                    }
                                    
                                    $set('file_type', $ext);
                                }
                            })
                            ->columnSpan(2),

                        Forms\Components\Select::make('file_type')
                            ->label('File Type')
                            ->options([
                                'pdf' => 'PDF',
                                'docx' => 'Word Document',
                                'pptx' => 'PowerPoint',
                            ])
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('ai_summary_enabled')
                            ->label('Enable AI Summary')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1)
                            ->helperText('Aktifkan untuk membuat ringkasan otomatis'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('AI Summary')
                    ->schema([
                        Forms\Components\Textarea::make('ringkasan')
                            ->label('Summary')
                            ->nullable()
                            ->columnSpanFull()
                            ->disabled(fn(string $operation): bool => $operation === 'edit')
                            ->helperText('Ringkasan otomatis akan dibuat ketika Anda mengklik tombol "Ringkas"'),
                    ])
                    ->collapsible()
                    ->collapsed(fn(string $operation): bool => $operation === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable()
                    ->searchable()
                    ->description(fn (Material $record): string => $record->subject->description ?? ''),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Material $record): ?string {
                        $state = $record->title;
                        return strlen($state) > 40 ? $state : null;
                    }),

                Tables\Columns\IconColumn::make('file_type')
                    ->label('Type')
                    ->icon(fn (string $state): string => match ($state) {
                        'pdf' => 'heroicon-o-document-text',
                        'docx' => 'heroicon-o-document',
                        'pptx' => 'heroicon-o-presentation-chart-bar',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'docx' => 'primary',
                        'pptx' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\IconColumn::make('ai_summary_enabled')
                    ->label('AI Enabled')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Filter by Subject')
                    ->options(Subject::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('file_type')
                    ->label('File Type')
                    ->options([
                        'pdf' => 'PDF',
                        'docx' => 'Word',
                        'pptx' => 'PowerPoint',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil'),
                
                Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Material $record): string => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
                
               Action::make('ringkas')
    ->icon('heroicon-o-sparkles')
    ->color('primary')
    ->visible(fn($record) => $record->ai_summary_enabled)
    ->requiresConfirmation()
    ->modalHeading('Buat Ringkasan Otomatis')
    ->modalDescription('Apakah Anda yakin ingin membuat ringkasan otomatis menggunakan AI? Proses ini mungkin memakan waktu beberapa detik.')
    ->action(function (Material $record) {
        try {
            // 1. Verifikasi file ada
            $filePath = Storage::disk('public')->path($record->file_path);
            if (!file_exists($filePath)) {
                throw new \Exception("File tidak ditemukan di server");
            }

            // 2. Ekstrak konten
            $content = self::extractFileContent($filePath, $record->file_type);
            
            if (empty($content)) {
                throw new \Exception("Tidak dapat mengekstrak konten dari file");
            }

            // 3. Log konten untuk debugging (first 200 chars)
            Log::debug("Konten yang akan diringkas (potongan): ".mb_substr($content, 0, 200));

            // 4. Panggil layanan AI
            $summary = app(GeminiService::class)->ringkas($content);
            
            if (empty($summary)) {
                throw new \Exception("Layanan AI tidak mengembalikan ringkasan. Coba lagi nanti.");
            }

            // 5. Simpan hasil
            $record->update(['ringkasan' => $summary]);
            
            Notification::make()
                ->title('Ringkasan Berhasil Dibuat')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Log::error("AI Summary Error - Material ID: {$record->id} - " . $e->getMessage());
            
            $errorMessage = $e->getMessage();
            
            // Tambahkan saran solusi untuk error tertentu
            if (str_contains($errorMessage, 'Layanan AI tidak mengembalikan ringkasan')) {
                $errorMessage .= "\n\nSaran: Coba upload file dengan konten teks yang lebih jelas atau kurangi ukuran file.";
            }
            
            Notification::make()
                ->title('Gagal Membuat Ringkasan')
                ->body($errorMessage)
                ->danger()
                ->send();
        }
    })
                    ->hidden(fn($record) => !$record->ai_summary_enabled),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No teaching materials found')
            ->emptyStateDescription('Upload your first teaching material by clicking the button below')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Upload Material')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    private static function extractFileContent(string $path, string $ext): ?string
    {
        try {
            if (!file_exists($path)) {
                throw new \Exception("File tidak ditemukan di path: {$path}");
            }

            if (!is_readable($path)) {
                throw new \Exception("File tidak dapat dibaca, periksa permission");
            }

            $content = match (strtolower($ext)) {
                'pdf' => self::extractPdfContent($path),
                'docx' => self::extractDocxContent($path),
                'pptx' => self::extractPptxContent($path),
                default => throw new \Exception("Format file tidak didukung: {$ext}"),
            };

            if (empty(trim($content))) {
                throw new \Exception("File berhasil dibaca tetapi konten kosong");
            }

            return $content;
        } catch (\Exception $e) {
            Log::error("File Extraction Error: " . $e->getMessage());
            return null;
        }
    }

    private static function extractPdfContent(string $path): string {
    try {
        // Method 1: pdftotext (cepat untuk PDF teks)
        $popplerPath = config('services.pdftotext.path');
        if ($popplerPath && file_exists($popplerPath)) {
            $text = Pdf::getText($path, $popplerPath);
            if (!empty(trim($text))) return $text;
        }

        // Method 2: PDF Parser (untuk PDF kompleks)
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();
        if (!empty(trim($text))) return $text;

        // Method 3: OCR (untuk PDF gambar)
        if (self::isOcrAvailable()) {
            $ocrText = self::extractTextViaOcr($path);
            if (!empty(trim($ocrText))) return $ocrText;
        }

        throw new \Exception("PDF tidak mengandung teks yang bisa diekstrak");
    } catch (\Exception $e) {
        Log::error("PDF Extraction Error: " . $e->getMessage());
        throw new \Exception("Gagal mengekstrak teks dari PDF: " . $e->getMessage());
    }
}
    private static function extractDocxContent(string $path): string
    {
        try {
            $phpWord = WordIOFactory::load($path);
            $text = '';
            
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        foreach ($element->getElements() as $textElement) {
                            if ($textElement instanceof \PhpOffice\PhpWord\Element\Text) {
                                $text .= $textElement->getText();
                            }
                        }
                        $text .= "\n";
                    } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }
            
            return $text;
        } catch (\Exception $e) {
            Log::error("DOCX Extraction Error: " . $e->getMessage());
            throw new \Exception("Gagal mengekstrak teks dari DOCX: " . $e->getMessage());
        }
    }

    private static function extractPptxContent(string $path): string
    {
        try {
            $presentation = PresentationIOFactory::load($path);
            $text = '';
            
            foreach ($presentation->getAllSlides() as $slide) {
                foreach ($slide->getShapeCollection() as $shape) {
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                        $text .= $shape->getPlainText() . "\n";
                    }
                    
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\Table) {
                        foreach ($shape->getRows() as $row) {
                            foreach ($row->getCells() as $cell) {
                                $text .= $cell->getPlainText() . "\t";
                            }
                            $text .= "\n";
                        }
                    }
                }
            }
            
            return $text;
        } catch (\Exception $e) {
            Log::error("PPTX Extraction Error: " . $e->getMessage());
            throw new \Exception("Gagal mengekstrak teks dari PPTX: " . $e->getMessage());
        }
    }

    private static function isOcrAvailable(): bool
    {
        $tesseractPath = config('services.tesseract.path');
        return !empty($tesseractPath) && file_exists($tesseractPath);
    }

    private static function extractTextViaOcr(string $path): string
    {
        try {
            $tesseractPath = config('services.tesseract.path');
            $outputFile = tempnam(sys_get_temp_dir(), 'ocr');
            
            $command = sprintf(
                '%s "%s" "%s" -l eng+ind --psm 6',
                escapeshellarg($tesseractPath),
                escapeshellarg($path),
                escapeshellarg($outputFile)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception("OCR failed with code: $returnCode");
            }
            
            $text = file_get_contents($outputFile . '.txt');
            unlink($outputFile . '.txt');
            
            return $text ?: '';
        } catch (\Exception $e) {
            Log::error("OCR Error: " . $e->getMessage());
            return '';
        }
    }

    private static function cleanContent(string $content): string
    {
        // Remove non-UTF8 characters
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Remove special characters that might break the API
        $content = preg_replace('/[^\p{L}\p{N}\s\.\,\-\:\;]/u', '', $content);
        
        return trim($content);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
            'view' => Pages\ViewMaterial::route('/{record}'),
        ];
    }
}