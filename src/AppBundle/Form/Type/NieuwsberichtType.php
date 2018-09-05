<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NieuwsberichtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titel', 'text', array(
                'attr' => array('style' => 'width:580px'),
            ))
            ->add('bericht', 'textarea', array(
                'attr' => array('cols' => '80', 'rows' => '40'),
            ))
            ->add('Verstuur', 'submit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Nieuwsbericht',
        ));
    }

    public function getName()
    {
        return 'nieuwsbericht';
    }
}