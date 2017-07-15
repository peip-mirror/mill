<?php
namespace Mill\Generator\Changelog\Changesets;

use Mill\Generator\Changelog\Changeset;

class ActionThrows extends Changeset
{
    /**
     * @var array
     */
    protected $templates = [
        'plural' => [
            'added' => '{uri} will now throw the following errors on {method} requests:',
            'removed' => '{uri} will no longer throw the following errors on {method} requests:'
        ],
        'singular' => [
            'added' => 'On {method} requests to {uri}, a {http_code} with a {representation} representation ' .
                'will now be returned: {description}',
            'removed' => '{method} requests to {uri} longer will return a {http_code} with a {representation} ' .
                'representation: {description}'
        ]
    ];

    /**
     * @inheritdoc
     */
    public function compileAddedOrRemovedChangeset($definition, array $changes = [])
    {
        if (count($changes) === 1) {
            $change = array_shift($changes);
            $template = $this->templates['singular'][$definition];
            return $this->renderText($template, $change);
        }

        $methods = [];
        foreach ($changes as $change) {
            $methods[$change['method']][] = $change;
        }

        $entries = [];
        foreach ($methods as $method => $changes) {
            if (count($changes) > 1) {
                $errors = [];
                foreach ($changes as $change) {
                    $errors[] = $this->renderText(
                        '{http_code} with a {representation} representation: {description}',
                        $change
                    );
                }

                $template = $this->templates['plural'][$definition];
                $entries[] = [
                    $this->renderText($template, [
                        'method' => $method,
                        'uri' => array_shift($changes)['uri']
                    ]),
                    array_unique($errors)
                ];
                continue;
            }

            $change = array_shift($changes);
            $template = $this->templates['singular'][$definition];
            $entries[] = $this->renderText($template, $change);
        }

        return $entries;
    }

    /**
     * @inheritdoc
     */
    public function compileChangedChangeset($definition, array $changes = [])
    {
        throw new \Exception($definition . ' action throws changes are not yet supported.');
    }
}
