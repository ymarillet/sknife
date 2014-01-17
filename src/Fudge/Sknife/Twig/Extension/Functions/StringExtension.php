<?php
namespace Fudge\Sknife\Twig\Extension\Functions;

use Fudge\Sknife\Form\DataTransformer\HtmlToTextTransformer;

/**
 * Various twig filters about strings
 * @author Yohann Marillet <yohann.marillet@gmail.com> <yohann.marillet@gmail.com>
 * @since 13/10/13
 */
class StringExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return [
            'html2text'=> new \Twig_SimpleFilter('html2text', [$this, 'html2text']),
        ];
    }

    /**
     * @param array|string $value
     *
     * @return array|string
     * @author Yohann Marillet <yohann.marillet@gmail.com>
     */
    public function html2text($value)
    {
        $transformer = new HtmlToTextTransformer();
        $return = $transformer->transform($value);

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'sknife_twig_string';
    }
}
