<?php
namespace Fudge\Sknife\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * HTML to text converter
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 28/11/13
 */
class HtmlToTextTransformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function transform($value)
    {
        $return = $value;
        $transformers = [];
        $transformers[] = new HtmlDecoderTransformer();
        $transformers[] = new HtmlStripperTransformer();
        foreach ($transformers as $transformer) {
            /** @var DataTransformerInterface $transformer */
            $return = $transformer->transform($return);
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
