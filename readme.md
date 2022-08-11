# Document Converter
### PHP Library for LibreOffice
#### Convert offices files to PDF or JPG or PNG using LibreOffice

### Supported file types
```shell
pdf
pptx
ppt
docx
doc
wps
dotx
dotm
dot
odt
docm
ddt
xlsx
xls
log
txt
```

### System Library Installation
- mac
```
brew install ghostscript
brew install ImageMagick
brew install pkg-config
pecl install imagick
brew install libreoffice
```
- centos
```
yum install ghostscript
yum install ImageMagick
yum install pkg-config
pecl install imagick
yum install libreoffice
```
- ubuntu
```
apt-get install ghostscript
apt-get install ImageMagick
apt-get install pkg-config
pecl install imagick
apt-get install libreoffice
```

### Installation
Run this command within your project directory

```shell
composer require hum/document-converter
```

### Usage
Here are some samples.
- pdf converter to image array
```php
$service = new DocumentConverter('/tmp/1.pdf');
$service->fileToImage()

// image save dir : /usr/tmp/a/dir
// image save ext : jpg
// image background : green
// image background : green
// read source file resolution : 100
// save image quality : 90
$service = new DocumentConverter('/tmp/1.pdf', '/usr/tmp/a/dir', 'jpg', 'green', 100, 90);
$service->fileToImage()
```

- doc converter to image array
```php
$service = new DocumentConverter('/tmp/1.docx');
$service->fileToImage()
```

- doc/ppt converter to image array
```php
$service = new DocumentConverter('/tmp/1.pptx');
$pdfPath = $service->fileToPdf()
```

### Method Returns Result
- fileToImage
```php
[
    'total_page' => 4,
    'success_total' => 3,
    'fail_total' => 1,
    'success_page' => [
        '/tmp/1.jpg',
        '/tmp/2.jpg',
        '/tmp/4.jpg',
    ],
    'success_page' => [
        '/tmp/1.jpg',
        '/tmp/2.jpg',
    ],
    'fail_page' => [
        '/tmp/3.jpg',
    ]
]
```

- fileToPdf
```php
'/tmp/a/b/c/d/1.pdf'
```

- Exception
```php
DocumentConverterException
```
