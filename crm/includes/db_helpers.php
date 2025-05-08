<?php
/**
 * Database Helper Functions
 *
 * This file contains helper functions for database operations
 * that can be used across the application.
 */

/**
 * Check if a column exists in a table
 *
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @param string $column Column name
 * @return bool True if column exists, false otherwise
 */
function columnExists($conn, $table, $column) {
    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

/**
 * Check if a table exists in the database
 *
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @return bool True if table exists, false otherwise
 */
function tableExists($conn, $table) {
    $sql = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

/**
 * Check if a foreign key exists between two tables
 *
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @param string $column Column name
 * @param string $refTable Referenced table name
 * @param string $refColumn Referenced column name (usually 'id')
 * @return bool True if foreign key exists, false otherwise
 */
function foreignKeyExists($conn, $table, $column, $refTable, $refColumn = 'id') {
    $sql = "SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '$table'
            AND COLUMN_NAME = '$column'
            AND REFERENCED_TABLE_NAME = '$refTable'
            AND REFERENCED_COLUMN_NAME = '$refColumn'";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

/**
 * Create a directory if it doesn't exist
 *
 * @param string $dir Directory path
 * @param int $permissions Directory permissions (default: 0777)
 * @param bool $recursive Create parent directories if they don't exist (default: true)
 * @return bool True if directory exists or was created, false otherwise
 */
function createDirectoryIfNotExists($dir, $permissions = 0777, $recursive = true) {
    if (!file_exists($dir)) {
        return mkdir($dir, $permissions, $recursive);
    }
    return true;
}

/**
 * Get the absolute path to the documents directory
 *
 * @param bool $create Whether to create the directory if it doesn't exist
 * @return string The absolute path to the documents directory
 */
function getDocumentsDirectory($create = true) {
    // Use absolute paths to avoid file:// protocol issues
    $documents_dir = realpath(__DIR__ . '/../../documents/');
    if ($documents_dir === false) {
        // If the directory doesn't exist yet, get the parent directory and append /documents
        $parent_dir = realpath(__DIR__ . '/../..');
        $documents_dir = $parent_dir . DIRECTORY_SEPARATOR . 'documents';
    }

    // Ensure directory path ends with a directory separator
    if (substr($documents_dir, -1) !== DIRECTORY_SEPARATOR) {
        $documents_dir .= DIRECTORY_SEPARATOR;
    }

    // Create the directory if it doesn't exist and $create is true
    if ($create && !file_exists($documents_dir)) {
        $documents_parent_dir = dirname($documents_dir);
        if (!file_exists($documents_parent_dir)) {
            mkdir($documents_parent_dir, 0777, true);
        }
        mkdir($documents_dir, 0777, true);
    }

    return $documents_dir;
}

/**
 * Get the absolute path to the contracts directory
 *
 * @param bool $create Whether to create the directory if it doesn't exist
 * @return string The absolute path to the contracts directory
 */
function getContractsDirectory($create = true) {
    // Use absolute paths to avoid file:// protocol issues
    $contracts_dir = realpath(__DIR__ . '/../../contracts/');
    if ($contracts_dir === false) {
        // If the directory doesn't exist yet, get the parent directory and append /contracts
        $parent_dir = realpath(__DIR__ . '/../..');
        $contracts_dir = $parent_dir . DIRECTORY_SEPARATOR . 'contracts';
    }

    // Ensure directory path ends with a directory separator
    if (substr($contracts_dir, -1) !== DIRECTORY_SEPARATOR) {
        $contracts_dir .= DIRECTORY_SEPARATOR;
    }

    // Create the directory if it doesn't exist and $create is true
    if ($create && !file_exists($contracts_dir)) {
        $contracts_parent_dir = dirname($contracts_dir);
        if (!file_exists($contracts_parent_dir)) {
            mkdir($contracts_parent_dir, 0777, true);
        }
        mkdir($contracts_dir, 0777, true);
    }

    return $contracts_dir;
}

/**
 * Safe database query that checks for column existence before executing
 *
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @param string $column Column name
 * @param string $query SQL query to execute if column exists
 * @param array $params Parameters for prepared statement
 * @param string $types Types of parameters (e.g., 'i' for integer, 's' for string)
 * @return bool|mysqli_result Result of query if column exists, false otherwise
 */
function safeColumnQuery($conn, $table, $column, $query, $params = [], $types = '') {
    if (columnExists($conn, $table, $column)) {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                // Create references for bind_param
                $bind_params = array();
                $bind_params[] = &$types;
                foreach ($params as $key => $value) {
                    $params[$key] = $value; // Ensure the value is set
                    $bind_params[] = &$params[$key]; // Add a reference to this element
                }

                // Call bind_param with the array of references
                call_user_func_array(array($stmt, 'bind_param'), $bind_params);
            }
            $stmt->execute();
            return $stmt->get_result();
        }
    }
    return false;
}

/**
 * Get a dynamic SQL query that only includes columns that exist in the table
 *
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @param array $columns Array of column names to include in the query
 * @param string $baseQuery Base SQL query with placeholders for columns
 * @return string SQL query with only existing columns
 */
function getDynamicQuery($conn, $table, $columns, $baseQuery) {
    $existingColumns = [];
    foreach ($columns as $column) {
        if (columnExists($conn, $table, $column)) {
            $existingColumns[] = $column;
        }
    }

    return str_replace('{columns}', implode(', ', $existingColumns), $baseQuery);
}

/**
 * Build a dynamic UPDATE query that only includes columns that exist in the table
 *
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @param array $columnValues Associative array of column names and values
 * @param string $whereClause WHERE clause for the UPDATE query
 * @return array Array containing the SQL query, parameter types, and values
 */
function buildDynamicUpdateQuery($conn, $table, $columnValues, $whereClause) {
    $setClauses = [];
    $paramTypes = '';
    $paramValues = [];

    foreach ($columnValues as $column => $value) {
        if (columnExists($conn, $table, $column)) {
            $setClauses[] = "`$column` = ?";

            // Determine parameter type
            if (is_int($value)) {
                $paramTypes .= 'i';
            } elseif (is_float($value)) {
                $paramTypes .= 'd';
            } elseif (is_null($value)) {
                $paramTypes .= 's';
                $value = null;
            } else {
                $paramTypes .= 's';
            }

            $paramValues[] = $value;
        }
    }

    // Add WHERE clause parameters
    if (preg_match_all('/\?/', $whereClause, $matches)) {
        $whereParamCount = count($matches[0]);
        for ($i = 0; $i < $whereParamCount; $i++) {
            $paramTypes .= 'i'; // Assuming WHERE clause parameters are integers (usually IDs)
        }
    }

    $query = "UPDATE `$table` SET " . implode(', ', $setClauses) . " WHERE $whereClause";

    return [
        'query' => $query,
        'types' => $paramTypes,
        'values' => $paramValues
    ];
}

/**
 * Execute a dynamic UPDATE query with proper parameter binding
 *
 * @param mysqli $conn Database connection
 * @param string $table Table name
 * @param array $columnValues Associative array of column names and values
 * @param string $whereClause WHERE clause for the UPDATE query
 * @param array $whereParams Parameters for the WHERE clause
 * @return bool True if the query was successful, false otherwise
 */
function executeDynamicUpdate($conn, $table, $columnValues, $whereClause, $whereParams = []) {
    $queryData = buildDynamicUpdateQuery($conn, $table, $columnValues, $whereClause);

    // Prepare the statement
    $stmt = $conn->prepare($queryData['query']);
    if (!$stmt) {
        return false;
    }

    // Combine column values and where params
    $allParams = array_merge($queryData['values'], $whereParams);

    // Create references for bind_param
    $bindParams = [];
    $bindParams[] = &$queryData['types']; // Reference to types string

    // Create references to each parameter
    foreach ($allParams as $key => $value) {
        $allParams[$key] = $value; // Ensure the value is set
        $bindParams[] = &$allParams[$key]; // Add a reference to this element
    }

    // Call bind_param with the array of references
    call_user_func_array([$stmt, 'bind_param'], $bindParams);

    // Execute the statement
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
