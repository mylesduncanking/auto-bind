<?php

namespace MylesDuncanKing\AutoBind;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutoBindProperty
{
    /**
     * Specify the column to use for model binding.
     *
     * @param string|null $column The column to query on, defaults to 'id'.
     */
    public function __construct(
        public ?string $column = 'id'
    ) {
    }
}
