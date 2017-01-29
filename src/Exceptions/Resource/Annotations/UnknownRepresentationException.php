<?php
namespace Mill\Exceptions\Resource\Annotations;

class UnknownRepresentationException extends \Exception
{
    use AnnotationExceptionTrait;

    /**
     * @param string $representation
     * @param string $class
     * @param string $method
     * @return UnknownRepresentationException
     */
    public static function create($representation, $class, $method)
    {
        $message = sprintf(
            'The `@api-return %s` in %s::%s has an unknown representation. Is it present in your config file?',
            $representation,
            $class,
            $method
        );

        $exception = new self($message);
        $exception->docblock = $representation;
        $exception->class = $class;
        $exception->method = $method;

        return $exception;
    }
}
