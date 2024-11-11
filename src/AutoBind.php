<?php

namespace MylesDuncanKing\AutoBind;

class AutoBind
{
    public static array $ignoreAutoBindProperties = ['middleware'];

    public static function bind($class): void
    {
        $reflection = new \ReflectionClass($class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $property->getType()?->getName() ?? null;

            // Check if the property has the AutoBind attribute
            $shouldAutoBind = count($property->getAttributes(Attribute::class)) > 0;

            if (
                /* No AutoBind attribute  */ ! $shouldAutoBind
                /* No type hint defined   */ || ! $type
                /* Is an ignored property */ || in_array($name, self::$ignoreAutoBindProperties)
                /* Not bound in the route */ || request()->route($name) === null
                /* Not a valid class      */ || ! class_exists($type)
            ) {
                continue;
            }

            $class->$name = (new $type())->findOrFail(request()->route($name));
            request()->route()->forgetParameter($name);
        }
    }

    public static function bound($class): array
    {
        $boundParameters = [];
        foreach (get_object_vars($class) as $key => $value) {
            if (in_array($key, self::$ignoreAutoBindProperties) || isset($data[$key])) {
                continue;
            }
            $boundParameters[$key] = $value;
        }
        return $boundParameters;
    }
}
