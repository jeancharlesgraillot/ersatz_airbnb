<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationType extends ApplicationType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfiguration("Prénom", "Votre prénom..."))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Votre nom..."))
            ->add('email', EmailType::class, $this->getConfiguration("Email", "Votre adresse email..."))
            ->add('picture', UrlType::class, $this->getConfiguration("Photo de profil", "Votre photo de profil..."))
            ->add('hash', PasswordType::class, $this->getConfiguration("Mot de passe", "Votre mot de passe..."))
            //On ajoute cette entrée au formulaire pour créer un champ de confirmation de mot de passe; du coup il faut rajouter une variable dans l'entité
            ->add('passwordConfirm', PasswordType::class, $this->getConfiguration("Confirmation du mot de passe", "Veuillez confirmer votre mot de passe..."))
            ->add('introduction', TextType::class, $this->getConfiguration("Introduction", "Présentez-vous..."))
            ->add('description', TextareaType::class, $this->getConfiguration("Description", "Présentez-vous en détails..."))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
