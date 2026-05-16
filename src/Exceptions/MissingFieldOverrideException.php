<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Exceptions;

final class MissingFieldOverrideException extends RdwException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * @param list<string> $missingFromOverrides
     * @param list<string> $missingFromMetadata
     */
    public static function forDataset(
        string $datasetId,
        array $missingFromOverrides,
        array $missingFromMetadata,
    ): self {
        $lines = ["Schema drift for dataset \"{$datasetId}\":"];

        if ($missingFromOverrides !== []) {
            $lines[] = '  - fields present in RDW metadata but not in overrides: '
                . implode(', ', $missingFromOverrides);
        }

        if ($missingFromMetadata !== []) {
            $lines[] = '  - fields present in overrides but not in RDW metadata: '
                . implode(', ', $missingFromMetadata);
        }

        return new self(implode("\n", $lines));
    }
}
