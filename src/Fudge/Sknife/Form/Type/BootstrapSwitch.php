<?php
namespace Fudge\Sknife\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * BootstrapSwitch
 * @author Yohann Marillet <yohann.marillet@gmail.com>
 * @since 21/10/13
 */
class BootstrapSwitch extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('sizeClass', $options['sizeClass'])
                ->setAttribute('on_label', $options['on_label'])
                ->setAttribute('off_label', $options['off_label'])
                ->setAttribute('on_color', $options['on_color'])
                ->setAttribute('off_color', $options['off_color'])
                ->setAttribute('min_width', $options['min_width'])
                ->setAttribute('add_classes', $options['add_classes'])
                ->setAttribute('add_styles', $options['add_styles'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['sizeClass'] = $form->getConfig()->getAttribute('sizeClass');
        $view->vars['on_label'] = $form->getConfig()->getAttribute('on_label');
        $view->vars['off_label'] = $form->getConfig()->getAttribute('off_label');
        $view->vars['on_color'] = $form->getConfig()->getAttribute('on_color');
        $view->vars['off_color'] = $form->getConfig()->getAttribute('off_color');
        $view->vars['min_width'] = $form->getConfig()->getAttribute('min_width');
        $view->vars['add_classes'] = $form->getConfig()->getAttribute('add_classes');
        $view->vars['add_styles'] = $form->getConfig()->getAttribute('add_styles');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
                                'sizeClass' => 'switch-small',
                                'on_label' => 'Yes',
                                'off_label' => 'No',
                                'on_color' => 'success',
                                'off_color' => 'default',
                                'min_width' => '100px',
                                'add_classes' => '',
                                'add_styles' => '',
                               ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sknife_bootstrap_switch';
    }
}
