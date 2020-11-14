<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Sync\Mapping\Field;

class Field
{
    private $name;
    private $label;
    private $dataType;
    private $isRequired;
    private $isWritable;

    public function __construct(array $field = [])
    {
        $this->name       = $field['name'];
        $this->label      = $field['displayName'];
        $this->dataType   = $field['type'];
        $this->isRequired = false;
        $this->isWritable = false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDataType(): string
    {
        return $this->dataType;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isWritable(): bool
    {
        return $this->isWritable;
    }
}
