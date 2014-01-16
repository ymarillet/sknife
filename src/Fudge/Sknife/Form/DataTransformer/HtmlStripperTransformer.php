<?php
namespace Fudge\Sknife\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * HTML stripper converter
 * @author Yohann Marillet
 * @since 28/11/13
 */
class HtmlStripperTransformer implements DataTransformerInterface
{
    /**
     * @inheritdoc
     * @author Yohann Marillet
     */
    public function transform($value)
    {
        //strip_tags is broken here as it'll strip all characters starting a "<" character
//        $NotWhiteSpaceOrEndTagClass = '[^\b>]+?';
//        $baseNameClass = '[a-z]+(?:[-_][a-z]+)*';
//        $composedNameClass = '(?:'.$baseNameClass.'(?::'.$baseNameClass.')?)+';
//
//        $regex = '#'
//                . '</?'
//                . $composedNameClass
//                . '(?:'
//                    . '\b+' . $composedNameClass . '(?:=("|\')[^\1]*\1)?'
//                . ')*'
//                . $NotWhiteSpaceOrEndTagClass
//                . '>'
//                .'#i'
//        ;
//        var_dump($regex);
//        //'#</?'.$NotWhiteSpaceOrEndTagClass.'+?([\b]+[a-z_-]+(=("|\')[^\2]+\2)?)*[^\b>]+? >#i'
//        $return = preg_replace($regex, '', $value);
        $return = strip_tags($value);

        return $return;
    }

    /**
     * @inheritdoc
     * @author Yohann Marillet
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
