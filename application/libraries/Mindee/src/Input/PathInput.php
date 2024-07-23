<?php

namespace Mindee\Input;

/**
 * Local path input.
 */
class PathInput extends LocalInputSource
{
    /**
     * @param string $filePath Path to open.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->fileName = basename($filePath);

        $file = finfo_open(FILEINFO_MIME_TYPE);

        $mimeType = finfo_file($file, $this->filePath);
        echo '<pre>';
        var_dump( $file ); 
        var_dump( $mimeType); 
        echo '</pre>';
        die();
        $this->fileMimetype = $mimeType;
        $this->fileObject = new \CURLFile($this->filePath, $mimeType, $this->fileName);
        finfo_close($file);
        parent::__construct();
    }
}
