<?php

namespace DocumentConverter;

use Imagick;

class DocumentConverter
{
    private $filePath, $savePath, $saveExt, $background, $resolution, $quality;

    /**
     * @param string $file
     * @param string $savePath
     * @param string $saveExt       https://www.php.net/manual/en/imagick.setimageformat.php
     * @param string $background    https://www.php.net/manual/en/imagick.setimagebackgroundcolor.php
     * @param int $resolution       https://www.php.net/manual/en/imagick.setresolution.php
     * @param int $quality          https://www.php.net/manual/en/imagick.setcompressionquality.php
     * @throws DocumentConverterException
     */
    public function __construct(string $file, string $savePath = '', string $saveExt = 'png', string $background = 'white', int $resolution = 200, int $quality = 80)
    {
        $this->filePath = $file;
        $this->savePath = $savePath;
        $this->saveExt = strtolower($saveExt);
        $this->background = $background;
        $this->resolution = $resolution;
        $this->quality = $quality;
        $this->verify();
    }

    public function fileToImage(): array
    {
        $ext = $this->getFileExt();
        try {
            switch ($ext) {
                case 'pdf':
                    return $this->pdfToImage($this->filePath);
                case 'pptx':
                case 'ppt':
                case 'docx':
                case 'doc':
                case 'wps':
                case 'dotx':
                case 'dotm':
                case 'dot':
                case 'odt':
                case 'docm':
                case 'ddt':
                case 'xlsx':
                case 'xls':
                case 'log':
                case 'txt':
                    return $this->docToImage();
                default:
                    throw new DocumentConverterException('unsupported file type `' . $ext . '`');
            }
        } catch (\ImagickException $e) {

            throw new DocumentConverterException($e->getMessage());
        } catch (\Exception $e) {
            throw new DocumentConverterException($e->getMessage());
        }
    }

    public function fileToPdf(): string
    {
        $ext = $this->getFileExt();
        if ($ext == 'pdf') {
            return $this->filePath;
        }
        try {
            $this->convertDocToPdf();
            return $this->getUnoconvPdfPath();
        } catch (\ImagickException $e) {

            throw new DocumentConverterException($e->getMessage());
        } catch (\Exception $e) {
            throw new DocumentConverterException($e->getMessage());
        }
    }

    /**
     * @throws \ImagickException
     */
    private function pdfToImage(string $pdfFile): array
    {
        $im = new \Imagick();
        $im->setResolution($this->resolution, $this->resolution);
        $im->setCompressionQuality($this->quality);
        $im->readImage($pdfFile);
        $this->setImageSavePath();

        $res = [
            'total_page' => $im->getNumberImages(),
            'success_total' => 0,
            'fail_total' => 0,
            'success_page' => [],
            'fail_page' => [],
        ];
        foreach ($im as $page => $image) {
            $image->stripImage();
            $image->setImageFormat($this->saveExt);
            $image->setImageBackgroundColor($this->background);
            $image->setImageCompression(Imagick::COMPRESSION_JBIG2);
            $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $filename = $this->savePath . DIRECTORY_SEPARATOR . ($page + 1) . '.'.$this->saveExt;
            try {
                $image->writeImage($filename);
                $res['success_page'][] = $filename;
                $res['success_total'] += 1;
            }catch (\ImagickException $e){
                $res['fail_page'][] = $filename;
                $res['fail_total'] += 1;
            }
        }
        return $res;
    }

    /**
     * @throws \ImagickException
     */
    private function docToImage(): array
    {
        $this->convertDocToPdf();
        return $this->pdfToImage($this->getUnoconvPdfPath());
    }

    /**
     * @throws \ImagickException
     */
    private function convertDocToPdf(){
        exec("ps -ef |grep 'soffice' | grep -v grep", $ps);
        if(!empty($ps)){
            throw new \ImagickException('soffice is running, place wait');
        }
        exec("soffice --headless --invisible --convert-to pdf '{$this->filePath}' --outdir '{$this->getFilePath()}' 2>&1", $output,$res);
        if(!isset($output[0])){
            throw new \ImagickException('soffice convert error, output data is null');
        }
        if (substr($output[0], 0, 5) == 'Error') {
            throw new \ImagickException($output[0]);
        }
        if (!file_exists($this->getUnoconvPdfPath())) {
            throw new \ImagickException('soffice convert error, output file not exists');
        }
    }

    /**
     * @throws DocumentConverterException
     */
    private function verify(){
        if(!extension_loaded('imagick')){
            throw new DocumentConverterException('need imagick,you can run `pecl install imagick`');
        }
        exec("soffice --version", $output);
        if(empty($output)){
            throw new DocumentConverterException('need soffice,you can install `libreoffice` [https://www.libreoffice.org/download/download/]');
        }
        if(!file_exists($this->filePath)){
            throw new DocumentConverterException('file `' . $this->filePath . '` not found');
        }
        if($this->saveExt != 'png' && $this->saveExt != 'jpg' && $this->saveExt != 'jpeg'){
            throw new DocumentConverterException('saveExt must in `[png,jpg,jpeg]`');
        }
        if($this->resolution <= 0 || $this->resolution >1000){
            throw new DocumentConverterException('resolution must in `[1,1000]`');
        }
        if($this->quality <= 0 || $this->quality >100){
            throw new DocumentConverterException('quality must in `[1,100]`');
        }
    }

    private function getFileExt(): string
    {
        $pathInfo = pathinfo($this->filePath);
        return strtolower(trim($pathInfo['extension'], '.'));
    }

    private function getFileName(): string
    {
        $pathInfo = pathinfo($this->filePath);
        return $pathInfo['filename'];
    }

    private function getFilePath(): string
    {
        $pathInfo = pathinfo($this->filePath);
        return $pathInfo['dirname'];
    }

    private function getDefaultSavePath(): string
    {
        $pathInfo = pathinfo($this->filePath);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . DIRECTORY_SEPARATOR . uniqid();
    }
    private function getUnoconvPdfPath(): string
    {
        $pathInfo = pathinfo($this->filePath);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename']  . '.pdf';
    }

    private function setImageSavePath()
    {
        if (empty($this->savePath)) {
            $this->savePath = $this->getDefaultSavePath();
        }
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0766,true);
        }
    }
}