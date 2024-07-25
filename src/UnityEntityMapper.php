<?php
namespace Cmrweb\UnityWebRequest;

use Doctrine\ORM\EntityManagerInterface;

class UnityEntityMapper
{
    private string $className;
    private array $entities;
    private array $unityScript = [];
    // proprerties
    // methods/struct ?? 
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        $this->entities = $this->getEntities();
    }

    private function getClass()
    {
        return $this->entities[$this->className];
    }

    public function createScriptFromEntity(string $className): ?self
    {
        $this->className = $className; 
        if ($this->exist()) {
            $this->createClass($className);
            $this->createProprieties(); 
        } else {
            return null;
        }  
        return $this;
    }

    public function getFile()
    {
        $output = [];
        foreach ($this->unityScript as $key => $line) {
            if ('properties' === $key || 'methods' === $key) {
                foreach ($this->unityScript[$key] as $subLine) {
                    array_push($output, $subLine);
                }
            } else {
                array_push($output, $line);
            }
        }
        return implode("\n", $output);
    }
 

    private function createProprieties()
    {
        foreach ($this->getClass() as $type => $name) {
            $this->addproperty([
                $this->createProperty(name: $name, type: $this->formatType($type))
            ]);
        }
    }

    public function createProperty(string $name, string $visibility = 'public', string $type = 'string', bool $getterSetter = true): string
    {
        return $this->line([
            $visibility,
            $type,
            $name,
            ($getterSetter ? '{ get; set; }' : ';')
        ], 1);
    }
 
    private function createMethods()
    {
        $this->addMethod([ 
            $this->createMethod(
                name: 'create',
                params: [
                    'string' => 'jsonString' 
                ],
                content:  [ 
                    "return JsonUtility.FromJson<" . $this->className . '>(jsonString);'
                ],
                type: 'static'
            ),
            $this->createMethod(
                name: 'ToJson',  
                content:  [ 
                    "return JsonUtility.ToJson(this);"
                ]
            ) 
        ]);  
    }

    protected function createMethod(string $name, ?array $params = null, array $content = [], string $visibility = 'public', string $type = 'void'): string
    {
        $method =  [
            $visibility,
            $type,
            ucfirst($name),
        ];
        $param = [];
        if(null !== $params) {
            foreach ($params as $type => $name) {
                $param[] = $type . ' ' . $name;
            }
        }

        $methodLine = $this->line($method, 1) . "(" . implode(', ', $param) . ")";

        return $this->line([
            $methodLine,
            implode("\n", [ 
                "{", 
                "\t\t".implode("\n\t\t\t",$content),
                "\t}\n"
            ]) 
        ]);
    }

    public function createClass(string $className, bool $serializable = true): static
    {
        $this->className = $className; 
        $this->append([
            'using System;',
            'using UnityEngine;'
        ]);
        $this->lineBreak();
        if ($serializable) {
            $this->append(['[Serializable]']);
        }
        $this->append([
            'public class ' . $this->className,
            '{',
            'properties' => [],
            '',
            'methods' => [], 
            '}'
        ]); 
        $this->createMethods();
        return $this;
    }

    protected function createStruct(string $name): string
    {
        /**
         * ??
         * array to list 
         * entity to struct
         * ??
         */
        # turn types to array 
        # serializer by type !!
        dd($name);
    }
    public function addproperty(array $content): void
    {
        $this->unityScript['properties'] = [...$this->unityScript['properties'], ...$content];
    }

    protected function addMethod(array $content): void
    {
        $this->unityScript['methods'] = [...$this->unityScript['methods'], ...$content];
    }

    private function append(array $content): void
    {
        $this->unityScript = [...$this->unityScript, ...$content];
    }

    private function line(array $content, int $tab = 0): string
    {
        $line = [];

        foreach ($content as $value) {
            array_push($line, $value);
        }
        $strLine = "";
        for ($i = 0; $i < $tab; $i++) {
            $strLine .= "\t";
        }
        return  $strLine . implode(" ", $line);
    }

    private function lineBreak(): void
    {
        $this->unityScript = [...$this->unityScript, ''];
    }

    private function formatType(string $type): string
    {
        return match ($type) {
            'string', 'bool', 'float' => $type,
            'integer' => 'int',
            default => $this->createStruct($type)
        };
    }


    private function exist(): bool
    {
        return in_array($this->className, array_keys($this->entities));
    }

    private function getEntities(): array
    {
        $entities = [];
        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $entity) {
            $shortName = $entity->getReflectionClass()->getShortName();
            foreach ($entity->getFieldNames() as $field) {
                $entities[$shortName][$entity->getTypeOfField($field)] = $field;
            }
        }
        return $entities;
    }
}
