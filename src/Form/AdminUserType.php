<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminUserType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, $this->getConfiguration("Prénom", "Modifier le prénom !"))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom", "Modifier le nom !"))
            ->add('picture', UrlType::class, $this->getConfiguration("Avatar", "Modifier l'url de l'avatar !"))
            ->add('introduction', TextType::class, $this->getConfiguration("Introduction", "Modifier l'introduction !"))
            ->add('description', TextareaType::class, $this->getConfiguration("Description", "Modifier la description !"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
