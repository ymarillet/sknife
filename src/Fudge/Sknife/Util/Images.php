<?php
namespace Fudge\Sknife\Util;

use Fudge\Sknife\Exception\BusinessException;

/**
 * Images
 *
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 */
class Images
{
    const WATERMARK_RESIZE_NONE = 0x0;
    const WATERMARK_RESIZE_WIDTH = 0x1;
    const WATERMARK_RESIZE_HEIGHT = 0x2;
    const WATERMARK_RESIZE_BOTH = 0x3;

    const WATERMARK_ALPHA_AUTO = -1;

    const RESIZE_QUALITY_AUTO = -1;

    /**
     * Resize an image
     *
     * @param string $source complete path to the source file
     * @param string $destination folder or complete path to the destination file (will overwrite if exists)
     * @param int|null $x leave null to auto calculate ($y must be specified)
     * @param int|null $y leave null to auto calculate ($x must be specified)
     * @param int $quality quality of the image (depends on the image type - leave empty to use default compression)
     *
     * @throws BusinessException
     * @return bool
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public static function resize($source, $destination, $x = null, $y = null, $quality = self::RESIZE_QUALITY_AUTO)
    {
        //checking the source does exist
        Files::requireFileExists($source);

        //getting the source filename
        $filename = basename($source);

        //append the source filename if the destination is a folder
        if (is_dir($destination)) {
            $destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        }

        //check the destination is writable
        Files::requireWritePermissions($destination);

        $image_functions = self::_getImageFunctions($source);

        //checking validity of input parameters
        $x = abs((int)$x);
        $y = abs((int)$y);
        if (empty($x) && empty($y)) {
            throw new BusinessException('You didn\'t specify a width or an height to resize to');
        }

        //resource creation
        $image_source = $image_functions['create']($source);
        imagealphablending($image_source, true);
        $x_source = imagesx($image_source);
        $y_source = imagesy($image_source);

        //$is_landscape = ($x_source>=$y_source);

        //if we did specify only 1 dimension, the other one is calculated automatically
        if (empty($y)) {
            $ratio = $x_source / $x;
            $y = round($y_source / $ratio);
        } elseif (empty($x)) {
            $ratio = $y_source / $y;
            $x = round($x_source / $ratio);
        }

        //creating new resource for destination
        $image_destination = imagecreatetruecolor($x, $y);
        imagealphablending($image_destination, false);
        imagesavealpha($image_destination, true);
        $x_destination = imagesx($image_destination);
        $y_destination = imagesy($image_destination);

        //saving result

        imagecopyresampled(
                $image_destination,
                $image_source,
                0,
                0,
                0,
                0,
                $x_destination,
                $y_destination,
                $x_source,
                $y_source
        );

        if ($quality == self::RESIZE_QUALITY_AUTO) {
            if ($image_functions['save'] == 'imagejpeg') {
                $quality = 90;
            } elseif ($image_functions['save'] == 'imagepng') {
                $quality = 0;
            }
        }

        if ($image_functions['save'] == 'imagegif') {
            $return = $image_functions['save']($image_destination, $destination);
        } else {
            $return = $image_functions['save']($image_destination, $destination, $quality);
        }

        //free mem
        imagedestroy($image_source);
        imagedestroy($image_destination);

        return $return;
    }

    /**
     * @param $file_path
     *
     * @return array
     * @throws BusinessException
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    protected static function _getImageFunctions($file_path)
    {
        //getting the source file information
        $info = getimagesize($file_path);

        //getting the right imagecreate function
        $return = array();
        switch ($info[2]) {
            case IMAGETYPE_GIF:
                $return['create'] = 'imagecreatefromgif';
                $return['save'] = 'imagegif';
                break;
            case IMAGETYPE_PNG:
                $return['create'] = 'imagecreatefrompng';
                $return['save'] = 'imagepng';
                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $return['create'] = 'imagecreatefromjpeg';
                $return['save'] = 'imagejpeg';
                break;
            default:
                throw new BusinessException('Unknown image format to resize: "' . $info['mime'] . '"');
                break;
        }

        return $return;
    }

    /**
     * Apply an image watermark to another image
     *
     * @param string $watermark path to the watermark image
     * @param string $source path to the source image
     * @param string $destination path to the destination image
     * @param string|int $position_x position of the waterwark on the X axis. 'left'/'center'/'right'. You can also specify a value in px; >0 numbers will put a padding from the left side; <0 from the right side
     * @param string|int $position_y position of the waterwark on the Y axis. 'top'/'center'/'bottom'. You can also specify a value in px; >0 numbers will put a padding from the top side; <0 from the bottom side
     * @param int|string $alpha in percentage (0 to 100), transparency of the watermark. Leave to -1 (default) to use the default alpha channel of the watermark.
     * @param int $resize_watermark one of the self::WATERMARK_RESIZE_* constants
     *
     * @throws BusinessException
     * @return bool
     * @author Yohann Marillet
     */
    public static function applyWatermark(
            $watermark,
            $source,
            $destination,
            $position_x = 'left',
            $position_y = 'top',
            $alpha = self::WATERMARK_ALPHA_AUTO,
            $resize_watermark = self::WATERMARK_RESIZE_NONE
    ) {
        if (is_numeric($position_x)) {
            $position_x = intval($position_x);
        } elseif (is_string($position_x)) {
            if (!in_array($position_x, array('left', 'center', 'right'))) {
                throw new BusinessException('Unknown $position_x "' . $position_x . '"');
            }
        } else {
            throw new BusinessException('Unknown format type for $position_x (' . strval($position_x) . ')');
        }

        if (is_numeric($position_y)) {
            $position_y = intval($position_y);
        } elseif (is_string($position_y)) {
            if (!in_array($position_y, array('top', 'center', 'bottom'))) {
                throw new BusinessException('Unknown $position_y "' . $position_y . '"');
            }
        } else {
            throw new BusinessException('Unknown format type for $position_y (' . strval($position_y) . ')');
        }

        //checking the sources does exist
        Files::requireWritePermissions($watermark);
        Files::requireWritePermissions($source);

        //getting the source filename
        $filename = basename($source);

        //append the source filename if the destination is a folder
        if (is_dir($destination)) {
            $destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        }

        //check the destination is writable
        Files::requireWritePermissions($destination);

        $source_size = getimagesize($source);
        if ($resize_watermark != self::WATERMARK_RESIZE_NONE) {
            $tmp_watermark = tempnam('/tmp', 'watermarkresized');
            self::resize(
                    $watermark,
                    $tmp_watermark,
                    ($resize_watermark & self::WATERMARK_RESIZE_WIDTH ? $source_size[0] : null),
                    ($resize_watermark & self::WATERMARK_RESIZE_HEIGHT ? $source_size[1] : null)
            );
            $watermark = $tmp_watermark;
        }

        $image_watermark_functions = self::_getImageFunctions($watermark);
        $image_source_functions = self::_getImageFunctions($source);

        $watermark_image = $image_watermark_functions['create']($watermark);
        $x_watermark = imagesx($watermark_image);
        $y_watermark = imagesy($watermark_image);

        $source_image = $image_source_functions['create']($source);
        $x_source = imagesx($source_image);
        $y_source = imagesy($source_image);

        //Comptuting the X axis destination position
        switch ($position_x) {
            case 'left':
                $x_destination = 0;
                break;
            case 'center':
                $x_destination = ($x_source - $x_watermark) / 2;
                break;
            case 'right':
                $x_destination = ($x_source - $x_watermark);
                break;
            default:
                if ($position_x < 0) {
                    $x_destination = ($x_source - $x_watermark) + $position_x;
                } else {
                    $x_destination = $position_x;
                }
                break;
        }

        //Comptuting the Y axis destination position
        switch ($position_y) {
            case 'top':
                $y_destination = 0;
                break;
            case 'center':
                $y_destination = ($y_source - $y_watermark) / 2;
                break;
            case 'bottom':
                $y_destination = ($y_source - $y_watermark);
                break;
            default:
                if ($position_y < 0) {
                    $y_destination = ($y_source - $y_watermark) + $position_y;
                } else {
                    $y_destination = $position_y;
                }
                break;
        }

        /**
         * @see http://www.redmonkey.org/php-bug-23815/
         */
        if ($alpha < 0 || $alpha > 100) {
            imagecopy(
                    $source_image,
                    $watermark_image,
                    $x_destination,
                    $y_destination,
                    0,
                    0,
                    $x_watermark,
                    $y_watermark
            );
        } else {
            imagecopymerge(
                    $source_image,
                    $watermark_image,
                    $x_destination,
                    $y_destination,
                    0,
                    0,
                    $x_watermark,
                    $y_watermark,
                    $alpha
            );
        }

        $return = $image_source_functions['save']($source_image, $destination);

        imagedestroy($watermark_image);
        imagedestroy($source_image);

        return $return;
    }
}
