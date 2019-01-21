<?php

namespace bongrun\generator;

class Template
{
    private $nameSpace;
    private $className;
    /** @var Method[] */
    private $methods;
    private $properties;
    private $uses;
    private $classComments;
    private $extends;
    private $implements;

    public function __construct(string $nameSpace, string $className, array $methods, array $properties = [], array $constants = [], array $uses = [], array $classComments = [], $extends = null, $implements = [])
    {
        $this->nameSpace = $nameSpace;
        $this->className = $className;
        $this->methods = $methods;
        $this->properties = $properties;
        $this->uses = $uses;
        $this->classComments = $classComments;
        $this->extends = $extends;
        $this->implements = $implements;
    }

    public function getCode()
    {
        return sprintf(
            "<?php

namespace %s;
%s%s
class %s%s%s
{
    %s%s
}"
            ,
            $this->nameSpace,
            ($this->uses ? PHP_EOL . 'use ' . implode(';' . PHP_EOL . 'use ', $this->uses) . ';' . PHP_EOL : ''),
            ($this->classComments ? PHP_EOL . "/**" . PHP_EOL . ' * ' . implode(PHP_EOL . '    * ', $this->classComments) . PHP_EOL . '*/' : ''),
            $this->className,
            ($this->extends ? ' extends ' . $this->extends : ''),
            ($this->implements ? ' implements ' . implode(', ', $this->implements) : ''),
            ($this->properties ?
                '' . implode(';' . PHP_EOL . '    ', array_map(function ($item) {
                        return (isset($item['comments']) ? "/**" . (count($item['comments']) > 1 ? PHP_EOL : '') . implode(PHP_EOL . '    * ', $item['comments']) . (count($item['comments']) > 1 ? PHP_EOL . '    ' : '') . '*/' . PHP_EOL : '') .
                            (isset($item['comments']) ? '    ' : '') . ($item['type'] ?? 'private') . ' ' . $item['name'];
                    }, $this->properties)
                ) . ';' . PHP_EOL . PHP_EOL : ''),
            ($this->methods ? implode(PHP_EOL . PHP_EOL, array_map(function ($method) {
                /** @var $method Method */
                return $method->getCode();
            }, $this->methods)) : '')
        );
    }
}