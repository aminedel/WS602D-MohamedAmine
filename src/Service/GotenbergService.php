<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class GotenbergService
{
    private string $gotenbergUrl;

    public function __construct(
        private HttpClientInterface $client,
        string $gotenbergUrl = 'http://gotenberg:3000'
    ) {
        $this->gotenbergUrl = $gotenbergUrl;
    }

    public function generatePdfFromUrl(string $url): string
    {
        $formData = new FormDataPart([
            'url' => $url
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromHtml(string $htmlContent): string
    {
        $formData = new FormDataPart([
            'files' => [
                new DataPart($htmlContent, 'index.html', 'text/html')
            ]
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromMarkdown(string $markdownContent): string
    {
        $htmlWrapper = '<!DOCTYPE html><html><body>' . $markdownContent . '</body></html>';

        $formData = new FormDataPart([
            'files' => [
                new DataPart($htmlWrapper, 'index.html', 'text/html'),
                new DataPart($markdownContent, 'file.md', 'text/markdown')
            ]
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/markdown', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generatePdfFromOffice(string $filePath, string $fileName): string
    {
        $formData = new FormDataPart([
            'files' => [
                DataPart::fromPath($filePath, $fileName)
            ]
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/libreoffice/convert', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    /**
     * @param array<string> $pdfPaths
     */
    public function mergePdfs(array $pdfPaths): string
    {
        $files = [];
        foreach ($pdfPaths as $index => $path) {
            $files[] = DataPart::fromPath($path, 'file_' . $index . '.pdf', 'application/pdf');
        }

        $formData = new FormDataPart([
            'files' => $files
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/pdfengines/merge', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    public function generateScreenshotFromUrl(string $url): string
    {
        $formData = new FormDataPart([
            'url' => $url,
            'format' => 'png'
        ]);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/screenshot/url', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $response->getContent();
    }

    /**
     * Save generated content to disk and return the stored filename.
     */
    public function savePdf(string $content, string $directory, string $filename): string
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filepath = rtrim($directory, '/') . '/' . $filename;
        file_put_contents($filepath, $content);

        return $filename;
    }

    /**
     * Check if Gotenberg service is available.
     */
    public function isAvailable(): bool
    {
        try {
            $response = $this->client->request('GET', $this->gotenbergUrl . '/health');
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
