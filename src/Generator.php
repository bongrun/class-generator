<?php

namespace bongrun\generator;

class Generator
{
    private $path;
    private $namespace;
    private $isRework;

    public function __construct($path, $namespace, $isRework = false)
    {
        $this->path = $path;
        $this->namespace = $namespace;
        $this->isRework = $isRework;
    }

    /**
     * @param string $className
     * @param Method[] $methods
     * @param array $properties
     * @param array $constants
     * @param array $uses
     * @param array $classComments
     * @param null $extends
     * @param array $implements
     */
    public function run(string $className, array $methods, array $properties = [], array $constants = [], array $uses = [], array $classComments = [], $extends = null, $implements = [])
    {
        if ($this->isRework && file_exists($this->getPath($className))) {
            require_once $this->getPath($className);
            $reflectionClass = new \ReflectionClass($this->getClassFullName($className));
            if ($reflectionClass->getParentClass()) {
                $extends = $reflectionClass->getParentClass()->name;
            }
            if ($reflectionClass->getInterfaces()) {
                $implements = array_map(function ($interface) use($uses) {
                    return $interface->name;
                }, $reflectionClass->getInterfaces());
            }
            if ($reflectionClass->getDocComment()) {
                $classComments = array_map(function ($line) {
                    return trim($line, ' /*');
                }, explode(PHP_EOL, $reflectionClass->getDocComment()));
                $classComments = array_filter($classComments);
            }
            $usesLines = array_slice(explode(PHP_EOL, file_get_contents($this->getPath($className))), 0, $reflectionClass->getStartLine());
            foreach ($usesLines as $useLine) {
                if (substr(trim($useLine), 0, 3) === 'user') {
                    $uses[] = trim(substr(trim($useLine), 3, -1));
                }
            }
            foreach ($reflectionClass->getProperties() as $property) {
                if ($property->getDeclaringClass() !== $this->getClassFullName($className)) {
                    continue;
                }
                $property->setAccessible(true);
                $properties = array_filter($properties, function ($propertyItem) use ($property) {
                    return $property->name !== $propertyItem['name'];
                });
                $propertyInsert = [
                    'name' => $property->name,
                    'type' => ($property->isPrivate() ? 'private' : ($property->isPublic() ? 'public' : 'protected')),
                ];
                if ($property->getDocComment()) {
                    $propertyInsert['comments'] = array_map(function ($line) {
                        return trim($line, ' /*');
                    }, explode(PHP_EOL, $reflectionClass->getDocComment()));
                    $propertyInsert['comments'] = array_filter($propertyInsert['comments']);
                }
                if ($property->getValue()) {
                    $propertyInsert['value'] = $property->getValue();
                }
                $properties[] = $propertyInsert;
            }
            foreach ($reflectionClass->getConstants() as $constantName => $constantValue) {
                $constants = array_filter($constants, function ($constantItemName) use ($constantName) {
                    return $constantItemName !== $constantName;
                });
                $constants[] = [
                    'name' => $constantName,
                    'value' => $constantValue,
                ];
            }
            foreach ($reflectionClass->getMethods() as $method) {
                $methods = array_filter($methods, function ($methodItem) use ($method) {
                    return $methodItem->getName() !== $method->name;
                });
                $methodLines = array_slice(explode(PHP_EOL, file_get_contents($this->getPath($className))), $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);
                $methods[] = new Method([
                    'codeFull' => implode(PHP_EOL, $methodLines),
                    'commentFull' => $method->getDocComment(),
                ]);
            }
        }
        foreach ($methods as $method) {
            if ($method->getReturnType() && !in_array($method->getReturnType(), ['string', 'int', 'array', 'float'])) {
                $uses[] = $method->getReturnType();
                $method->setReturnType(static::getClassName($method->getReturnType()));
            }
            $params = $method->getParams();
            foreach ($params as &$param) {
                if (isset($params['type']) && !in_array($params['type'], ['string', 'int', 'array', 'float'])) {
                    $uses[] = $params['type'];
                    $params['type'] = static::getClassName($params['type']);
                }
            }
            unset($param);
            $method->setParams($params);
        }
        $uses[] = $extends;
        $extends = static::getClassName($extends);
        $implements = array_map(function ($interface) {
            $uses[] = $interface;
            return static::getClassName($interface);
        }, $implements);
        $uses = array_unique($uses);
        $uses = array_filter($uses, function ($use) use ($className) {
            return static::getNamespace($use) !== static::getNamespace($this->getClassFullName($className));
        });
        $template = new Template($this->namespace, $className, $methods, $properties, $constants, $uses, $classComments, $extends, $implements);
        file_put_contents($this->getPath($className), $template->getCode());
    }

    private function getPath($className)
    {
        return $this->path . '/' . $className . '.php';
    }

    private function getClassFullName($className)
    {
        return $this->namespace . '\\' . $className;
    }

    private static function getNamespace($classFullName)
    {
        return implode('\\', array_slice(explode('\\', $classFullName), 0, -1));
    }

    private static function getClassName($classFullName)
    {
        return array_slice(explode('\\', $classFullName), -1)[0] ?? null;
    }
}