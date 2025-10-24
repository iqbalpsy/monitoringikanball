<?php
/**
 * Check sensor_data table structure and fix issues
 */

echo "🔧 Checking sensor_data Table Structure\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=monitoringikan', 'root', '');
    
    // Check table structure
    $stmt = $pdo->query('DESCRIBE sensor_data');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Current table structure:\n";
    echo str_repeat('-', 60) . "\n";
    printf("%-15s | %-15s | %-8s | %-10s\n", 'Field', 'Type', 'Null', 'Default');
    echo str_repeat('-', 60) . "\n";
    
    $hasVoltage = false;
    foreach ($columns as $column) {
        printf("%-15s | %-15s | %-8s | %-10s\n",
               $column['Field'],
               $column['Type'], 
               $column['Null'],
               $column['Default'] ?? 'NULL'
        );
        
        if ($column['Field'] == 'voltage') {
            $hasVoltage = true;
        }
    }
    echo str_repeat('-', 60) . "\n\n";
    
    // Check if voltage column exists
    if (!$hasVoltage) {
        echo "⚠️  MASALAH DITEMUKAN: Column 'voltage' tidak ada!\n";
        echo "🔧 Menambahkan column voltage...\n";
        
        $pdo->exec("ALTER TABLE sensor_data ADD COLUMN voltage DECIMAL(4,2) NULL AFTER oxygen");
        
        echo "✅ Column voltage berhasil ditambahkan!\n\n";
        
        // Show updated structure
        $stmt = $pdo->query('DESCRIBE sensor_data');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 Updated table structure:\n";
        echo str_repeat('-', 60) . "\n";
        printf("%-15s | %-15s | %-8s | %-10s\n", 'Field', 'Type', 'Null', 'Default');
        echo str_repeat('-', 60) . "\n";
        
        foreach ($columns as $column) {
            printf("%-15s | %-15s | %-8s | %-10s\n",
                   $column['Field'],
                   $column['Type'], 
                   $column['Null'],
                   $column['Default'] ?? 'NULL'
            );
        }
        echo str_repeat('-', 60) . "\n";
        
    } else {
        echo "✅ Column voltage sudah ada!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n🔧 Table Structure Check Complete\n";
?>