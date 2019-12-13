<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;


class ApplicationType extends AbstractType
{

    /**
     * Permet d'avoir la configuration de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */
    protected function getConfiguration($label, $placeholder, $options = []){
        // array_merge est une fonction php
        // avec le recursive en plus on empèche dans le cas ou on se sert d'un attr dans le tableau qu'il recouvre le attr contenant le placeholder
        return array_merge_recursive([
                'label'=> $label,
                'attr'=> [
                    'placeholder'=> $placeholder,
                ]
        ], $options);
    }

}

?>