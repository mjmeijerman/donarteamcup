<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SponsorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('naam', null, array(
                'required' => true
            ))
            ->add('file', null, array(
                'required' => true
            ))
            ->add('file2', null, array(
                'required' => true
            ))
            ->add('website')
            ->add('omschrijving')
            ->add('opslaan', 'submit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Sponsor',
        ));
    }

    public function getName()
    {
        return 'sponsor';
    }
}