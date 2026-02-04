<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GotenbergService
{
    private string $gotenbergUrl;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, string $gotenbergUrl)
    {
        $this->httpClient = $httpClient;
        $this->gotenbergUrl = $gotenbergUrl;
    }

    /**
     * Generate PDF from URL
     *
     * @param string $url
     * @return string PDF content
     * @throws \Exception
     */
    public function generatePdfFromUrl(string $url): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => [
                    'url' => $url,
                    'marginTop' => '0.5',
                    'marginBottom' => '0.5',
                    'marginLeft' => '0.5',
                    'marginRight' => '0.5',
                    'paperWidth' => '8.27',
                    'paperHeight' => '11.7',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Gotenberg service returned status: ' . $response->getStatusCode());
            }

            return $response->getContent();
        } catch (\Exception $e) {
            throw new \Exception('Error generating PDF from URL: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF from HTML content
     *
     * @param string $htmlContent
     * @return string PDF content
     * @throws \Exception
     */
    public function generatePdfFromHtml(string $htmlContent): string
    {
        try {
            // Wrap HTML in complete document if needed
            if (stripos($htmlContent, '<html') === false) {
                $htmlContent = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>PDF Document</title></head><body>' . $htmlContent . '</body></html>';
            }

            // Create a temporary HTML file named index.html (required by Gotenberg)
            $tempDir = sys_get_temp_dir() . '/gotenberg_' . uniqid();
            mkdir($tempDir);
            $tempFile = $tempDir . '/index.html';
            file_put_contents($tempFile, $htmlContent);

            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
                'body' => [
                    'files' => [
                        fopen($tempFile, 'r')
                    ],
                    'marginTop' => '0.5',
                    'marginBottom' => '0.5',
                    'marginLeft' => '0.5',
                    'marginRight' => '0.5',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                // Clean up before throwing exception
                @unlink($tempFile);
                @rmdir($tempDir);
                throw new \Exception('Gotenberg service returned status: ' . $response->getStatusCode());
            }

            $content = $response->getContent();

            // Clean up temp files
            @unlink($tempFile);
            @rmdir($tempDir);

            return $content;
        } catch (\Exception $e) {
            throw new \Exception('Error generating PDF from HTML: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF from uploaded file (Office documents)
     *
     * @param UploadedFile $file
     * @return string PDF content
     * @throws \Exception
     */
    public function generatePdfFromFile(UploadedFile $file): string
    {
        try {
            // Get file content and original filename
            $fileContent = file_get_contents($file->getPathname());
            $filename = $file->getClientOriginalName();

            // Create a temporary file with the original name
            $tempDir = sys_get_temp_dir() . '/gotenberg_file_' . uniqid();
            mkdir($tempDir);
            $tempFile = $tempDir . '/' . $filename;
            file_put_contents($tempFile, $fileContent);

            $response = $this->httpClient->request('POST', $this->gotenbergUrl . '/forms/libreoffice/convert', [
                'body' => [
                    'files' => fopen($tempFile, 'r'),
                ],
            ]);

            // Clean up
            @unlink($tempFile);
            @rmdir($tempDir);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Gotenberg service returned status: ' . $response->getStatusCode());
            }

            return $response->getContent();
        } catch (\Exception $e) {
            throw new \Exception('Error generating PDF from file: ' . $e->getMessage());
        }
    }

    /**
     * Save PDF content to file
     *
     * @param string $pdfContent
     * @param string $directory
     * @param string|null $filename
     * @return string Filename
     */
    public function savePdf(string $pdfContent, string $directory, ?string $filename = null): string
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if ($filename === null) {
            $filename = 'pdf_' . date('Y_m_d_His') . '_' . uniqid() . '.pdf';
        }

        $filepath = $directory . '/' . $filename;
        file_put_contents($filepath, $pdfContent);

        return $filename;
    }

    /**
     * Check if Gotenberg service is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->gotenbergUrl . '/health');
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
