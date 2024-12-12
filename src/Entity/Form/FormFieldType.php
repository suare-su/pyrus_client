<?php

declare(strict_types=1);

namespace SuareSu\PyrusClient\Entity\Form;

/**
 * List of all fields types for form.
 *
 * @psalm-api
 */
enum FormFieldType: string
{
    case TEXT = 'text';
    case MONEY = 'money';
    case NUMBER = 'number';
    case DATE = 'date';
    case TIME = 'time';
    case CHECKMARK = 'checkmark';
    case DUE_DATE = 'due_date';
    case DUE_DATE_TIME = 'due_date_time';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case FLAG = 'flag';
    case STEP = 'step';
    case STATUS = 'status';
    case CREATION_DATE = 'creation_date';
    case NOTE = 'note';
    case CATALOG = 'catalog';
    case FILE = 'file';
    case PERSON = 'person';
    case AUTHOR = 'author';
    case TABLE = 'table';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TITLE = 'title';
    case FORM_LINK = 'form_link';
    case PROJECT = 'project';

    public function isReadonly(): bool
    {
        return match ($this) {
            self::STEP, self::STATUS, self::CREATION_DATE,
            self::NOTE, self::AUTHOR, self::PROJECT => true,
            default => false,
        };
    }
}
