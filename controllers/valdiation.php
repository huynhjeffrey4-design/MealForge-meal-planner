<?php
class ValidationResult
{
    private array $errors = [];
    
    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }
    
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
    
    public function mergeWith(ValidationResult $other): self
    {
        $this->errors = array_merge($this->errors, $other->getErrors());
        return $this;
    }
}

