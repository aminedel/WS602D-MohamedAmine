<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class PdfGenerationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de génération',
                'choices' => [
                    'URL vers PDF' => 'url',
                    'Fichier vers PDF' => 'file',
                    'Éditeur HTML vers PDF' => 'wysiwyg',
                ],
                'expanded' => true,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL à convertir',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com',
                ],
                'constraints' => [
                    new Url([
                        'message' => 'Veuillez entrer une URL valide',
                    ]),
                ],
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier à convertir',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF, Word, Excel, PowerPoint, TXT)',
                    ]),
                ],
            ])
            ->add('html_content', TextareaType::class, [
                'label' => 'Contenu HTML',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                    'placeholder' => '<h1>Mon document</h1><p>Contenu...</p>',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
