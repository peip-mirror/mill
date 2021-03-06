<?php
namespace Mill\Tests\Compiler\Changelog\Formats;

use Mill\Compiler\Changelog\Formats\Json;
use Mill\Tests\TestCase;

class JsonTest extends TestCase
{
    public function testCompiler(): void
    {
        $application = $this->getApplication();

        $compiler = new Json($application);
        $compiled = $compiler->getCompiled();
        $compiled = array_shift($compiled);
        $compiled = json_decode($compiled, true);

        $this->assertSame([
            '1.1.3',
            '1.1.2',
            '1.1.1',
            '1.1'
        ], array_keys($compiled));

        // v1.1.3
        $this->assertSame([
            '_details' => [
                'release_date' => '2017-05-27',
                'description' => 'Changed up the responses for `/movie/{id}`, `/movies/{id}` and `/movies`.'
            ],
            'added' => [
                'resources' => [
                    [
                        'The following <span class="mill-changelog_resource_group" ' .
                            'data-mill-resource-group="Movies">Movies</span> resources have added:',
                        [
                            [
                                '<span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                    'data-mill-method="GET" data-mill-path="/movie/{id}">/movie/{id}</span> now ' .
                                    'returns the following errors on <span class="mill-changelog_method" ' .
                                    'data-mill-resource-group="Movies" data-mill-method="GET" ' .
                                    'data-mill-path="/movie/{id}">GET</span> requests:',
                                [
                                    '<span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movie/{id}" ' .
                                        'data-mill-operation-id="getMovie_alt1" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">404 Not Found</span> with a <span ' .
                                        'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movie/{id}" ' .
                                        'data-mill-operation-id="getMovie_alt1" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">Error</span> representation: For no reason.',
                                    '<span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movie/{id}" ' .
                                        'data-mill-operation-id="getMovie_alt1" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">404 Not Found</span> with a <span ' .
                                        'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movie/{id}" ' .
                                        'data-mill-operation-id="getMovie_alt1" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">Error</span> representation: For some ' .
                                        'other reason.'
                                ]
                            ],
                            [
                                '<span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                    'data-mill-method="GET" data-mill-path="/movies/{id}">/movies/{id}</span> now ' .
                                    'returns the following errors on <span class="mill-changelog_method" ' .
                                    'data-mill-resource-group="Movies" data-mill-method="GET" ' .
                                    'data-mill-path="/movies/{id}">GET</span> requests:',
                                [
                                    '<span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="getMovie" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">404 Not Found</span> with a <span ' .
                                        'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="getMovie" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">Error</span> representation: For no reason.',
                                    '<span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="getMovie" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">404 Not Found</span> with a <span ' .
                                        'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="GET" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="getMovie" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">Error</span> representation: For some ' .
                                        'other reason.'
                                ]
                            ],
                            [
                                '<span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                    'data-mill-method="PATCH" data-mill-path="/movies/{id}">/movies/{id}</span> now ' .
                                    'returns the following errors on <span class="mill-changelog_method" ' .
                                    'data-mill-resource-group="Movies" data-mill-method="PATCH" ' .
                                    'data-mill-path="/movies/{id}">PATCH</span> requests:',
                                [
                                    '<span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="updateMovie" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">404 Not Found</span> with a <span ' .
                                        'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="updateMovie" data-mill-http-code="404 Not Found" ' .
                                        'data-mill-representation="Error">Error</span> representation: If the ' .
                                        'trailer URL could not be validated.',
                                    '<span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="updateMovie" data-mill-http-code="403 Forbidden" ' .
                                        'data-mill-representation="Coded error">403 Forbidden</span> with a <span ' .
                                        'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="updateMovie" data-mill-http-code="403 Forbidden" ' .
                                        'data-mill-representation="Coded error">Coded error</span> representation: ' .
                                        'If something cool happened.',
                                    '<span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="updateMovie" data-mill-http-code="403 Forbidden" ' .
                                        'data-mill-representation="Coded error">403 Forbidden</span> with a <span ' .
                                        'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="updateMovie" data-mill-http-code="403 Forbidden" ' .
                                        'data-mill-representation="Coded error">Coded error</span> representation: ' .
                                        'If the user is not allowed to edit that movie.'
                                ]
                            ],
                            'On <span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="updateMovie" data-mill-http-code="202 Accepted" ' .
                                'data-mill-representation="Movie">/movies/{id}</span>, <span ' .
                                'class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="updateMovie" data-mill-http-code="202 Accepted" ' .
                                'data-mill-representation="Movie">PATCH</span> requests now return a <span ' .
                                'class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="updateMovie" data-mill-http-code="202 Accepted" ' .
                                'data-mill-representation="Movie">202 Accepted</span> with a <span ' .
                                'class="mill-changelog_representation" data-mill-resource-group="Movies" ' .
                                'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="updateMovie" data-mill-http-code="202 Accepted" ' .
                                'data-mill-representation="Movie">Movie</span> representation.',
                            '<span class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                'data-mill-method="POST" data-mill-path="/movies" ' .
                                'data-mill-operation-id="createMovie" data-mill-http-code="201 Created" ' .
                                'data-mill-representation="">POST</span> on <span class="mill-changelog_path" ' .
                                'data-mill-resource-group="Movies" data-mill-method="POST" ' .
                                'data-mill-path="/movies" data-mill-operation-id="createMovie" ' .
                                'data-mill-http-code="201 Created" data-mill-representation="">/movies</span> now ' .
                                'returns a <span class="mill-changelog_http_code" data-mill-resource-group="Movies" ' .
                                'data-mill-method="POST" data-mill-path="/movies" ' .
                                'data-mill-operation-id="createMovie" data-mill-http-code="201 Created" ' .
                                'data-mill-representation="">201 Created</span>.'
                        ]
                    ]
                ]
            ],
            'removed' => [
                'representations' => [
                    '<span class="mill-changelog_field" data-mill-field="external_urls.tickets" ' .
                        'data-mill-representation="Movie">external_urls.tickets</span> has been removed from the ' .
                        '<span class="mill-changelog_representation" data-mill-field="external_urls.tickets" ' .
                        'data-mill-representation="Movie">Movie</span> representation.'
                ]
            ]
        ], $compiled['1.1.3'], '1.1.3 changelog does not match');

        // v1.1.2
        $this->assertSame([
            '_details' => [
                'release_date' => '2017-04-01'
            ],
            'changed' => [
                'resources' => [
                    [
                        'The following <span class="mill-changelog_resource_group" ' .
                            'data-mill-resource-group="Movies">Movies</span> resources have changed:',
                        [
                            'On <span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movie/{id}" ' .
                                'data-mill-operation-id="getMovie_alt1" ' .
                                'data-mill-content-type="application/mill.example.movie+json">/movie/{id}</span>, ' .
                                '<span class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movie/{id}" ' .
                                'data-mill-operation-id="getMovie_alt1" ' .
                                'data-mill-content-type="application/mill.example.movie+json">GET</span> requests ' .
                                'now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Movies" data-mill-method="GET" ' .
                                'data-mill-path="/movie/{id}" data-mill-operation-id="getMovie_alt1" ' .
                                'data-mill-content-type="application/mill.example.movie+json">' .
                                'application/mill.example.movie+json</span> Content-Type header.',
                            'On <span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="getMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">/movies/{id}</span>, ' .
                                '<span class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="getMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">GET</span> requests ' .
                                'now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Movies" data-mill-method="GET" ' .
                                'data-mill-path="/movies/{id}" data-mill-operation-id="getMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">' .
                                'application/mill.example.movie+json</span> Content-Type header.',
                            'On <span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="updateMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">/movies/{id}</span>, ' .
                                '<span class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="updateMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">PATCH</span> requests ' .
                                'now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Movies" data-mill-method="PATCH" ' .
                                'data-mill-path="/movies/{id}" data-mill-operation-id="updateMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">' .
                                'application/mill.example.movie+json</span> Content-Type header.',
                            'On <span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movies" data-mill-operation-id="getMovies" ' .
                                'data-mill-content-type="application/mill.example.movie+json">/movies</span>, <span ' .
                                'class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movies" data-mill-operation-id="getMovies" ' .
                                'data-mill-content-type="application/mill.example.movie+json">GET</span> requests ' .
                                'now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Movies" data-mill-method="GET" data-mill-path="/movies" ' .
                                'data-mill-operation-id="getMovies" ' .
                                'data-mill-content-type="application/mill.example.movie+json">' .
                                'application/mill.example.movie+json</span> Content-Type header.',
                            'On <span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                'data-mill-method="POST" data-mill-path="/movies" ' .
                                'data-mill-operation-id="createMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">/movies</span>, <span ' .
                                'class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                'data-mill-method="POST" data-mill-path="/movies" ' .
                                'data-mill-operation-id="createMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">POST</span> requests ' .
                                'now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Movies" data-mill-method="POST" data-mill-path="/movies" ' .
                                'data-mill-operation-id="createMovie" ' .
                                'data-mill-content-type="application/mill.example.movie+json">' .
                                'application/mill.example.movie+json</span> Content-Type header.'
                        ]
                    ],
                    [
                        'The following <span class="mill-changelog_resource_group" ' .
                            'data-mill-resource-group="Theaters">Theaters</span> resources have changed:',
                        [
                            'On <span class="mill-changelog_path" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="GET" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="getTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">/theaters/{id}' .
                                '</span>, <span class="mill-changelog_method" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="GET" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="getTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">GET</span> requests ' .
                                'now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Theaters" data-mill-method="GET" ' .
                                'data-mill-path="/theaters/{id}" data-mill-operation-id="getTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">' .
                                'application/mill.example.theater+json</span> Content-Type header.',
                            'On <span class="mill-changelog_path" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="PATCH" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="updateTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">/theaters/{id}' .
                                '</span>, <span class="mill-changelog_method" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="PATCH" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="updateTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">PATCH</span> ' .
                                'requests now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Theaters" data-mill-method="PATCH" ' .
                                'data-mill-path="/theaters/{id}" data-mill-operation-id="updateTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">' .
                                'application/mill.example.theater+json</span> Content-Type header.',
                            'On <span class="mill-changelog_path" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="GET" data-mill-path="/theaters" ' .
                                'data-mill-operation-id="getTheaters" ' .
                                'data-mill-content-type="application/mill.example.theater+json">/theaters</span>, ' .
                                '<span class="mill-changelog_method" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="GET" data-mill-path="/theaters" ' .
                                'data-mill-operation-id="getTheaters" ' .
                                'data-mill-content-type="application/mill.example.theater+json">GET</span> requests ' .
                                'now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Theaters" data-mill-method="GET" ' .
                                'data-mill-path="/theaters" data-mill-operation-id="getTheaters" ' .
                                'data-mill-content-type="application/mill.example.theater+json">' .
                                'application/mill.example.theater+json</span> Content-Type header.',
                            'On <span class="mill-changelog_path" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="POST" data-mill-path="/theaters" ' .
                                'data-mill-operation-id="createTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">/theaters</span>, ' .
                                '<span class="mill-changelog_method" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="POST" data-mill-path="/theaters" ' .
                                'data-mill-operation-id="createTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">POST</span> ' .
                                'requests now return a <span class="mill-changelog_content_type" ' .
                                'data-mill-resource-group="Theaters" data-mill-method="POST" ' .
                                'data-mill-path="/theaters" data-mill-operation-id="createTheater" ' .
                                'data-mill-content-type="application/mill.example.theater+json">' .
                                'application/mill.example.theater+json</span> Content-Type header.'
                        ]
                    ]
                ]
            ],
            'removed' => [
                'resources' => [
                    [
                        'The following <span class="mill-changelog_resource_group" ' .
                            'data-mill-resource-group="Theaters">Theaters</span> resources have removed:',
                        [
                            '<span class="mill-changelog_method" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="PATCH" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="updateTheater" data-mill-http-code="403 Forbidden" ' .
                                'data-mill-representation="Coded error">PATCH</span> requests to <span ' .
                                'class="mill-changelog_path" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="PATCH" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="updateTheater" data-mill-http-code="403 Forbidden" ' .
                                'data-mill-representation="Coded error">/theaters/{id}</span> no longer returns a ' .
                                '<span class="mill-changelog_http_code" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="PATCH" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="updateTheater" data-mill-http-code="403 Forbidden" ' .
                                'data-mill-representation="Coded error">403 Forbidden</span> with a <span ' .
                                'class="mill-changelog_representation" data-mill-resource-group="Theaters" ' .
                                'data-mill-method="PATCH" data-mill-path="/theaters/{id}" ' .
                                'data-mill-operation-id="updateTheater" data-mill-http-code="403 Forbidden" ' .
                                'data-mill-representation="Coded error">Coded error</span> representation: If ' .
                                'something cool happened.'
                        ]
                    ]
                ]
            ]
        ], $compiled['1.1.2'], '1.1.2 changelog does not match');

        // v1.1.1
        $this->assertSame([
            '_details' => [
                'release_date' => '2017-03-01'
            ],
            'added' => [
                'resources' => [
                    [
                        'The following <span class="mill-changelog_resource_group" ' .
                            'data-mill-resource-group="Movies">Movies</span> resources have added:',
                        [
                            'A <span class="mill-changelog_parameter" data-mill-resource-group="Movies" ' .
                                'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                'data-mill-operation-id="updateMovie" data-mill-parameter="imdb">imdb</span> request ' .
                                'parameter was added to <span class="mill-changelog_method" ' .
                                'data-mill-resource-group="Movies" data-mill-method="PATCH" ' .
                                'data-mill-path="/movies/{id}" data-mill-operation-id="updateMovie" ' .
                                'data-mill-parameter="imdb">PATCH</span> on <span class="mill-changelog_path" ' .
                                'data-mill-resource-group="Movies" data-mill-method="PATCH" ' .
                                'data-mill-path="/movies/{id}" data-mill-operation-id="updateMovie" ' .
                                'data-mill-parameter="imdb">/movies/{id}</span>.'
                        ]
                    ]
                ]
            ]
        ], $compiled['1.1.1'], '1.1.1 changelog does not match');

        // v1.1
        $this->assertSame([
            '_details' => [
                'release_date' => '2017-02-01'
            ],
            'added' => [
                'representations' => [
                    [
                        'The <span class="mill-changelog_representation" data-mill-field="external_urls" ' .
                            'data-mill-representation="Movie">Movie</span> representation has added the following ' .
                            'fields:',
                        [
                            '<span class="mill-changelog_field" data-mill-field="external_urls" ' .
                                'data-mill-representation="Movie">external_urls</span>',
                            '<span class="mill-changelog_field" data-mill-field="external_urls.imdb" ' .
                                'data-mill-representation="Movie">external_urls.imdb</span>',
                            '<span class="mill-changelog_field" data-mill-field="external_urls.tickets" ' .
                                'data-mill-representation="Movie">external_urls.tickets</span>',
                            '<span class="mill-changelog_field" data-mill-field="external_urls.trailer" ' .
                                'data-mill-representation="Movie">external_urls.trailer</span>'
                        ]
                    ]
                ],
                'resources' => [
                    [
                        'The following <span class="mill-changelog_resource_group" ' .
                            'data-mill-resource-group="Movies">Movies</span> resources have added:',
                        [
                            [
                                '<span class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                    'data-mill-path="/movies/{id}">/movies/{id}</span> has been added with support ' .
                                    'for the following HTTP methods:',
                                [
                                    '<span class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="PATCH" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="updateMovie">PATCH</span>',
                                    '<span class="mill-changelog_method" data-mill-resource-group="Movies" ' .
                                        'data-mill-method="DELETE" data-mill-path="/movies/{id}" ' .
                                        'data-mill-operation-id="deleteMovie">DELETE</span>'
                                ]
                            ],
                            'A <span class="mill-changelog_parameter" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movies" ' .
                                'data-mill-operation-id="getMovies" data-mill-parameter="page">page</span> request ' .
                                'parameter was added to <span class="mill-changelog_method" ' .
                                'data-mill-resource-group="Movies" data-mill-method="GET" data-mill-path="/movies" ' .
                                'data-mill-operation-id="getMovies" data-mill-parameter="page">GET</span> on <span ' .
                                'class="mill-changelog_path" data-mill-resource-group="Movies" ' .
                                'data-mill-method="GET" data-mill-path="/movies" data-mill-operation-id="getMovies" ' .
                                'data-mill-parameter="page">/movies</span>.',
                            [
                                'The following parameters have been added to <span class="mill-changelog_method" ' .
                                    'data-mill-resource-group="Movies" data-mill-method="POST" ' .
                                    'data-mill-path="/movies">POST</span> on <span class="mill-changelog_path" ' .
                                    'data-mill-resource-group="Movies" data-mill-method="POST" ' .
                                    'data-mill-path="/movies">/movies</span>:',
                                [
                                    '<span class="mill-changelog_parameter" data-mill-parameter="imdb" ' .
                                        'data-mill-resource-group="Movies" data-mill-method="POST" ' .
                                        'data-mill-path="/movies">imdb</span>',
                                    '<span class="mill-changelog_parameter" data-mill-parameter="trailer" ' .
                                        'data-mill-resource-group="Movies" data-mill-method="POST" ' .
                                        'data-mill-path="/movies">trailer</span>'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'removed' => [
                'representations' => [
                    '<span class="mill-changelog_field" data-mill-field="website" ' .
                        'data-mill-representation="Theater">website</span> has been removed from the <span ' .
                        'class="mill-changelog_representation" data-mill-field="website" ' .
                        'data-mill-representation="Theater">Theater</span> representation.'
                ]
            ]
        ], $compiled['1.1'], '1.1 changelog does not match');
    }
}
