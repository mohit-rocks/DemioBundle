<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Form\Type;

use MauticPlugin\DemioBundle\Connection\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ConfigAuthType extends AbstractType
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'key',
            TextType::class,
            [
                'label'    => 'demio.form.key',
                'required' => true,
                'attr'     => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'demio.form.key.required']),
                ],
            ]
        );

        $builder->add(
            'secret',
            TextType::class,
            [
                'label'      => 'demio.form.secret',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'demio.form.secret.required']),
                ],
            ]
        );

        $builder->add(
            'host',
            TextType::class,
            [
                'label'      => 'demio.form.host',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'demio.form.host.required']),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->setDefaults(
            [
                'integration' => null,
                'constraints' => [
                    new Callback([$this, 'validate']),
                ],
            ]
        );
    }

    public function validate(array $data, ExecutionContextInterface $context): void
    {
        if (!empty($data['key']) && !empty($data['secret'])) {
            if (!$this->client->validateCredentials($data['host'], $data['key'], $data['secret'])) {
                $context->buildViolation('The API user credentials supplied are invalid.')
                    ->addViolation();
            }
        }
    }
}
