<?php
class Images
{
    private $svg_image = null;

    private $png_image = null;

    /**
     * get PNG-image from language-code
     *
     * @access    public
     * @param     string      current language-code
     *
     * @return    string
     */
    public function __construct($lang_code)
    {
        global $website;

        $this -> svg_image = $website -> config['Misc']['path'] . '/skin/images/flags/4x3/' . $lang_code . '.svg';
        $this -> png_image = $website -> config['Misc']['path'] . '/skin/images/flags/4x3/' . $lang_code . '.png';
    }

    public function __destruct()
    {
    }

    /**
     * get PNG-image from language-code
     *
     * @access    public
     *
     * @return    string
     */
    public function getPngFile()
    {
        if ( !is_file($this -> png_image) ) {
            $this -> _convertSvgToPng();
        }

        return $this -> png_image;
    }

    /**
     * convert SVG-File to one-line-string
     *
     * @access    public
     *
     * @return    string
     */
    public function getSvgData()
    {
        $svg_buffer = file_get_contents($this -> svg_image);
        $svg_raw_data = str_replace(array("\r", "\n"), '', $svg_buffer);

        while ( strpos($svg_raw_data, '> ') !== false ) {
            $svg_raw_data = str_replace('> ', '>', $svg_raw_data);
        }

        return $svg_raw_data;
    }

    /**
     * convert SVG-image to PNG-image
     * @see https://www.phpclasses.org/package/12991-PHP-Convert-an-SVG-image-to-PNG-removing-transparency.html#view_files/files/349142
     *
     * @access    private
     *
     * @return    string
     */
    private function _convertSvgToPng()
    {
        $outputFormat = 'png';

        $svgContent = file_get_contents($this -> svg_image);

        // Create an Imagick object and set the SVG content
        $imagick = new Imagick();
        $imagick -> readImageBlob($svgContent);

        // Optionally, you can adjust the image properties as needed
        $imagickPixel = new ImagickPixel("rgba(255,255,255,1)");

        // generate Image
        $imagick -> setImageFormat($outputFormat);
        $imagick -> setImageBackgroundColor($imagickPixel);
        $imagick -> setResolution(300, 300);

        // Perform the conversion
        $imagick -> setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $imagick -> mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        // Save the converted image to a file
        $imagick -> writeImage($this -> png_image);

        // Clean up resources
        $imagick -> clear();
        $imagick -> destroy();
    }
}