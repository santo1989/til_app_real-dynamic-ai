<?php
require 'vendor/autoload.php';

try {
    // Load Laravel
    $app = require_once(__DIR__ . '/bootstrap/app.php');
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    echo "=== APPLICATION HEALTH CHECK ===\n\n";

    // Check database connection
    DB::connection()->getPdo();
    echo "✓ Database Connection: SUCCESS\n";

    // Check users
    $userCount = App\Models\User::count();
    echo "✓ Total Users: " . $userCount . "\n";

    // Check financial years
    $fyCount = App\Models\FinancialYear::count();
    echo "✓ Total Financial Years: " . $fyCount . "\n";

    $activeFY = App\Models\FinancialYear::getActiveName();
    echo "✓ Active FY: " . ($activeFY ? $activeFY : 'NONE') . "\n";

    // Check objectives
    $objCount = App\Models\Objective::count();
    echo "✓ Total Objectives: " . $objCount . "\n";

    // Check appraisals
    $appCount = App\Models\Appraisal::count();
    echo "✓ Total Appraisals: " . $appCount . "\n";

    // Check PIPs
    $pipCount = App\Models\Pip::count();
    echo "✓ Total PIPs: " . $pipCount . "\n";

    // Check IDPs
    $idpCount = App\Models\Idp::count();
    echo "✓ Total IDPs: " . $idpCount . "\n";

    // Test user roles exist
    echo "\n=== USER ROLES ===\n";
    $roles = App\Models\User::select('role')->distinct()->get();
    foreach ($roles as $r) {
        $count = App\Models\User::where('role', $r->role)->count();
        echo "  • " . $r->role . ": " . $count . "\n";
    }

    echo "\n✓ ALL SYSTEMS OPERATIONAL\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Location: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
