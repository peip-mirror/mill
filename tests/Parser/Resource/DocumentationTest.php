<?php
namespace Mill\Tests\Parser\Resource;

use Mill\Exceptions\MethodNotImplementedException;
use Mill\Parser\Resource\Documentation;
use Mill\Tests\TestCase;

class DocumentationTest extends TestCase
{
    /**
     * @dataProvider controllersProvider
     */
    public function testDocumentation($controller, $expected)
    {
        $docs = (new Documentation($controller))->parse();

        $this->assertSame($controller, $docs->getController());
        $this->assertCount($expected['methods.size'], $docs->getMethods());

        // Test that it was pulled out of the local cache.
        $this->assertCount($expected['methods.size'], $docs->getMethods());

        $this->assertInstanceOf('\Mill\Parser\Resource\Documentation', $docs->parseMethods());

        // Assert that parseMethods() didn't re-parse or mess up the methods we already had.
        $this->assertCount($expected['methods.size'], $docs->getMethods());

        $class_docs = $docs->toArray();

        $this->assertSame($class_docs['controller'], $controller);
        $this->assertSame($expected['label'], $class_docs['label']);
        $this->assertSame($expected['description.length'], strlen($class_docs['description']));

        foreach ($expected['methods.available'] as $method) {
            $this->assertInternalType('array', $class_docs['methods'][$method]);
            $this->assertInstanceOf('\Mill\Parser\Resource\Action\Documentation', $docs->getMethod($method));
        }

        try {
            $docs->getMethod($expected['method.unavailable']);
            $this->fail();
        } catch (MethodNotImplementedException $e) {
            $this->assertSame($controller, $e->getClass());
            $this->assertSame($expected['method.unavailable'], $e->getMethod());
        }
    }

    /**
     * @dataProvider badControllersProvider
     */
    public function testDocumentationFailsOnBadControllers($controller, $exception, $regex)
    {
        $this->expectException($exception);
        foreach ($regex as $rule) {
            $this->expectExceptionMessageRegExp($rule);
        }

        (new Documentation($controller))->parse();
    }

    /**
     * This is to test that Documentation::getMethod() properly calls getMethods() the first time any method is
     * pulled off the current class.
     *
     */
    public function testDocumentationAndGetSpecificMethod()
    {
        $controller = '\Mill\Examples\Showtimes\Controllers\Movie';
        $docs = (new Documentation($controller))->parse();

        $this->assertSame($controller, $docs->getController());
        $this->assertInstanceOf('\Mill\Parser\Resource\Action\Documentation', $docs->getMethod('GET'));
    }

    /**
     * @return array
     */
    public function controllersProvider()
    {
        return [
            'Movie' => [
                'controller' => '\Mill\Examples\Showtimes\Controllers\Movie',
                'expected' => [
                    'methods.size' => 3,
                    'label' => 'Movies',
                    'description.length' => 0,
                    'methods.available' => [
                        'GET',
                        'PATCH',
                        'DELETE'
                    ],
                    'method.unavailable' => 'POST'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function badControllersProvider()
    {
        return [
            'missing-required-label-annotation' => [
                'controller' => '\Mill\Tests\Fixtures\Controllers\ControllerWithRequiredLabelAnnotationMissing',
                'expected.exception' => '\Mill\Exceptions\RequiredAnnotationException',
                'expected.exception.regex' => [
                    '/api-label/'
                ]
            ],
            'multiple-label-annotations' => [
                'controller' => '\Mill\Tests\Fixtures\Controllers\ControllerWithMultipleLabelAnnotations',
                'expected.exception' => '\Mill\Exceptions\MultipleAnnotationsException',
                'expected.exception.regex' => [
                    '/api-label/'
                ]
            ]
        ];
    }
}
