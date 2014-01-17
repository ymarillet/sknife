<?php
namespace Fudge\Sknife\Form\DataTransformer;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * HTML to string converter
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 28/11/13
 */
class HtmlDecoderTransformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function transform($value)
    {
        $return = $value;
        if ($return instanceof PersistentCollection) {
            throw new \Exception('Not implemented');
        } elseif (is_array($return) || $return instanceof \Traversable) {
            foreach ($return as $k=>$val) {
                $return[$k] = $this->transform($val);
            }
        } else {
            $return = html_entity_decode($return, ENT_QUOTES);
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
