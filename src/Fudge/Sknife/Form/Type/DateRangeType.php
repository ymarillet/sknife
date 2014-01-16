<?php
namespace Fudge\Sknife\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * DateRangeType
 * @author Yohann Marillet
 * @since 21/11/13
 */
class DateRangeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateStart', 'date', [
                                            'label' => $options['start_label'],
                                            'required' => $options['required'],
                                            'widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy',
                                            'input' => 'datetime',
                                            'empty_value' => '',
                                           ])
                ->add('dateEnd', 'date', [
                                            'label' => $options['end_label'],
                                            'required' => $options['required'],
                                            'widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy',
                                            'input' => 'datetime',
                                            'empty_value' => '',
                                         ])
                ->add('errors', 'text', [
                                            'required' => false,
                                            'mapped' => false,
                                            'attr' => [
                                                'style' => 'display: none;'
                                            ],
                                         ])
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => '\\Fudge\\Sknife\\Model\\DateRange',
                                   'start_label' => false,
                                   'end_label' => false,
                                   'required' => true,
                               ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sknife_date_range';
    }
}
