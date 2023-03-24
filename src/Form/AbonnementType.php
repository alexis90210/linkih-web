<?php

namespace App\Form;

use App\Entity\Abonnement;
//use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbonnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('EnNom', TextType::class)
            ->add('code', ChoiceType::class, [
                'choices'  => array(
                    "Abonnement mensuel" => '1',
                    "Abonnement Annuel" => '2',
                ),
                'multiple' => false,
                'expanded' => true, 'mapped' => true, 'label' => false
            ])
            ->add('montant', NumberType::class)
            ->add('devise', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    '$' => '$',
                    '€' => '€',
                ],

            ])
            ->add('description', TextType::class)
            ->add('duree_abonnement', NumberType::class)

            ->add('type_abonnement', ChoiceType::class, [
                'choices' => [
                    'Societe' => 1,
                    'Auto Entrepreneur' => 2
                ],
            ])

            ->add('submit', SubmitType::class, [
                'label' => "Ajouter"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Abonnement::class,
        ]);
    }
}
