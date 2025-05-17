<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class Material extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'ai_summary_enabled',
        'ringkasan'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function generateSummary()
    {
        try {
            $content = $this->extractFileContent();
            
            if (empty($content)) {
                throw new \Exception("Failed to extract content from file");
            }

            // Limit content to 15,000 characters to avoid API limits
            $content = mb_substr($content, 0, 15000);
            
            $summary = app(\App\Services\GeminiService::class)->ringkas($content);
            
            if (empty($summary)) {
                throw new \Exception("AI service returned empty summary");
            }

            $this->update(['ringkasan' => $summary]);
            return true;
            
        } catch (\Exception $e) {
            logger()->error("Summary generation failed: " . $e->getMessage());
            return false;
        }
    }

    protected function extractFileContent(): string
    {
        $fullPath = Storage::disk('public')->path($this->file_path);
        
        if (!file_exists($fullPath)) {
            throw new \Exception("File not found at path: {$fullPath}");
        }

        return match ($this->file_type) {
            'pdf' => $this->extractPdfContent($fullPath),
            'docx' => $this->extractDocxContent($fullPath),
            'pptx' => $this->extractPptxContent($fullPath),
            default => throw new \Exception("Unsupported file type: {$this->file_type}"),
        };
    }

    protected function extractPdfContent(string $path): string
    {
        try {
            // Try with pdftotext first
            if (config('services.pdftotext.path') && file_exists(config('services.pdftotext.path'))) {
                return Pdf::getText($path, config('services.pdftotext.path'));
            }
            
            // Fallback to PDFParser
            return (new PdfParser())->parseFile($path)->getText();
        } catch (\Exception $e) {
            throw new \Exception("PDF extraction failed: " . $e->getMessage());
        }
    }

    protected function extractDocxContent(string $path): string
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
                    }
                }
            }
            
            return $text ?: throw new \Exception("DOCX file appears to be empty");
        } catch (\Exception $e) {
            throw new \Exception("DOCX extraction failed: " . $e->getMessage());
        }
    }

    protected function extractPptxContent(string $path): string
    {
        try {
            $presentation = PresentationIOFactory::load($path);
            $text = '';
            
            foreach ($presentation->getAllSlides() as $slide) {
                foreach ($slide->getShapeCollection() as $shape) {
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                        $text .= $shape->getPlainText() . "\n";
                    }
                }
            }
            
            return $text ?: throw new \Exception("PPTX file appears to be empty");
        } catch (\Exception $e) {
            throw new \Exception("PPTX extraction failed: " . $e->getMessage());
        }
    }
}