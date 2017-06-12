<?php
/**
 * Helper methods to query Postgresql's information_schema
 */

namespace Dcp\Core;

class PgInformationSchema
{
    /**
     * Check if a table exists.
     *
     * @param string $dbaccess dbaccess
     * @param string $schemaName Schema name (e.g. 'public')
     * @param string $tableName Table name (e.g. 'doc123')
     * @return bool bool(true) if table exists in given schema or bool(false) if table does not exists
     */
    public static function tableExists($dbaccess, $schemaName, $tableName)
    {
        $tpl_sql =
        /** @lang text */
        <<<'EOF'
SELECT
    true
FROM
    information_schema.tables
WHERE
    table_schema = %s
    AND table_name = %s
LIMIT 1
EOF;
        $q = sprintf($tpl_sql, pg_escape_literal($schemaName) , pg_escape_literal($tableName));
        simpleQuery($dbaccess, $q, $res, true, true, true);
        return ($res === 't');
    }
    /**
     * Get columns for a given table.
     *
     * @param string $dbaccess dbaccess
     * @param string $schemaName Schema name (e.g. 'public')
     * @param string $tableName Table name (e.g. 'doc123')
     * @return string[] List of column names for given table
     */
    public static function getTableColumns($dbaccess, $schemaName, $tableName)
    {
        $tpl_sql =
        /** @lang text */
        <<<'EOF'
SELECT
    column_name
FROM
    information_schema.columns
WHERE
    table_schema = %s
    AND table_name = %s
EOF;
        $q = sprintf($tpl_sql, pg_escape_literal($schemaName) , pg_escape_literal($tableName));
        simpleQuery($dbaccess, $q, $res, true, false, true);
        return $res;
    }
    /**
     * Get indexes for a given table.
     *
     * @param string $dbaccess dbaccess
     * @param string $schemaName Schema name (e.g. 'public')
     * @param string $tableName Table name (e.g. 'doc123')
     * @return string[] List of index names for given table
     */
    public static function getTableIndexes($dbaccess, $schemaName, $tableName)
    {
        $tpl_sql =
        /** @lang text */
        <<<'EOF'
SELECT
    ci.relname AS index_name
FROM
    pg_class AS ct,
    pg_class AS ci,
    pg_namespace AS ns,
    pg_index AS ix
WHERE
    ns.nspname = %s
    AND ct.relname = %s
    AND ct.relkind = 'r'
    AND ct.relnamespace = ns.oid
    AND ci.relnamespace = ns.oid
    AND ct.oid = ix.indrelid
    AND ci.oid = ix.indexrelid
EOF;
        $q = sprintf($tpl_sql, pg_escape_literal($schemaName) , pg_escape_literal($tableName));
        simpleQuery($dbaccess, $q, $res, true, false, true);
        return $res;
    }
}
