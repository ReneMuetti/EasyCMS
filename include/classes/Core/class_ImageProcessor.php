<?php
class ImageProcessor
{
    private $language = null;

    private $tmpImage   = null;
    private $imageType  = null;
    private $thumbImage = null;

    private $sourceFile      = '';
    private $destinationFile = '';

    private $quality   = 85;     // default image quality
    private $thumbnail = 400;    // default thumbnail size
    private $minSize   = 100;    // minimal thumbnail size

    private $gobalError   = FALSE;
    private $errorMessage = '';

    private $imageSizeX = 0;
    private $imageSizeY = 0;
    private $thumbSizeX = 0;
    private $thumbSizeY = 0;

    /**
     * create class instance
     *
     * @access     public
     */
    public function __construct()
    {
        global $website;

        $this -> language = $website -> user_lang;

        $this -> _resetError();
    }

    /**
     * Free up memory space
     *
     * @access     public
     */
    public function __destruct()
    {
        unset($this -> tmpImage);
        unset($this -> thumbImage);
    }

    /**
     * Set quality for the generation of JPG and PNG
     *
     * @access     public
     * @param      integer      Compression quality (0 = worst; 100 = highest)
     */
    public function setQuality($quality)
    {
        $quality = intval($quality);

        if ( ($quality > 0) AND ($quality <= 100) ) {
            $this -> quality = $quality;
        }
    }

    /**
     * Set the width of the thumbnail to be generated
     *
     * @access     public
     * @param      integer      Width in pixels
     */
    public function setThumbnailSizeX($size)
    {
        $size = intval($size);

        if ( $size >= $this -> minSize ) {
            $this -> thumbnail;
        }
    }

    /**
     * Source file for processing
     *
     * @access     public
     * @param      string       Full path to the image
     */
    public function setSourceFile($sourceFileName)
    {
        if ( strlen($sourceFileName) AND is_file($sourceFileName) ) {
            $this -> sourceFile = $sourceFileName;
        }
        else {
            $this -> gobalError   = TRUE;
            $this -> errorMessage = sprintf($this -> language['image']['failed_image_source_found'], $sourceFileName);
        }
    }

    /**
     * Target file for processing
     *
     * @access     public
     * @param      string       Full path to the image
     */
    public function setDestinationFile($destinationFileName)
    {
        if ( strlen($destinationFileName) AND !is_file($destinationFileName) ) {
            $this -> destinationFile = $destinationFileName;
        }
        else {
            $this -> gobalError   = TRUE;
            $this -> errorMessage = sprintf($this -> language['image']['failed_image_destination_found'], $destinationFileName);
        }
    }

    /**
     * Return error status during processing for evaluation
     *
     * @access     public
     * @return     array        Error code and error message
     */
    public function getErrorStatus()
    {
        return array(
                   'error'   => ( $this -> gobalError ? 1 : 0 ),
                   'message' => $this -> errorMessage
               );
    }

    /**
     * Creates a new version of the file from a source file while retaining
     * the size and file format
     *
     * @access     public
     */
    public function recodeOriginalFileType()
    {
        if ( $this -> gobalError === FALSE ) {
            $this -> _resetError();
            $this -> errorMessage = sprintf($this -> language['image']['success_recode_new_file_from_original'], basename($this -> sourceFile));

            $this -> _getImageType();

            switch( $this -> imageType ) {
                case IMAGETYPE_GIF  : $this -> _createFromGif();
                                      $this -> _recodeGifImage();
                                      $this -> _deleteTempImage();
                                      break;

                case IMAGETYPE_JPEG : $this -> _createFromJpg();
                                      $this -> _recodeJpgImage();
                                      $this -> _deleteTempImage();
                                      break;

                case IMAGETYPE_PNG  : $this -> _createFromPng();
                                      $this -> _recodePngImage();
                                      $this -> _deleteTempImage();
                                      break;

                default             : $this -> gobalError   = TRUE;
                                      $this -> errorMessage = sprintf($this -> language['image']['failed_check_image_format'], basename($this -> sourceFile));
                                      break;
            }
        }
    }

