<?php
namespace Mill\Tests\Parser\Resource\Action;

use Mill\Parser\Resource\Action\Documentation;
use Mill\Tests\ReaderTestingTrait;
use Mill\Tests\TestCase;

class DocumentationTest extends TestCase
{
    use ReaderTestingTrait;

    /**
     * @dataProvider providerParseMethodDocumentation
     * @param string $method
     * @param array $expected
     * @return void
     */
    public function testParseMethodDocumentation($method, array $expected)
    {
        $class_stub = '\Mill\Examples\Showtimes\Controllers\Movie';
        $parser = (new Documentation($class_stub, $method))->parse();

        $this->assertSame($class_stub, $parser->getClass());
        $this->assertSame($method, $parser->getMethod());

        $this->assertSame($expected['label'], $parser->getLabel());
        $this->assertSame($expected['content_type'], $parser->getContentType());
        $this->assertEmpty($parser->getCapabilities());

        /** @var \Mill\Parser\Annotations\MinVersionAnnotation $min_version */
        $min_version = $parser->getMinimumVersion();
        if ($expected['minimum_version']) {
            $this->assertInstanceOf('\Mill\Parser\Annotations\MinVersionAnnotation', $min_version);
            $this->assertSame($expected['minimum_version'], $min_version->getMinimumVersion());
        } else {
            $this->assertNull($min_version);
        }

        if (!isset($expected['annotations']['scope'])) {
            $this->assertEmpty($parser->getScopes());
        } else {
            $this->assertCount(count($expected['annotations']['scope']), $parser->getScopes());
        }

        if (!isset($expected['annotations']['param'])) {
            $this->assertEmpty($parser->getParameters());
        } else {
            $this->assertCount(count($expected['annotations']['param']), $parser->getParameters());
        }

        $this->assertSame($expected['responses.length'], count($parser->getResponses()));

        $docs = $parser->toArray();
        $this->assertSame($expected['label'], $docs['label']);
        $this->assertSame($docs['description'], $parser->getDescription());
        $this->assertSame($expected['description'], $docs['description']);
        $this->assertSame($method, $docs['method']);
        $this->assertSame($expected['content_type'], $docs['content_type']);

        if (empty($docs['annotations'])) {
            $this->fail('No parsed annotations for ' . $class_stub);
        }

        foreach ($docs['annotations'] as $name => $data) {
            if (!isset($expected['annotations'][$name])) {
                $this->fail('A parsed `' . $name . '` annotation was not present in the expected data.');
            }

            foreach ($data as $k => $annotation) {
                $annotation_key = $k;
                if ($name === 'param') {
                    // Param annotations are keyed off of the field name.
                    $annotation_key = $annotation['field'];
                }

                $this->assertSame(
                    $expected['annotations'][$name][$annotation_key],
                    $annotation,
                    '`' . $k . '` mismatch'
                );
            }
        }
    }

    /**
     * @dataProvider providerParsingOfSpecificUseCases
     * @param string $docblock
     * @param array $asserts
     * @return void
     */
    public function testParsingOfSpecificUseCases($docblock, array $asserts)
    {
        $this->overrideReadersWithFakeDocblockReturn($docblock);

        $parser = (new Documentation(__CLASS__, __METHOD__))->parse();

        $docs = $parser->toArray();
        $annotations = $docs['annotations'];
        foreach ($asserts as $method => $assert) {
            $this->assertCount($assert['total'], $parser->{$method}());
            $this->assertArrayHasKey($assert['annotation.name'], $annotations);
            $this->assertSame($assert['data'], $annotations[$assert['annotation.name']]);
        }
    }

