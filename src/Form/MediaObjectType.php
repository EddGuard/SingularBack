<?php
// src/Form/MediaObjectType.php

namespace App\Form;

use App\Entity\MediaObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

final class MediaObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Configure each fields you want to be submitted here, like a classic form.
            ->add('file', FileType::class, [
                'label' => 'label.file',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => MediaObject::max_size,
                        'mimeTypes' => [
                            "application/pdf",
                            "application/x-pdf",
                            "image/jpeg", "image/jpg", "image/png",
                            "application/octet-stream",
                            "video/mp4", "video/m4v", "video/x-m4v", "video/JPEG",
                            "video/quicktime", "video/x-msvideo",
                            "audio/mp4", "audio/mpeg", "text/plain",
                            "application/msword",
                            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                            "application/vnd.ms-excel",
                            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                            "application/vnd.ms-powerpoint",
                            "application/vnd.openxmlformats-officedocument.presentationml.presentation"
                        ],
                        //'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->add('type', TextType::class, [
            ])
            ->add('entityId', TextType::class, [
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MediaObject::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
