<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Sync\DataExchange;

use Mautic\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Mautic\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;

class ValueNormalizer implements ValueNormalizerInterface
{
    // Example of a type that could require values to be transformed to supported format by each side of the sync
    const BOOLEAN_TYPE = 'bool';

    public function normalizeForIntegration(NormalizedValueDAO $value)
    {
        switch ($value->getType()) {
            case NormalizedValueDAO::BOOLEAN_TYPE:
                return (bool) $value->getNormalizedValue();
            default:
                return $value->getNormalizedValue();
        }
    }

    public function normalizeForMautic($value, $type): NormalizedValueDAO
    {
        switch ($type) {
            case self::BOOLEAN_TYPE:
                return new NormalizedValueDAO(NormalizedValueDAO::BOOLEAN_TYPE, $value, (int) $value);
            default:
                return new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, $value, (string) $value);
        }
    }
}
