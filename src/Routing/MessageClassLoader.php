<?php

namespace App\Routing;

use Jawira\CaseConverter\Convert;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * MessageClassLoader is responsible for loading routing information from a Message class used by symfony/messenger.
 */
class MessageClassLoader extends AnnotationClassLoader
{
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

        $globals = [
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
    protected function getMethod(): \ReflectionMethod
    {
        $class = new \ReflectionClass(\App\Controller\ApiController::class);

        return $class->getMethod('__invoke');
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

    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        $route->setDefault('_controller', $method->class);
        $route->setDefault('type', $class->getName());
    }
}
