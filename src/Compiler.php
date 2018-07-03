<?php
namespace Mill;

use Mill\Parser\Annotations\PathAnnotation;
use Mill\Parser\Annotations\VendorTagAnnotation;
use Mill\Parser\Representation;
use Mill\Parser\Resource;
use Mill\Parser\Version;

class Compiler
{
    /** @var Config */
    protected $config;

    /** @var Version|null */
    protected $version = null;

    /** @var array */
    protected $supported_versions = [];

    /** @var array Compiled documentation. */
    protected $compiled = [
        'representations' => [],
        'resources' => []
    ];

    /**
     * A setting to compile documentation for documentation that's been marked, through a `:private` decorator, as
     * private.
     *
     * @var bool
     */
    protected $load_private_docs = true;

    /**
     * Vendor tags to compile documentation against. If this array contains vendor tags, only public and documentation
     * that have a matched tag will be compiled. If this is null, this will be disregarded and everything will be
     * compiled.
     *
     * @var array|null
     */
    protected $load_vendor_tag_docs = null;

    /**
     * @param Config $config
     * @param null|Version $version
     */
    public function __construct(Config $config, Version $version = null)
    {
        $this->config = $config;
        $this->version = $version;

        $this->supported_versions = $this->config->getApiVersions();
    }

    /**
     * Compile API documentation into a parseable collection.
     *
     * @return array
     */
    public function compile(): array
    {
        $resources = $this->compileResources($this->parseResources());
        foreach ($resources as $version => $groups) {
            ksort($resources[$version]);
        }

        $representations = $this->compileRepresentations($this->parseRepresentations());
        foreach ($representations as $version => $data) {
            ksort($representations[$version]);
        }

        $this->compiled['resources'] = $resources;
        $this->compiled['representations'] = $representations;

        return $this->compiled;
    }

    /**
     * Run through configured controllers, parse them, and compile a collection of resource action documentation.
     *
     * @return array
     * @throws Exceptions\Annotations\MultipleAnnotationsException
     * @throws Exceptions\Annotations\RequiredAnnotationException
     */
    protected function parseResources(): array
    {
        $resources = [];
        foreach ($this->config->getControllers() as $controller) {
            $docs = (new Resource\Documentation($controller))->parse();
            $annotations = $docs->toArray();

            /** @var \Mill\Parser\Resource\Action\Documentation $method */
            foreach ($docs->getMethods() as $method) {
                $group = $method->getGroup();

                // Set the amount of aliases that we've accrued here so we can properly enforce uniqueness of operation
                // IDs on aliased paths.
                $aliases = 0;

                /** @var \Mill\Parser\Annotations\PathAnnotation $path */
                foreach ($method->getPaths() as $path) {
                    // Are we compiling documentation for a private or protected resource?
                    if (!$this->shouldParsePath($method, $path)) {
                        continue;
                    }

                    $is_aliased = $path->isAliased();

                    // We're always going to be compiling documentation for this path, regardless if it's an alias or
                    // not, so let's strip any awareness of that.
                    $path->setAliased(false);
                    $path->setAliases([]);

                    $resource_label = $annotations['label'];
                    if (!isset($resources[$group]['resources'][$resource_label])) {
                        $resources[$group]['resources'][$resource_label] = [
                            'label' => $annotations['label'],
                            'description' => $annotations['description'],
                            'actions' => []
                        ];
                    }

                    // Set any params that belong to this path on onto this action.
                    $params = [];

                    /** @var \Mill\Parser\Annotations\PathParamAnnotation $param */
                    foreach ($method->getPathParameters() as $param) {
                        if ($path->doesPathHaveParam($param)) {
                            $params[$param->getField()] = $param;
                        }
                    }

                    // Set the lone path that this action and group run under.
                    $action = clone $method;
                    $action->setPath($path);
                    $action->setPathParams($params);
                    $action->filterAnnotationsForVisibility($this->load_private_docs, $this->load_vendor_tag_docs);

                    if ($is_aliased) {
                        $action->incrementOperationId(++$aliases);
                    }

                    // Hash the action so we don't happen to double up and end up with dupes.
                    $identifier = $action->getPath()->getPath() . '::' . $action->getMethod();

                    $resources[$group]['resources'][$resource_label]['actions'][$identifier] = $action;
                }
            }
        }

        return $resources;
    }

    /**
     * Compile parsed resources into a versioned collection.
     *
     * @psalm-suppress EmptyArrayAccess Psalm thinks that `$resources[$version][$group]['resources']` is an empty
     *      value array. It is not.
     * @param array $parsed
     * @return array
     * @throws \Exception
     */
    private function compileResources(array $parsed = []): array
    {
        $resources = [];
        foreach ($parsed as $group => $group_data) {
            foreach ($group_data['resources'] as $resource_label => $resource) {
                /** @var Resource\Action\Documentation $action */
                foreach ($resource['actions'] as $identifier => $action) {
                    // Run through every supported API version and flatten out documentation for it.
                    foreach ($this->supported_versions as $supported_version) {
                        $version = $supported_version['version'];

                        // If we're compiling documentation for a specific version range, and this doesn't fall in
                        // that, then skip it.
                        if ($this->version && !$this->version->matches($version)) {
                            continue;
                        }

                        // If this method has either a minimum or maximum version specified, and we aren't compiling
                        // an acceptable version, skip it.
                        $min_version = $action->getMinimumVersion();
                        $max_version = $action->getMaximumVersion();
                        if ($min_version && !$min_version->matches($version)
                            || $max_version && !$max_version->matches($version)
                        ) {
                            continue;
                        }

                        if (!isset($resources[$version])) {
                            $resources[$version] = [];
                        } elseif (!isset($resources[$version][$group])) {
                            $resources[$version][$group] = [
                                'resources' => []
                            ];
                        }

                        // Filter down the annotations on this action for just those of the current version we're
                        // compiling documentation for.
                        $cloned = clone $action;
                        $cloned->filterAnnotationsForVersion($version);

                        if (!isset($resources[$version][$group]['resources'][$resource_label])) {
                            $resources[$version][$group]['resources'][$resource_label] = [
                                'label' => $resource['label'],
                                'description' => $resource['description'],
                                'actions' => []
                            ];
                        }

                        // Hash the action so we don't happen to double up and end up with dupes, and then remove the
                        // currently non-hash index from the action array.
                        $identifier = $cloned->getPath()->getPath() . '::' . $cloned->getMethod();

                        $resources[$version][$group]['resources'][$resource_label]['actions'][$identifier] = $cloned;
                    }
                }
            }
        }

        return $resources;
    }