    /**
     * Creates a JPG file from a source file while retaining the size
     *
     * @access     public
     */
    public function recodeOriginalToJpg()
    {
        if ( $this -> gobalError === FALSE ) {
            $this -> _resetError();
            $this -> errorMessage = sprintf($this -> language['image']['success_recode_new_file_from_original'], basename($this -> sourceFile));

            $this -> _getImageType();

            switch( $this -> imageType ) {
                case IMAGETYPE_GIF  : $this -> _createFromGif();
                                      $this -> _recodeJpgImage();
                                      $this -> _deleteTempImage();
                                      break;

                case IMAGETYPE_JPEG : $this -> _createFromJpg();
                                      $this -> _recodeJpgImage();
                                      $this -> _deleteTempImage();
                                      break;

                case IMAGETYPE_PNG  : $this -> _createFromPng();
                                      $this -> _recodeJpgImage();
                                      $this -> _deleteTempImage();
                                      break;

                default             : $this -> gobalError   = TRUE;
                                      $this -> errorMessage = sprintf($this -> language['image']['failed_check_image_format'], basename($this -> sourceFile));
                                      break;
            }
        }
    }

    /**
     * Creates a reduced version in JPG format from a source file
     *
     * @access     public
     * @return     ressource    Generated image file
     */
    public function resizeJpgImage()
    {
        if ( $this -> gobalError === FALSE ) {
            $this -> _resetError();
            $this -> _getImageType();

            switch( $this -> imageType ) {
                case IMAGETYPE_GIF  : $this -> _createFromGif();
                                      break;
                case IMAGETYPE_JPEG : $this -> _createFromJpg();
                                      break;
                case IMAGETYPE_PNG  : $this -> _createFromPng();
                                      break;
                default             : $this -> gobalError   = TRUE;
                                      $this -> errorMessage = sprintf($this -> language['image']['failed_check_image_format'], basename($this -> sourceFile));
                                      break;
            }

            if ( ($this -> gobalError === FALSE) AND $this -> tmpImage ) {
                $this -> imageSizeX = imagesx( $this -> tmpImage );
                $this -> imageSizeY = imagesy( $this -> tmpImage );

                $this -> thumbSizeX = $this -> thumbnail;
                $this -> thumbSizeY = intval( floatval($this -> imageSizeY) / floatval($this -> imageSizeX) * floatval($this -> thumbnail) );

                $this -> thumbImage = imagecreatetruecolor($this -> thumbSizeX, $this -> thumbSizeY);

                if ( $this -> thumbImage === FALSE ) {
                    $this -> gobalError   = TRUE;
                    $this -> errorMessage = $this -> language['image']['failed_image_thumb_resorce_loaded'];
                }
                else {
                    if ( ($this -> imageType == IMAGETYPE_GIF) OR ($this -> imageType == IMAGETYPE_PNG) ) {
                        imagealphablending($this -> thumbImage, FALSE);
                        imagesavealpha($this -> thumbImage, TRUE);
                        $transparent = imagecolorallocatealpha($this -> thumbImage, 255, 255, 255, 127);
                        imagefilledrectangle($this -> thumbImage, 0, 0, $this -> thumbSizeX, $this -> thumbSizeY, $transparent);
                    }

                    $error = imagecopyresampled($this -> thumbImage, $this -> tmpImage, 0, 0, 0, 0, $this -> thumbSizeX, $this -> thumbSizeY, $this -> imageSizeX, $this -> imageSizeY);

                    if ( $error === FALSE ) {
                        $this -> gobalError   = TRUE;
                        $this -> errorMessage = sprintf($this -> language['image']['failed_create_small_image_size'], basename($this -> sourceFile));
                    }
                    else {
                        if ( !imagejpeg($this -> thumbImage, $this -> destinationFile, $this -> quality) ) {
                            $this -> gobalError   = TRUE;
                            $this -> errorMessage = sprintf($this -> language['image']['failed_create_thumbnail'], basename($this -> sourceFile));
                        }
                        else {
                            $this -> errorMessage = sprintf($this -> language['image']['success_create_thumbnail'], basename($this -> sourceFile));
                        }
                    }
                }

                imagedestroy($this -> thumbImage);
                $this -> _deleteTempImage();

                return $this -> destinationFile;
            }
        }
    }