    /**
     * @dataProvider providerMethodsThatWillFailParsing
     * @param string $docblock
     * @param string $exception
     * @param array $asserts
     * @throws \Exception
     */
    public function testMethodsThatWillFailParsing($docblock, $exception, array $asserts)
    {
        $this->expectException($exception);
        $this->overrideReadersWithFakeDocblockReturn($docblock);

        try {
            (new Documentation(__CLASS__, __METHOD__))->parse()->toArray();
        } catch (\Exception $e) {
            if ('\\' . get_class($e) !== $exception) {
                $this->fail('Unrecognized exception (' . get_class($e) . ') thrown.');
            }

            $this->assertExceptionAsserts($e, __CLASS__, __METHOD__, $asserts);
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function providerParseMethodDocumentation()
    {
        return [
            'GET' => [
                'method' => 'GET',
                'expected' => [
                    'label' => 'Get a single movie.',
                    'description' => 'Return information on a specific movie.',
                    'content_type' => 'application/json',
                    'minimum_version' => false,
                    'responses.length' => 3,
                    'annotations' => [
                        'uri' => [
                            [
                                'deprecated' => false,
                                'group' => 'Movies',
                                'path' => '/movies/+id',
                                'visible' => true
                            ]
                        ],
                        'uriSegment' => [
                            [
                                'description' => 'Movie ID',
                                'field' => 'id',
                                'type' => 'integer',
                                'uri' => '/movies/+id',
                                'values' => false
                            ]
                        ],
                        'return' => [
                            [
                                'description' => false,
                                'http_code' => '200 OK',
                                'representation' => '\Mill\Examples\Showtimes\Representations\Movie',
                                'type' => 'object',
                                'version' => false
                            ],
                            [
                                'description' => 'If no content has been modified since the supplied Last-Modified ' .
                                    'header.',
                                'http_code' => '304 Not Modified',
                                'representation' => false,
                                'type' => 'notmodified',
                                'version' => false
                            ]
                        ],
                        'throws' => [
                            [
                                'capability' => false,
                                'description' => 'If the movie could not be found.',
                                'error_code' => false,
                                'http_code' => '404 Not Found',
                                'representation' => '\Mill\Examples\Showtimes\Representations\Error',
                                'version' => false,
                                'visible' => true
                            ]
                        ]
                    ]
                ]
            ],
            'PATCH' => [
                'method' => 'PATCH',
                'expected' => [
                    'label' => 'Update a movie.',
                    'description' => 'Update a movies data.',
                    'content_type' => 'application/json',
                    'minimum_version' => '1.1',
                    'responses.length' => 4,
                    'annotations' => [
                        'uri' => [
                            [
                                'deprecated' => false,
                                'group' => 'Movies',
                                'path' => '/movies/+id',
                                'visible' => true
                            ]
                        ],
                        'uriSegment' => [
                            [
                                'description' => 'Movie ID',
                                'field' => 'id',
                                'type' => 'integer',
                                'uri' => '/movies/+id',
                                'values' => false
                            ]
                        ],
                        'minVersion' => [
                            [
                                'minimum_version' => '1.1'
                            ]
                        ],
                        'param' => [
                            'cast' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Array of names of the cast.',
                                'field' => 'cast',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'array',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'content_rating' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'MPAA rating',
                                'field' => 'content_rating',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'string',
                                'values' => [
                                    'G' => '',
                                    'NC-17' => '',
                                    'NR' => '',
                                    'PG' => '',
                                    'PG-13' => '',
                                    'R' => '',
                                    'UR' => '',
                                    'X' => ''
                                ],
                                'version' => false,
                                'visible' => true
                            ],
                            'description' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Description, or tagline, for the movie.',
                                'field' => 'description',
                                'required' => true,
                                'sample_data' => false,
                                'type' => 'string',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'director' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Name of the director.',
                                'field' => 'director',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'string',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'is_kid_friendly' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Is this movie kid friendly?',
                                'field' => 'is_kid_friendly',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'boolean',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'name' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Name of the movie.',
                                'field' => 'name',
                                'required' => true,
                                'sample_data' => false,
                                'type' => 'string',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'genres' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Array of movie genres.',
                                'field' => 'genres',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'array',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'imdb' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'IMDB URL',
                                'field' => 'imdb',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'string',
                                'values' => false,
                                'version' => '>=1.1.1',
                                'visible' => true
                            ],
                            'rotten_tomatoes_score' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Rotten Tomatoes score',
                                'field' => 'rotten_tomatoes_score',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'integer',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'runtime' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Movie runtime, in `HHhr MMmin` format.',
                                'field' => 'runtime',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'string',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ],
                            'trailer' => [
                                'capability' => false,
                                'deprecated' => false,
                                'description' => 'Trailer URL',
                                'field' => 'trailer',
                                'required' => false,
                                'sample_data' => false,
                                'type' => 'string',
                                'values' => false,
                                'version' => false,
                                'visible' => true
                            ]
                        ],
                        'return' => [
                            [
                                'description' => false,
                                'http_code' => '200 OK',
                                'representation' => '\Mill\Examples\Showtimes\Representations\Movie',
                                'type' => 'object',
                                'version' => false
                            ]
                        ],
                        'scope' => [
                            [
                                'description' => false,
                                'scope' => 'edit'
                            ]
                        ],
                        'throws' => [
                            [
                                'capability' => false,
                                'description' => 'If there is a problem with the request.',
                                'error_code' => false,
                                'http_code' => '400 Bad Request',
                                'representation' => '\Mill\Examples\Showtimes\Representations\Error',
                                'version' => false,
                                'visible' => true
                            ],
                            [
                                'capability' => false,
                                'description' => 'If the IMDB URL could not be validated.',
                                'error_code' => false,
                                'http_code' => '400 Bad Request',
                                'representation' => '\Mill\Examples\Showtimes\Representations\Error',
                                'version' => false,
                                'visible' => true
                            ],
                            [
                                'capability' => false,
                                'description' => 'If the movie could not be found.',
                                'error_code' => false,
                                'http_code' => '404 Not Found',
                                'representation' => '\Mill\Examples\Showtimes\Representations\Error',
                                'version' => false,
                                'visible' => true
                            ]
                        ]
                    ]
                ]
            ],
            'DELETE' => [
                'method' => 'DELETE',
                'expected' => [
                    'label' => 'Delete a movie.',
                    'description' => 'Delete a movie.',
                    'content_type' => 'application/json',
                    'minimum_version' => false,
                    'responses.length' => 2,
                    'annotations' => [
                        'uri' => [
                            [
                                'deprecated' => false,
                                'group' => 'Movies',
                                'path' => '/movies/+id',
                                'visible' => false
                            ]
                        ],
                        'uriSegment' => [
                            [
                                'description' => 'Movie ID',
                                'field' => 'id',
                                'type' => 'integer',
                                'uri' => '/movies/+id',
                                'values' => false
                            ]
                        ],
                        'return' => [
                            [
                                'description' => false,
                                'http_code' => '204 No Content',
                                'representation' => false,
                                'type' => 'deleted',
                                'version' => false
                            ]
                        ],
                        'scope' => [
                            [
                                'description' => false,
                                'scope' => 'delete'
                            ]
                        ],
                        'throws' => [
                            [
                                'capability' => false,
                                'description' => 'If the movie could not be found.',
                                'error_code' => false,
                                'http_code' => '404 Not Found',
                                'representation' => '\Mill\Examples\Showtimes\Representations\Error',
                                'version' => false,
                                'visible' => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerParsingOfSpecificUseCases()
    {
        return [
            'with-multiple-visibilities' => [
                'docblock' => '/**
                  * @api-label Update a piece of content.
                  *
                  * @api-uri:public {Foo\Bar} /foo
                  * @api-uri:private {Foo\Bar} /bar
                  *
                  * @api-contentType application/json
                  * @api-scope public
                  *
                  * @api-return:public {ok}
                  */',
                'asserts' => [
                    'getUris' => [
                        'total' => 2,
                        'annotation.name' => 'uri',
                        'data' => [
                            [
                                'deprecated' => false,
                                'group' => 'Foo\Bar',
                                'path' => '/foo',
                                'visible' => true
                            ],
                            [
                                'deprecated' => false,
                                'group' => 'Foo\Bar',
                                'path' => '/bar',
                                'visible' => false
                            ]
                        ]
                    ]
                ]
            ],
            'with-capabilities' => [
                'docblock' => '/**
                  * @api-label Delete a piece of content.
                  *
                  * @api-uri:private {Foo\Bar} /foo
                  *
                  * @api-contentType application/json
                  * @api-scope delete
                  * @api-capability NONE
                  *
                  * @api-return:private {deleted}
                  */',
                'asserts' => [
                    'getCapabilities' => [
                        'total' => 1,
                        'annotation.name' => 'capability',
                        'data' => [
                            [
                                'capability' => 'NONE'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerMethodsThatWillFailParsing()
    {
        return [
            'no-parsed-annotations' => [
                'docblock' => '',
                'expected.exception' => '\Mill\Exceptions\Resource\NoAnnotationsException',
                'expected.exception.asserts' => []
            ],
            'missing-required-label-annotation' => [
                'docblock' => '/**
                  * Test throwing an exception when a required `@api-label` annotation is missing.
                  *
                  * @api-uri {Something} /some/page
                  */',
                'expected.exception' => '\Mill\Exceptions\Annotations\RequiredAnnotationException',
                'expected.exception.asserts' => [
                    'getAnnotation' => 'label'
                ]
            ],
            'multiple-label-annotations' => [
                'docblock' => '/**
                  * Test throwing an exception when multiple `@api-label` annotations are present.
                  *
                  * @api-label Test method
                  * @api-label Test method
                  */',
                'expected.exception' => '\Mill\Exceptions\Annotations\MultipleAnnotationsException',
                'expected.exception.asserts' => [
                    'getAnnotation' => 'label'
                ]
            ],
            'missing-required-content-type-annotation' => [
                'docblock' => '/**
                  * Test throwing an exception when a required `@api-contentType` annotation is missing.
                  *
                  * @api-label Test Method
                  * @api-uri {Something} /some/page
                  */',
                'expected.exception' => '\Mill\Exceptions\Annotations\RequiredAnnotationException',
                'expected.exception.asserts' => [
                    'getAnnotation' => 'contentType'
                ]
            ],
            'multiple-content-type-annotations' => [
                'docblock' => '/**
                  * Test throwing an exception when multiple `@api-contentType` annotations are present.
                  *
                  * @api-label Test method
                  * @api-uri {Something} /some/page
                  * @api-contentType application/json
                  * @api-contentType text/xml
                  */',
                'expected.exception' => '\Mill\Exceptions\Annotations\MultipleAnnotationsException',
                'expected.exception.asserts' => [
                    'getAnnotation' => 'contentType'
                ]
            ],
            'missing-required-visibility-decorator' => [
                'docblock' => '/**
                  * Test throwing an exception when a required visibility decorator is missing on an annotation.
                  *
                  * @api-label Test method
                  * @api-uri {Root} /
                  * @api-contentType application/json
                  * @api-return:public {collection} \Mill\Examples\Showtimes\Representations\Representation
                  */',
                'expected.exception' => '\Mill\Exceptions\Resource\MissingVisibilityDecoratorException',
                'expected.exception.asserts' => [
                    'getAnnotation' => 'uri'
                ]
            ],
            'unsupported-decorator' => [
                'docblock' => '/**
                  * Test throwing an exception when an unsupported decorator is found.
                  *
                  * @api-label Test method
                  * @api-uri:special {Root} /
                  * @api-contentType application/json
                  * @api-return {collection} \Mill\Examples\Showtimes\Representations\Representation
                  */',
                'expected.exception' => '\Mill\Exceptions\Resource\UnsupportedDecoratorException',
                'expected.exception.asserts' => [
                    'getDecorator' => 'special',
                    'getAnnotation' => 'uri'
                ]
            ],
            'required-uri-annotation-missing' => [
                'docblock' => '/**
                  * Test throwing an exception when a required `@api-uri` annotation is missing.
                  *
                  * @api-label Test method
                  * @api-contentType application/json
                  * @api-param:public {page}
                  */',
                'expected.exception' => '\Mill\Exceptions\Annotations\RequiredAnnotationException',
                'expected.exception.asserts' => [
                    'getAnnotation' => 'uri'
                ]
            ],
            'public-annotations-on-a-private-action' => [
                'docblock' => '/**
                  * Test throwing an exception when there are private annotations on a private action.
                  *
                  * @api-label Test method
                  * @api-uri:private {Search} /search
                  * @api-contentType application/json
                  * @api-scope public
                  * @api-return:private {collection} \Mill\Examples\Showtimes\Representations\Representation
                  * @api-throws:public {403} \Mill\Examples\Showtimes\Representations\CodedError
                  *      (Mill\Examples\Showtimes\Representations\CodedError::DISALLOWED) If the user isn\'t allowed to
                  *      do something.
                  */',
                'expected.exception' => '\Mill\Exceptions\Resource\PublicDecoratorOnPrivateActionException',
                'expected.exception.asserts' => [
                    'getAnnotation' => 'throws'
                ]
            ]
        ];
    }
}
