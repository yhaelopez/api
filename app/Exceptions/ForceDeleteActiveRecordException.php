<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when attempting to force delete an active (non-deleted) record.
 * 
 * This exception is used to prevent accidental permanent deletion of active records
 * that should only be soft-deleted first, then force deleted if needed.
 */
class ForceDeleteActiveRecordException extends Exception
{
    /**
     * The model class name that was attempted to be force deleted
     */
    protected ?string $modelClass;

    /**
     * The model ID that was attempted to be force deleted
     */
    protected ?int $modelId;

    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     * @param string|null $modelClass
     * @param int|null $modelId
     * @param Throwable|null $previous
     */
    public function __construct(
        ?string $message = '',
        ?int $code = 422,
        ?string $modelClass = null,
        ?int $modelId = null,
        ?Throwable $previous = null
    ) {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;

        if (empty($message)) {
            $message = $this->getDefaultMessage();
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the default error message.
     */
    protected function getDefaultMessage(): string
    {
        if ($this->modelClass && $this->modelId) {
            return sprintf(
                'Cannot force delete active %s with ID %s. The record must be soft-deleted first.',
                class_basename($this->modelClass),
                $this->modelId
            );
        }

        return 'Cannot force delete an active record. The record must be soft-deleted first.';
    }

    /**
     * Get the model class name.
     * 
     * @return string|null
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * Get the model ID.
     * 
     * @return int|null
     */
    public function getModelId(): ?int
    {
        return $this->modelId;
    }
}
