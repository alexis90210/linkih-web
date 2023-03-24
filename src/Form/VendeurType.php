<?php

namespace App\Form;

use App\Entity\Vendeur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendeurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        //dd($options);
        $builder
            ->add('nom', TextType::class)
            ->add('mail', EmailType::class)
            ->add('mobile', TextType::class)
            ->add('adresse', TextType::class)
            ->add('CorpsMetier', TextType::class)
            // ->add('Categorie')
            ->add('NomResponsable', TextType::class)
            ->add('Sciem', TextType::class)
            ->add('PosteResponsable', TextType::class)
            ->add('Password', PasswordType::class, [
                'mapped' => false, 'label' => false
            ])

            ->add('logo', FileType::class, ['mapped' => false, 'required' => false])
            ->add('abonnement', ChoiceType::class, [
                'mapped' => false,
                'choices' => $options['abonnement'],
            ])

            ->add('type_etablissement', ChoiceType::class, [
                'choices' => [
                    'Societe' => 1,
                    'Auto Entrepreneur' => 2
                ],
            ])
            // ->add('CompteActif', ChoiceType::class, [
            //     'choices'  => array(
            //         "Oui" => '1',
            //         "Non" => '0',
            //     ),
            //     'multiple' => false,
            //     'expanded' => true, 'mapped' => true, 'label' => false
            // ])
            // ->add('CompteConfirme', ChoiceType::class, [
            //     'choices'  => array(
            //         "Oui" => '1',
            //         "Non" => '0',
            //     ),
            //     'multiple' => false,
            //     'expanded' => true, 'mapped' => true, 'label' => false
            // ])
            // ->add('CodeConfirmation')
            // ->add('Logo',FileType::class)
            // ->add('Utilisateur')
            // ->add('abonnementVendeur')
            // ->add('geolocalisation')
            ->add(
                'submit',
                SubmitType::class,
                ['label' => 'Enregistrer',]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vendeur::class,
        ]);

        $resolver->setRequired('abonnement');
    }
}
