<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Query;

enum SortDirection: string
{
    case Asc = 'ASC';
    case Desc = 'DESC';
}
