<?php

namespace bongrun\generator;

class Method
{
    private $setting = [
        'codeFull' => null,
        'commentFull' => null,
        'name' => null,
        'comments' => [],
        'codeLines' => [],
        'params' => [],
        'type' => 'public',
        'returnType' => null,
    ];

    public function __construct(array $setting = [])
    {
        $this->setSetting($setting);
    }

    public function getName()
    {
        return $this->setting['name'] ?? null;
    }

    public function getReturnType()
    {
        return $this->setting['returnType'] ?? null;
    }

    public function setReturnType($returnType)
    {
        $this->setting['returnType'] = $returnType;
    }

    public function getParams()
    {
        return $this->setting['params'] ?? [];
    }

    public function setParams($params)
    {
        $this->setting['params'] = $params;
    }

    public function setSetting($setting)
    {
        $this->setting = array_merge($this->setting, $setting);
    }

    public function isValid()
    {
        if (is_null($this->setting['codeFull']) && (is_null($this->setting['name']) || count($this->setting['codeLines']) === 0)) {
            throw new \Exception('Не все settings заданы');
        }
    }

    public function getCode()
    {
        $this->isValid();
        if (!is_null($this->setting['codeFull'])) {
            var_dump($this->setting['commentFull']);
            return (!is_null($this->setting['commentFull']) && $this->setting['commentFull'] ? "    " . $this->setting['commentFull'] . "\n" : '') .
                "    " . trim($this->setting['codeFull']);
        } else {
            return sprintf(
                "    %s%s function %s(%s)%s
    {
        %s
    }"
                ,
                ($this->setting['comments'] ? "/**" . PHP_EOL . '     * ' . implode(PHP_EOL . '     * ', $this->setting['comments']) . PHP_EOL . '     */' . PHP_EOL . '    ' : ''),
                $this->setting['type'],
                $this->setting['name'],
                implode(', ', array_map(function ($item) {
                        return (isset($item['type']) ? $item['type'] . ' ' : '') . $item['name'];
                    }, $this->setting['params'])
                ),
                ($this->setting['returnType'] ? ': ' . $this->setting['returnType'] : ''),
                implode(PHP_EOL . '        ', $this->setting['codeLines'])
            );
        }
    }
}