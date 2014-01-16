<?php
namespace Fudge\Sknife\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * BootstrapExtension
 * @author Yohann Marillet
 * @since 01/10/13
 */
class BootstrapExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('is_bootstrap',$options['is_bootstrap']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['is_bootstrap'] = $form->getConfig()->getAttribute('is_bootstrap');
    }

    /**
     * @inheritdoc
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['is_bootstrap'=>false]);
    }

    /**
     * @inheritdoc
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
