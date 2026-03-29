<?php
 
 namespace App\Exceptions;
 
 use Exception;

 class ProvisioningException extends Exception
 {
     /**
      * Additional metadata or response data for auditing.
      *
      * @var array
      */
     protected array $properties;
 
     /**
      * Constructor.
      *
      * @param string $message The user-friendly error message.
      * @param array $properties Additional context for auditing (e.g. ['response' => $response->body()]).
      * @param int $code
      * @param \Throwable|null $previous
      */
     public function __construct(string $message, array $properties = [], int $code = 0, ?\Throwable $previous = null)
     {
         parent::__construct($message, $code, $previous);
         $this->properties = $properties;
     }
 
     /**
      * Get the additional auditing properties.
      *
      * @return array
      */
     public function getProperties(): array
     {
         return $this->properties;
     }
 }
