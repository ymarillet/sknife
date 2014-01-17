<?php
namespace Fudge\Sknife\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * String to HTML converter
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 28/11/13
 */
class HtmlEncoderTransformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function transform($value)
    {
        $return = $value;
        if (is_array($return)) {
            foreach ($return as $k=>$val) {
                $return[$k] = $this->transform($val);
            }
        } else {
            $return = htmlspecialchars($value, ENT_QUOTES);
        }

        return $return;
    }

    /**
     * @inheritdoc
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