    /**
     * Reset error status
     *
     * @access     private
     */
    private function _resetError()
    {
        $this -> gobalError   = FALSE;
        $this -> errorMessage = '';
    }

    /**
     * Determine the file type of the source file
     *
     * @access     private
     */
    private function _getImageType()
    {
        $this -> imageType = exif_imagetype($this -> sourceFile);
    }

    /**
     * Release resource of the temp file
     *
     * @access     private
     */
    private function _deleteTempImage()
    {
        if ( $this -> gobalError === FALSE ) {
            imagedestroy( $this -> tmpImage );
        }
    }

    /**
     * Create temp file in GIF format
     *
     * @access     private
     */
    private function _createFromGif()
    {
        $this -> tmpImage = imagecreatefromgif( $this -> sourceFile );
    }

    /**
     * Create temp file in JPG format
     *
     * @access     private
     */
    private function _createFromJpg()
    {
        $this -> tmpImage = imagecreatefromjpeg( $this -> sourceFile );
    }

    /**
     * Create temp file in PNG format
     *
     * @access     private
     */
    private function _createFromPng()
    {
        $this -> tmpImage = imagecreatefrompng( $this -> sourceFile );
    }

    /**
     * Copy image data into the new GIF image
     *
     * @access     private
     */
    private function _recodeGifImage()
    {
        if ( !$this -> tmpImage ) {
            $this -> gobalError   = TRUE;
            $this -> errorMessage = sprintf($this -> language['image']['failed_create_temporary_image'], 'GIF', basename($this -> sourceFile));
        }
        else {
            if ( !imagegif( $this -> tmpImage, $this -> destinationFile ) ) {
                $this -> gobalError   = TRUE;
                $this -> errorMessage = sprintf($this -> language['image']['failed_create_new_image'], 'GIF', basename($this -> sourceFile));
            }
            else {
                $this -> errorMessage = sprintf($this -> language['image']['success_image_original_saved'], 'GIF', basename($this -> sourceFile));
            }
        }
    }

    /**
     * Copy image data into the new JPG image
     *
     * @access     private
     */
    private function _recodeJpgImage()
    {
        if ( !$this -> tmpImage ) {
            $this -> gobalError   = TRUE;
            $this -> errorMessage = sprintf($this -> language['image']['failed_create_temporary_image'], 'JPG', basename($this -> sourceFile));
        }
        else {
            if ( !imagejpeg( $this -> tmpImage, $this -> destinationFile, $this -> quality ) ) {
                $this -> gobalError   = TRUE;
                $this -> errorMessage = sprintf($this -> language['image']['failed_create_new_image'], 'JPG', basename($this -> sourceFile));
            }
            else {
                $this -> errorMessage = sprintf($this -> language['image']['success_image_original_saved'], 'JPG', basename($this -> sourceFile));
            }
        }
    }

    /**
     * Copy image data into the new PNG image
     *
     * @access     private
     */
    private function _recodePngImage()
    {
        if ( !$this -> tmpImage ) {
            $this -> gobalError   = TRUE;
            $this -> errorMessage = sprintf($this -> language['image']['failed_create_temporary_image'], 'PNG', basename($this -> sourceFile));
        }
        else {
            if ( !imagepng( $this -> tmpImage, $this -> destinationFile, $this -> quality ) ) {
                $this -> gobalError   = TRUE;
                $this -> errorMessage = sprintf($this -> language['image']['failed_create_new_image'], 'PNG', basename($this -> sourceFile));
            }
            else {
                $this -> errorMessage = sprintf($this -> language['image']['success_image_original_saved'], 'PNG', basename($this -> sourceFile));
            }
        }
    }
}