    /**
     * Run through configured representations, parse them, and compile a collection of representation documentation.
     *
     * @return array
     * @throws Exceptions\Annotations\MultipleAnnotationsException
     * @throws Exceptions\Annotations\RequiredAnnotationException
     * @throws Exceptions\Resource\NoAnnotationsException
     */
    protected function parseRepresentations(): array
    {
        $representations = [];
        $error_representations = $this->config->getErrorRepresentations();

        /** @var array $representation */
        foreach ($this->config->getAllRepresentations() as $class => $representation) {
            // If we're running through a standard (non-error) representation, let's make sure we don't want it
            // excluded.
            if (!isset($error_representations[$class])) {
                // If the representation is being excluded, then don't set it up for compilation.
                if ($this->config->isRepresentationExcluded($class)) {
                    continue;
                }
            }

            $parsed = (new Representation\Documentation($class, $representation['method']))->parse();
            $parsed->filterAnnotationsForVisibility($this->load_vendor_tag_docs);

            $representations[$class] = $parsed;
        }

        return $representations;
    }

    /**
     * Compile parsed representations into a versioned collection.
     *
     * @param array $parsed
     * @return array
     */
    private function compileRepresentations(array $parsed = []): array
    {
        $representations = [];

        foreach ($parsed as $identifier => $representation) {
            // Run through every supported API version and flatten out documentation for it.
            foreach ($this->supported_versions as $supported_version) {
                $version = $supported_version['version'];

                // If we're compiling documentation for a specific version range, and this doesn't fall in that, then
                // skip it.
                if ($this->version && !$this->version->matches($version)) {
                    continue;
                }

                // Filter down the annotations on this action for just those of the current version we're compiling
                // documentation for.
                $cloned = clone $representation;
                $cloned->filterRepresentationForVersion($version);

                $representations[$version][$identifier] = $cloned;
            }
        }

        return $representations;
    }

    /**
     * Get compiled representations.
     *
     * @param null|string|Version $version
     * @return array
     */
    public function getRepresentations($version = null): array
    {
        if (empty($version)) {
            return $this->compiled['representations'];
        }

        if ($version instanceof Version) {
            $version = $version->getConstraint();
        }

        return $this->compiled['representations'][$version];
    }

    /**
     * Get compiled resources.
     *
     * @param null|string $version
     * @return array
     */
    public function getResources(string $version = null): array
    {
        if (empty($version)) {
            return $this->compiled['resources'];
        }

        return $this->compiled['resources'][$version];
    }

    /**
     * Set if we'll be loading documentation that's been marked as being private.
     *
     * @param bool $load_private_docs
     * @return Compiler
     */
    public function setLoadPrivateDocs(bool $load_private_docs = true): self
    {
        $this->load_private_docs = $load_private_docs;
        return $this;
    }

    /**
     * Set an array of vendor tags that we'll be compiling documentation against.
     *
     * If you want all documentation, even that which has a vendor tag, supply `null`. If you want documentation that
     * either has no vendor tag, or specific ones, supply an array with those vendor tag names.
     *
     * @param array|null $vendor_tags
     * @return Compiler
     */
    public function setLoadVendorTagDocs(?array $vendor_tags): self
    {
        $this->load_vendor_tag_docs = $vendor_tags;
        return $this;
    }

    /**
     * With the rules set up on the compiler, should we parse a supplied path?
     *
     * @param Resource\Action\Documentation $method
     * @param PathAnnotation $path
     * @return bool
     */
    private function shouldParsePath(Resource\Action\Documentation $method, PathAnnotation $path): bool
    {
        $path_data = $path->toArray();
        $vendor_tags = $method->getVendorTags();

        // Should we compile documentation that has a vendor tag?
        if (!empty($vendor_tags) && !is_null($this->load_vendor_tag_docs)) {
            // We don't have any configured vendor tags to pull documentation for, so this path shouldn't be parsed.
            if (empty($this->load_vendor_tag_docs)) {
                return false;
            }

            $all_found = true;

            /** @var VendorTagAnnotation $vendor_tag */
            foreach ($vendor_tags as $vendor_tag) {
                if (!in_array($vendor_tag->getVendorTag(), $this->load_vendor_tag_docs)) {
                    $all_found = false;
                }
            }

            // This path should only be parsed if it has every vendor tag we're looking for.
            if ($all_found) {
                return true;
            }

            return false;
        }

        // If we aren't compiling docs for private resource, but this path is private, we shouldn't parse it.
        if (!$this->load_private_docs && !$path_data['visible']) {
            return false;
        }

        return true;
    }
}