<?php

namespace DocumentConverter\Tests;

use DocumentConverter\DocumentConverter;
use PHPUnit\Framework\TestCase;

class DocumentConverterTest extends TestCase
{

    public function testPdf(){
        $service = new DocumentConverter('/tmp/1.pdf');
        $this->assertArrayHasKey('success_page',$service->fileToImage());
    }
    public function testPpt(){
        $service = new DocumentConverter('/tmp/2.pptx');
        $this->assertArrayHasKey('success_page',$service->fileToImage());
    }


    public function testWord(){
        $service = new DocumentConverter('/tmp/3.docx');
        $this->assertArrayHasKey('success_page',$service->fileToImage());
    }

    public function testLog(){
        $service = new DocumentConverter('/tmp/4.log');
        $this->assertIsString($service->fileToPdf());
    }
    public function testLogImage(){
        $service = new DocumentConverter('/tmp/4.log' ,'/tmp', 'jpg', 'green', 100, 90);
        $this->assertArrayHasKey('success_page',$service->fileToImage());
    }
}
