<?php

namespace App\Routing;

use Doctrine\Common\Annotations\Reader;
use Jawira\CaseConverter\Convert;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * MessageClassLoader is responsible for loading routing information from a Message class used by symfony/messenger.
 */
class MessageClassLoader implements LoaderInterface
{
    protected $reader;

    /**
     * @var string
     */
    protected $routeAnnotationClass = 'Symfony\\Component\\Routing\\Annotation\\Route';

    /**
     * @var int
     */
    protected $defaultRouteIndex = 0;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Sets the annotation class to read route properties from.
     *
     * @param string $class A fully-qualified class name
     */
    public function setRouteAnnotationClass($class)
    {
        $this->routeAnnotationClass = $class;
    }

    /**
     * Loads from annotations from a class.
     *
     * @param string      $class A class name
     * @param string|null $type  The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class->getName()));
        }

        $collection = new RouteCollection();
        $collection->addResource(new FileResource((string) $class->getFileName()));

        $globals = $this->resetGlobals();
        foreach ($this->reader->getClassAnnotations($class) as $annot) {
            if ($annot instanceof $this->routeAnnotationClass) {
                $this->addRoute($collection, $annot, $globals, $class, $this->getMethod());
            }
        }

        return $collection;
    }

    /**
     * @return \ReflectionMethod
     *
     * @throws \ReflectionException
     *
     * @todo Read configuration
     */
    private function getMethod(): \ReflectionMethod
    {
        $class = new \ReflectionClass(\App\Controller\ApiController::class);

        return $class->getMethod('__invoke');
    }

    protected function addRoute(
        RouteCollection $collection, $annot, $globals, \ReflectionClass $class, \ReflectionMethod $method
    ) {
        $name = $annot->getName();
        if (null === $name) {
            $name = $this->getDefaultRouteName($class, $method);
        }
        $name = $globals['name'].$name;

        $requirements = $annot->getRequirements();

        foreach ($requirements as $placeholder => $requirement) {
            if (\is_int($placeholder)) {
                @trigger_error(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" of route "%s" in "%s::%s()"?', $placeholder, $requirement, $name, $class->getName(), $method->getName()), E_USER_DEPRECATED);
            }
        }

        $defaults = array_replace($globals['defaults'], $annot->getDefaults());
        $requirements = array_replace($globals['requirements'], $requirements);
        $options = array_replace($globals['options'], $annot->getOptions());
        $schemes = array_merge($globals['schemes'], $annot->getSchemes());
        $methods = array_merge($globals['methods'], $annot->getMethods());

        $host = $annot->getHost();
        if (null === $host) {
            $host = $globals['host'];
        }

        $condition = $annot->getCondition();
        if (null === $condition) {
            $condition = $globals['condition'];
        }

        $path = $annot->getLocalizedPaths() ?: $annot->getPath();
        $prefix = $globals['localized_paths'] ?: $globals['path'];
        $paths = [];

        if (\is_array($path)) {
            if (!\is_array($prefix)) {
                foreach ($path as $locale => $localePath) {
                    $paths[$locale] = $prefix.$localePath;
                }
            } elseif ($missing = array_diff_key($prefix, $path)) {
                throw new \LogicException(sprintf('Route to "%s" is missing paths for locale(s) "%s".', $class->name.'::'.$method->name, implode('", "', array_keys($missing))));
            } else {
                foreach ($path as $locale => $localePath) {
                    if (!isset($prefix[$locale])) {
                        throw new \LogicException(sprintf('Route to "%s" with locale "%s" is missing a corresponding prefix in class "%s".', $method->name, $locale, $class->name));
                    }

                    $paths[$locale] = $prefix[$locale].$localePath;
                }
            }
        } elseif (\is_array($prefix)) {
            foreach ($prefix as $locale => $localePrefix) {
                $paths[$locale] = $localePrefix.$path;
            }
        } else {
            $paths[] = $prefix.$path;
        }

        foreach ($method->getParameters() as $param) {
            if (isset($defaults[$param->name]) || !$param->isDefaultValueAvailable()) {
                continue;
            }
            foreach ($paths as $locale => $path) {
                if (false !== strpos($path, sprintf('{%s}', $param->name))) {
                    $defaults[$param->name] = $param->getDefaultValue();
                    break;
                }
            }
        }

        foreach ($paths as $locale => $path) {
            $route = $this->createRoute($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
            $route->setDefault('_controller', $method->class);
            $route->setDefault('type', $class->getName());
            if (0 !== $locale) {
                $route->setDefault('_locale', $locale);
                $route->setDefault('_canonical_route', $name);
                $collection->add($name.'.'.$locale, $route);
            } else {
                $collection->add($name, $route);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return \is_string($resource) && preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) && (!$type || 'message' === $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {
    }

    /**
     * Gets the default route name for a class method.
     *
     * @param \ReflectionClass  $class
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        return preg_replace([
            '/_message$/',
        ], [
            '',
        ], (new Convert($class->getShortName()))->toSnake());
    }

    protected function getGlobals(\ReflectionClass $class)
    {
        $globals = $this->resetGlobals();

        if ($annot = $this->reader->getClassAnnotation($class, $this->routeAnnotationClass)) {
            if (null !== $annot->getName()) {
                $globals['name'] = $annot->getName();
            }

            if (null !== $annot->getPath()) {
                $globals['path'] = $annot->getPath();
            }

            $globals['localized_paths'] = $annot->getLocalizedPaths();

            if (null !== $annot->getRequirements()) {
                $globals['requirements'] = $annot->getRequirements();
            }

            if (null !== $annot->getOptions()) {
                $globals['options'] = $annot->getOptions();
            }

            if (null !== $annot->getDefaults()) {
                $globals['defaults'] = $annot->getDefaults();
            }

            if (null !== $annot->getSchemes()) {
                $globals['schemes'] = $annot->getSchemes();
            }

            if (null !== $annot->getMethods()) {
                $globals['methods'] = $annot->getMethods();
            }

            if (null !== $annot->getHost()) {
                $globals['host'] = $annot->getHost();
            }

            if (null !== $annot->getCondition()) {
                $globals['condition'] = $annot->getCondition();
            }

            foreach ($globals['requirements'] as $placeholder => $requirement) {
                if (\is_int($placeholder)) {
                    @trigger_error(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" in "%s"?', $placeholder, $requirement, $class->getName()), E_USER_DEPRECATED);
                }
            }
        }

        return $globals;
    }

    private function resetGlobals()
    {
        return [
            'path' => null,
            'localized_paths' => [],
            'requirements' => [],
            'options' => [],
            'defaults' => [],
            'schemes' => [],
            'methods' => [],
            'host' => '',
            'condition' => '',
            'name' => '',
        ];
    }

    protected function createRoute($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition)
    {
        return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }
}
