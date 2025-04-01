<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Task;

use SuareSu\PyrusClient\Entity\Attachment\Attachment;
use SuareSu\PyrusClient\Entity\Person\Person;

/**
 * DTO for task for form entity from Pyrus.
 *
 * @psalm-api
 */
class FormTask
{
    /**
     * @param Approval[]      $approvals
     * @param Approval[]      $subscribers
     * @param int[]           $linkedTaskIds
     * @param Attachment[]    $attachments
     * @param FormTaskField[] $fields
     * @param Comment[]       $comments
     */
    public function __construct(
        public readonly int $id,
        public readonly int $formId,
        public readonly \DateTimeInterface $createDate,
        public readonly \DateTimeInterface $lastModifiedDate,
        public readonly Person $author,
        public readonly \DateTimeInterface $closeDate,
        public readonly ?Person $responsible,
        public readonly array $approvals = [],
        public readonly array $subscribers = [],
        public readonly ?int $parentTaskId = null,
        public readonly array $linkedTaskIds = [],
        public readonly array $attachments = [],
        public readonly array $fields = [],
        public readonly array $comments = [],
        public readonly bool $isClosed = false,
        public readonly int $currentStep = 1,
    ) {
    }
}
