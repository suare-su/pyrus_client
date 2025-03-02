<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Task;

/**
 * DTO for task for form entity creation from Pyrus.
 *
 * @psalm-api
 */
class FormTaskCreate
{
    /**
     * @param FormTaskCreateField[] $fields
     * @param array<int|string>     $attachments
     * @param array<int|string>     $subscribers
     * @param int[]                 $listIds
     * @param int[]                 $approvals
     */
    public function __construct(
        public readonly int $formId,
        public readonly array $fields = [],
        public readonly array $attachments = [],
        public readonly ?\DateTimeInterface $dueDate = null,
        public readonly ?string $due = null,
        public readonly ?int $duration = null,
        public readonly array $subscribers = [],
        public readonly ?int $parentTaskId = null,
        public readonly array $listIds = [],
        public readonly ?\DateTimeInterface $scheduledDate = null,
        public readonly ?\DateTimeInterface $scheduledDatetimeUtc = null,
        public readonly array $approvals = [],
        public readonly bool $fillDefaults = false,
    ) {
    }
}
