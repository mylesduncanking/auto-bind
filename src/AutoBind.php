<?php

namespace MylesDuncanKing\AutoBind;

/**
 * Handles automatic model binding for controller properties using attributes.
 *
 * The `AutoBind` class inspects public properties of a class for the `AutoBind` attribute
 * and binds them to models based on route parameters. It also allows for ignored properties
 * and supports custom columns for model lookup.
 */
class AutoBind
{
    // Static array to hold matched properties
    private static array $matchedProperties = [];

    /**
     * Automatically binds public properties of a class to their corresponding models.
     *
     * Inspects the public properties of the given class, checks for the `AutoBind` attribute,
     * and uses the specified column (or defaults to 'id') to query and bind the associated model.
     * Also removes the bound parameter from the route after binding.
     *
     * @param object $class The class instance to bind properties for.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If a model cannot be found.
     */
    public static function bind($class): void
    {
        $reflection = new \ReflectionClass($class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $name = $property->getName();
            $type = $property->getType()?->getName() ?? null;

            // Clear the matched properties for the given class before adding new ones
            self::$matchedProperties[get_class($class)] = [];

            // Check if the property has the AutoBind attribute
            $bindData = $property->getAttributes(Attribute::class);

            if (
                /* No AutoBind attribute  */ count($bindData) === 0
                /* No type hint defined   */ || ! $type
                /* Not bound in the route */ || request()->route($name) === null
                /* Not a valid class      */ || !class_exists($type)
            ) {
                continue;
            }

            // Get the attribute instance and extract the column
            $attributeInstance = $bindData[0]->newInstance();
            $column = $attributeInstance->column ?? 'id';

            // Perform the binding
            $class->$name = $type::where($column, request()->route($name))->firstOrFail();
            request()->route()->forgetParameter($name);

            // Store the matched property
            self::$matchedProperties[get_class($class)][] = $name;
        }
    }

    /**
     * Retrieves bound properties of a class and merges them into an array.
     *
     * Iterates through the public properties of the given class and adds them to the provided array.
     * Skips ignored properties and those already present in the array.
     *
     * @param object $class The class instance to retrieve properties from.
     * @param array $data An optional array to merge the bound properties into.
     *
     * @return array The merged array containing bound properties.
     */
    public static function bound($class, $data = []): array
    {
        // Get the fully qualified class name of the given class instance
        $className = get_class($class);

        // Check if the class has any matched properties in the static array
        // - if no matched properties for the class, return the original data
        if (! isset(self::$matchedProperties[$className])) {
            return $data;
        }

        // Loop through all matched properties for the specific class
        // - Ensure they exist then add to the $data array if not already set
        foreach (self::$matchedProperties[$className] as $name) {
            if (isset($class->$name) && ! array_key_exists($name, $data)) {
                $data[$name] = $class->$name;
            }
        }

        return $data;
    }
}
