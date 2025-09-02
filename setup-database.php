<?php
// Database setup script for MediCare Store
echo "Setting up MediCare Store Database...\n\n";

try {
    // Connect to MySQL (without specifying database)
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    $sql = file_get_contents('database/medicine_store.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read database/medicine_store.sql file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "✅ Database 'medicine_store' created successfully!\n";
    echo "✅ Tables created successfully!\n";
    echo "✅ Sample data inserted successfully!\n\n";
    
    echo "Demo accounts created:\n";
    echo "- User: john_doe / user123\n";
    echo "- Admin: admin / admin123\n\n";
    
    echo "You can now start the server using: php start-server.php\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\nPlease make sure:\n";
    echo "1. MySQL/MariaDB is running\n";
    echo "2. You have proper permissions\n";
    echo "3. The database connection settings are correct\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>