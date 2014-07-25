<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Image\Transform;

/**
 * Image class
 *
 * @category   Pop
 * @package    Pop_Image
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Imagick extends AbstractTransform
{

    /**
     * Rotate the image object
     *
     * @param  int $degrees
     * @return Imagick
     */
    public function rotate($degrees)
    {
        return $this;
    }

    /**
     * Flip the image over the x-axis.
     *
     * @return Imagick
     */
    public function flip()
    {
        return $this;
    }

    /**
     * Flip the image over the y-axis.
     *
     * @return Imagick
     */
    public function flop()
    {
        return $this;
    }

}