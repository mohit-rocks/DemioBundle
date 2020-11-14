<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Form\Type;

use MauticPlugin\DemioBundle\Integration\DemioIntegration;
use MauticPlugin\DemioBundle\Services\DemioContactStore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemioEventActivities extends AbstractType
{
    /**
     * @var \MauticPlugin\DemioBundle\Services\DemioContactStore
     */
    private $demioContactStore;

    /**
     * {@inheritdoc}
     */
    public function __construct(DemioContactStore $demioContactStore)
    {
        $this->demioContactStore = $demioContactStore;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $activities = [
            'plugin.demio.form.left-early'             => DemioIntegration::WEBCAST_LEFT_EARLY,
            'plugin.demio.form.completed'           => DemioIntegration::WEBCAST_COMPLETED,
            'plugin.demio.form.did-not-attended' => DemioIntegration::WEBCAST_DID_NOT_ATTENDED,
            'plugin.demio.form.attended' => DemioIntegration::WEBCAST_ATTENDED,
            'plugin.demio.form.banned' => DemioIntegration::WEBCAST_BANNEd,
        ];

        $builder->add(
            'webcast_activities',
            ChoiceType::class,
            [
                'label'    => 'plugin.demio.form.event_activities',
                'required' => true,
                'attr'     => [
                    'class' => 'form-control',
                ],
                'choices'  => $activities,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefaults(
            [
                'webcast_activities' => null,
            ]
        );
    }

    private function getWebcastAsChoices()
    {
        $events = $this->demioContactStore->getDummyEventsData();
        $options  = [];

        foreach ($events as $id => $event) {
            $options[$event['title']] = $event['id'].':'.$id;
        }

        return $options;
    }
}
