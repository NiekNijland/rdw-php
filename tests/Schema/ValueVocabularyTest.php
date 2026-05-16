<?php

declare(strict_types=1);

namespace NiekNijland\RDW\Tests\Schema;

use InvalidArgumentException;
use NiekNijland\RDW\Schema\ValueVocabulary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValueVocabulary::class)]
final class ValueVocabularyTest extends TestCase
{
    public function test_closed_marks_exhaustive_true(): void
    {
        $vocab = ValueVocabulary::closed('a', 'b', 'c');

        self::assertTrue($vocab->exhaustive);
        self::assertSame(['a', 'b', 'c'], $vocab->values);
    }

    public function test_examples_marks_exhaustive_false(): void
    {
        $vocab = ValueVocabulary::examples('a', 'b');

        self::assertFalse($vocab->exhaustive);
        self::assertSame(['a', 'b'], $vocab->values);
    }

    public function test_rejects_empty_vocabulary(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ValueVocabulary([], true);
    }
}
