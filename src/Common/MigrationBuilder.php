<?php

/**
 * @Author: thedv
 * @Date:   2016-07-29 15:44:24
 * @Last Modified by:   thedv
 * @Last Modified time: 2016-08-01 13:45:09
 */

namespace Qsoft\Generator\Common;

use Qsoft\Generator\GeneratorException;

class MigrationBuilder
{
    /**
     * A template to be inserted.
     *
     * @var string
     */
    private $template;

    /**
     * Create the PHP syntax for the given schema.
     *
     * @param  array $schema
     * @param  array $meta
     * @return string
     */
    public function create($schema, $meta)
    {
        $up   = $this->createSchemaForUpMethod($schema, $meta);
        $down = $this->createSchemaForDownMethod($schema, $meta);

        return compact('up', 'down');
    }

    /**
     * Create the schema for the "up" method.
     *
     * @param  string $schema
     * @param  array $meta
     * @return string
     * @throws GeneratorException
     */
    private function createSchemaForUpMethod($schema, $meta)
    {
        $fields = $this->constructSchema($schema, $meta);

        switch ($meta['action']) {
            case 'create':
                return $this->insert($fields)->into($this->getCreateSchemaWrapper());
                break;
            case 'add':
                return $this->insert($fields)->into($this->getChangeSchemaWrapper());
                break;
            case 'remove':
                $fields = $this->constructSchema($schema, $meta, 'Drop');
                return $this->insert($fields)->into($this->getChangeSchemaWrapper());
                break;
            default:
                return $this->insert($fields)->into($this->getCreateSchemaWrapper());
                break;
        }

        // Otherwise, we have no idea how to proceed.
        throw new GeneratorException;
    }

    /**
     * Construct the syntax for a down field.
     *
     * @param  array $schema
     * @param  array $meta
     * @return string
     * @throws GeneratorException
     */
    private function createSchemaForDownMethod($schema, $meta)
    {
        // If the user created a table, then for the down
        // method, we should drop it.
        switch ($meta['action']) {
            case 'create':
                return sprintf("Schema::drop('%s');", $meta['table']);
                break;
            // If the user added columns to a table, then for
            // the down method, we should remove them.
            case 'add':
                $fields = $this->constructSchema($schema, $meta, 'Drop');

                return $this->insert($fields)->into($this->getChangeSchemaWrapper());
                break;
            // If the user removed columns from a table, then for
            // the down method, we should add them back on.
            case 'remove':
                $fields = $this->constructSchema($schema, $meta);

                return $this->insert($fields)->into($this->getChangeSchemaWrapper());
                break;
            default:
                return sprintf("Schema::drop('%s');", $meta['table']);
                break;
        }

        // Otherwise, we have no idea how to proceed.
        throw new GeneratorException;
    }

    /**
     * Store the given template, to be inserted somewhere.
     *
     * @param  string $template
     * @return $this
     */
    private function insert($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get the stored template, and insert into the given wrapper.
     *
     * @param  string $wrapper
     * @param  string $placeholder
     * @return mixed
     */
    private function into($wrapper, $placeholder = 'schema_up')
    {
        return str_replace('{{' . $placeholder . '}}', $this->template, $wrapper);
    }

    /**
     * Get the wrapper template for a "create" action.
     *
     * @return string
     */
    private function getCreateSchemaWrapper()
    {
        return file_get_contents(__DIR__ . '/../stubs/schema-create.stub');
    }

    /**
     * Get the wrapper template for an "add" action.
     *
     * @return string
     */
    private function getChangeSchemaWrapper()
    {
        return file_get_contents(__DIR__ . '/../stubs/schema-change.stub');
    }

    /**
     * Construct the schema fields.
     *
     * @param  array $schema
     * @param  string $direction
     * @return array
     */
    private function constructSchema($schema, $meta, $direction = 'Add')
    {
        if (!$schema) {
            return '';
        }

        $fields = array_map(function ($field) use ($direction, $meta) {
            $method = "{$direction}Column";

            return $this->$method($field, $meta);
        }, $schema);

        return implode("\n" . str_repeat(' ', 12), $fields);
    }

    /**
     * Construct the syntax to add a column.
     *
     * @param  string $field
     * @return string
     */
    private function addColumn($field, $meta)
    {
        $syntax = sprintf("\$table->%s('%s')", $field['key'], $field['name']);

        // If there are arguments for the schema type, like decimal('amount', 5, 2)
        // then we have to remember to work those in.
        if ($field['arguments']) {
            $syntax = substr($syntax, 0, -1) . ', ';

            $syntax .= implode(', ', $field['arguments']) . ')';
        }

        foreach ($field['options'] as $method => $value) {
            if (isset($meta['type']) && $meta['type'] == 0) {
                if ($value) {
                    $syntax .= sprintf("->%s(%s)", $method, $value === true ? '' : $value);
                }
            } else {
                $syntax .= sprintf("->%s(%s)", $method, $value === true ? '' : $value);
            }

        }

        return $syntax .= ';';
    }

    /**
     * Construct the syntax to drop a column.
     *
     * @param  string $field
     * @return string
     */
    private function dropColumn($field, $meta)
    {
        return sprintf("\$table->dropColumn('%s');", $field['name']);
    }
}
