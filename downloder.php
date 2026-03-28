<?php
class DatabaseCSVExporter {
    private $host;
    private $username;
    private $password;
    private $database;
    private $table;
    private $columns;

    /**
     * Constructor to set database connection details
     * 
     * @param string $host Database host
     * @param string $username Database username
     * @param string $password Database password
     * @param string $database Database name
     * @param string $table Table to export
     * @param array $columns Optional. Columns to export (if empty, exports all columns)
     */
    public function __construct($host, $username, $password, $database, $table, $columns = []) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->table = $table;
        $this->columns = $columns;
    }

    /**
     * Export table to CSV
     * 
     * @return bool True if export successful, false otherwise
     */
    public function exportToCSV() {
        // Validate inputs
        if (empty($this->table)) {
            throw new Exception("Table name is required");
        }

        // Establish database connection
        $conn = new mysqli($this->host, $this->username, $this->password, $this->database);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Prepare column list
        $columnQuery = "*";
        if (!empty($this->columns)) {
            // Sanitize column names to prevent SQL injection
            $sanitizedColumns = array_map(function($col) use ($conn) {
                return $conn->real_escape_string(trim($col));
            }, $this->columns);
            $columnQuery = implode(", ", $sanitizedColumns);
        }

        // Prepare SQL query
        $query = "SELECT $columnQuery FROM `" . $conn->real_escape_string($this->table) . "` where credit_score=400";

        // Execute query
        $result = $conn->query($query);

        if (!$result) {
            $conn->close();
            throw new Exception("Error executing query: " . $conn->error);
        }

        // Set headers for file download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $this->table . '_export_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');

        // Write header row
        $headerRow = $result->fetch_fields();
        $headers = array_column($headerRow, 'name');
        fputcsv($output, $headers);

        // Write data rows
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        // Close connections
        fclose($output);
        $result->free();
        $conn->close();

        return true;
    }
}

// Example usage
try {
    // Replace these with your actual database credentials
    $exporter = new DatabaseCSVExporter(
        'localhost',     // Host
        'root',      // Username
        '',      // Password
        'fintracms', // Database name
        'loan_applications',    // Table name
        ['name', 'mobile', 'pan', 'dob']  // Optional: Specific columns to export
    );

    $exporter->exportToCSV();
} catch (Exception $e) {
    // Handle any errors
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
    exit;
}
