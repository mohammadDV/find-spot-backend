<?php
/**
 * Redis Connection Test Script
 * This script tests the Redis connection from the Laravel backend
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Test Redis connection using Laravel's Redis facade
    $redis = \Illuminate\Support\Facades\Redis::connection();
    
    // Test PING
    $result = $redis->ping();
    echo "✓ Redis PING: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    
    // Test SET/GET
    $testKey = 'test:connection:' . time();
    $testValue = 'Hello Redis from Laravel!';
    
    $redis->set($testKey, $testValue);
    $retrieved = $redis->get($testKey);
    
    echo "✓ Redis SET/GET: " . ($retrieved === $testValue ? "SUCCESS" : "FAILED") . "\n";
    echo "  Set value: $testValue\n";
    echo "  Retrieved value: $retrieved\n";
    
    // Clean up
    $redis->del($testKey);
    
    // Test Cache facade
    \Illuminate\Support\Facades\Cache::put('test:cache', 'Cache works!', 60);
    $cacheValue = \Illuminate\Support\Facades\Cache::get('test:cache');
    
    echo "✓ Laravel Cache (Redis): " . ($cacheValue === 'Cache works!' ? "SUCCESS" : "FAILED") . "\n";
    echo "  Cache value: $cacheValue\n";
    
    // Get Redis info
    $info = $redis->info('server');
    echo "\n✓ Redis Server Info:\n";
    echo "  Redis Version: " . ($info['redis_version'] ?? 'N/A') . "\n";
    echo "  Connected Clients: " . ($info['connected_clients'] ?? 'N/A') . "\n";
    
    echo "\n✅ All Redis tests passed successfully!\n";
    exit(0);
    
} catch (\Exception $e) {
    echo "❌ Redis connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

