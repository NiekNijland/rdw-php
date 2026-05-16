<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Relations;

enum Cardinality: string
{
    case OneToMany = 'one-to-many';
    case ManyToOne = 'many-to-one';
}
