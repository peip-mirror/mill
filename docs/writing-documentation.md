---
id: writing-documentation
title: Documenting your API
---

## Resources
Resources are a collection of actions (endpoints). These can generally be referred to as a "controller" in a standard MVC application structure.

As long as a resource contains resource actions, there is nothing you need to do to document a resource. Resource action documentation will handle everything automatically.

## Resource Actions
A resource action (endpoint) is something that you can execute from your API.

Mill is currently a bit opinionated in how it expects the names of your action methods to be; it looks for methods in resources that have RESTful names: GET, POST, PUT, PATCH, and DELETE

Documenting a resource action is easy, however:

```php
class UsersController extends \MyApplication\Controller
{
    /**
     * Search for users.
     *
     * @api-label Search
     * @api-operationid searchUsers
     * @api-group Users
     *
     * @api-path:public /users
     *
     * @api-contenttype application/json
     *
     * @api-queryparam:public page (integer) - The page number to show.
     * @api-queryparam:public per_page (integer) - Number of items to show on
     *     each page. Max 100.
     * @api-queryparam:public query (string, required) - Search query.
     *
     * @api-return:public {collection} \MyApplication\Representation\User
     *
     * @api-error:public {503} \MyApplication\Representation\Error If search
     *     is disabled.
     */
    public function GET()
    {
        ...
    }
}
```

Here we're denoting that the action:

* Powers "Search for users", by way of the non-annotated description
* Handles the `GET /users`
* Returns its content as `application/json`
* Has five three request parameters
* Returns a collection of User representations
* Can throw a `503 Service Unavailable` error if search is disabled.

## Representations
A representation is an object of data that can be returned in a resource action. Since these cover a lot more data than a standard resource action, documenting them is a bit more work.

```php
<?php
namespace \MyApplication\Representation

/**
 * @api-label User
 */
class User extends \MyApplication\Representation
{
    public function _json($user)
    {
        return [
            /**
             * @api-data uri (uri) - The canonical relative URI for the user.
             */
            'uri' => sprintf('/users/%d', $user->id),

            /**
             * @api-data name (string) - The users' display name.
             */
            'name' => $user->getName(),

            /**
             * @api-data metadata (object) - The users' metadata.
             */
            'metadata' => [
                /**
                 * @api-data metadata.connections (object) - A list of resource
                 *     URIs related to the user.
                 */
                'connections' => [
                    /**
                     * @api-data metadata.connections.albums (object) - Info
                     *     about the albums created by this user.
                     */
                    'albums' => [
                        /**
                         * @api-data uri (uri) - URI that resolves to the
                         *     connection data.
                         */
                        'uri' => sprintf('/users/%s/albums', $user->id),

                        /**
                         * @api-data options (array) - Array of HTTP methods
                         *     allowed on this URI.
                         */
                        'options' => ['GET'],

                        /**
                         * @api-data total (number) - Total number of items on
                         *     this connection.
                         */
                        'total' => $user->getAlbums()->total
                    ]
                ]
            ]
        ];
    }
}
```

Here you can see that a User representation can contain:

* `uri`
* `name`
* `metadata[connections][albums]`
