<?php

namespace App\Tests\Service;

use App\Service\GotenbergService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GotenbergServiceTest extends TestCase
{
    private function createMockService(string $expectedContent = 'fake_pdf_content'): GotenbergService
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')->willReturn($expectedContent);

        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')->willReturn($mockResponse);

        return new GotenbergService($mockClient, 'http://fake-gotenberg:3000');
    }

    public function testGeneratePdfFromUrl(): void
    {
        $service = $this->createMockService();
        $result = $service->generatePdfFromUrl('https://example.com');
        $this->assertEquals('fake_pdf_content', $result);
    }

    public function testGeneratePdfFromHtml(): void
    {
        $service = $this->createMockService();
        $result = $service->generatePdfFromHtml('<h1>Hello</h1>');
        $this->assertEquals('fake_pdf_content', $result);
    }

    public function testGenerateScreenshotFromUrl(): void
    {
        $service = $this->createMockService('fake_screenshot_content');
        $result = $service->generateScreenshotFromUrl('https://example.com');
        $this->assertEquals('fake_screenshot_content', $result);
    }
}
