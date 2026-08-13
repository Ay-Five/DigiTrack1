<?php
/**
 * Database.php
 *
 * OOP PILLAR: ENCAPSULATION
 * --------------------------
 * The raw mysqli connection ($conn) is private — nothing outside this class
 * can touch it directly.  All callers go through the public methods query()
 * and escape(), hiding the implementation details behind a clean interface.
 */
class Database
{
    /** @var mysqli */
    private mysqli $conn;

    /**
     * Open the database connection.
     * Throws a RuntimeException if the connection fails so callers never
     * receive a broken object.
     */
    public function __construct(string $host, string $user, string $pass, string $dbname)
    {
        $this->conn = new mysqli($host, $user, $pass, $dbname);
        if ($this->conn->connect_error) {
            throw new \RuntimeException('DB connection failed: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8mb4');
    }

    /** Run any SQL and return the mysqli result (or true/false for writes). */
    public function query(string $sql): mixed
    {
        return $this->conn->query($sql);
    }

    /** Safely escape a value before embedding it in a query string. */
    public function escape(string $value): string
    {
        return $this->conn->real_escape_string($value);
    }

    /**
     * Shortcut: run a SELECT and return a single associative row.
     * Returns null when no rows match.
     */
    public function row(string $sql): ?array
    {
        $result = $this->conn->query($sql);
        return ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    /**
     * Shortcut: run a SELECT and return every row as an array of arrays.
     * Useful when you need all rows at once (e.g. chart data).
     */
    public function rows(string $sql): array
    {
        $result = $this->conn->query($sql);
        $out = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $out[] = $row;
            }
        }
        return $out;
    }

    /**
     * Shortcut: run a COUNT query and return the integer result.
     * e.g. $db->count("SELECT COUNT(*) AS c FROM students")
     */
    public function count(string $sql): int
    {
        $row = $this->row($sql);
        return $row ? (int)($row['c'] ?? 0) : 0;
    }

    /**
     * Shortcut: run a scalar query and return the first column of the first row.
     * e.g. $db->scalar("SELECT ROUND(AVG(rating),1) AS r FROM ...")
     */
    public function scalar(string $sql, string $col): mixed
    {
        $row = $this->row($sql);
        return $row[$col] ?? null;
    }
}
