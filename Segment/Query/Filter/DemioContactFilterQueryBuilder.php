<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Segment\Query\Filter;

use Doctrine\DBAL\ParameterType;
use Mautic\LeadBundle\Segment\ContactSegmentFilter;
use Mautic\LeadBundle\Segment\Query\Filter\BaseFilterQueryBuilder;
use Mautic\LeadBundle\Segment\Query\QueryBuilder;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;

class DemioContactFilterQueryBuilder extends BaseFilterQueryBuilder
{
    public static function getServiceId(): string
    {
        return 'demio.integration.query.builder.demio.contact';
    }

    public function applyQuery(QueryBuilder $queryBuilder, ContactSegmentFilter $filter): QueryBuilder
    {
        $filterOperator = $filter->getOperator();
        $filterValue    = $filter->getParameterValue();
        $queryAlias     = $this->generateRandomParameterName();
        $objectType     = DemioIntegration::SUBSCRIBER_ACTIVITY;

        $activity = $filter->contactSegmentFilterCrate->getField();
        switch ($activity) {
            case 'demio-attended':
                $integrationReference = $filterValue.':'.DemioIntegration::WEBCAST_ATTENDED;
                break;

            case 'demio-did-not-attended':
                $integrationReference = $filterValue.':'.DemioIntegration::WEBCAST_DID_NOT_ATTENDED;
                break;

            case 'demio-completed':
                $integrationReference = $filterValue.':'.DemioIntegration::WEBCAST_COMPLETED;
                break;

            case 'demio-left-early':
                $integrationReference = $filterValue.':'.DemioIntegration::WEBCAST_LEFT_EARLY;
                break;

            case 'demio-banned':
                $integrationReference = $filterValue.':'.DemioIntegration::WEBCAST_BANNEd;
                break;

            default:
                $integrationReference = null;
        }

        $filterQueryBuilder = $queryBuilder->createQueryBuilder();
        $queryBuilder->setParameter(
            $queryAlias.'_integration_value_reference',
            $integrationReference,
            (is_array($filter->getParameterValue()) ? \Doctrine\DBAL\Connection::PARAM_STR_ARRAY : ParameterType::STRING)
        );

        $filterQueryBuilder
            ->select('id')
            ->from(MAUTIC_TABLE_PREFIX.'sync_object_mapping', $queryAlias)
            ->where(
                $filterQueryBuilder->expr()->eq(
                    $queryAlias.'.integration',
                    $filterQueryBuilder->expr()->literal(DemioIntegration::NAME)
                )
            )
            ->andWhere(
                $filterQueryBuilder->expr()->eq(
                    $queryAlias.'.integration_object_name',
                    $filterQueryBuilder->expr()->literal($objectType)
                )
            )
            ->andWhere(
                $filterQueryBuilder->expr()->eq(
                    $queryAlias.'.integration_reference_id',
                    ':'.$queryAlias.'_integration_value_reference'
                )
            );
        $filterQueryBuilder
            ->andWhere(
                $filterQueryBuilder->expr()->eq($queryAlias.'.is_deleted', $filterQueryBuilder->expr()->literal(0))
            )
            ->andWhere(
                $queryBuilder->expr()->eq('l.id', 'internal_object_id')
            );

        switch ($filterOperator) {
            case 'neq':
            case 'notIn':
                $queryBuilder->addLogic($queryBuilder->expr()->notExists($filterQueryBuilder->getSQL()), $filter->getGlue());
                $filterQueryBuilder->orWhere($queryBuilder->expr()->isNull($queryAlias.'.integration_object_name'));

                break;
            default:
                $queryBuilder->addLogic($queryBuilder->expr()->exists($filterQueryBuilder->getSQL()), $filter->getGlue());
        }

        return $queryBuilder;
    }
}
