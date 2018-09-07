<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NieuwsberichtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titel', TextType::class, array(
                'attr' => array('style' => 'width:580px'),
            ))
            ->add('bericht', TextareaType::class, array(
                'attr' => array('cols' => '80', 'rows' => '40'),
            ))
            ->add('Verstuur', SubmitType::class)
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
