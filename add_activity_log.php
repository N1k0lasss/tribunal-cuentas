<?php

$models = glob('app/Models/*.php');

foreach ($models as $file) {
    $content = file_get_contents($file);
    
    if (strpos($content, 'LogsActivity') !== false) {
        continue;
    }

    // Add imports
    $content = preg_replace('/namespace App\\\\Models;/', "namespace App\\Models;\n\nuse Spatie\\Activitylog\\Traits\\LogsActivity;\nuse Spatie\\Activitylog\\LogOptions;", $content);

    // Add trait inside class
    $content = preg_replace('/(class [a-zA-Z0-9_]+ extends [^{]+\{)/', "$1\n    use LogsActivity;", $content);

    // Add getActivitylogOptions method
    $method = <<<METHOD

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
METHOD;

    // insert before last closing brace
    $content = preg_replace('/}(?=\s*$)/', $method . "\n}\n", $content);

    file_put_contents($file, $content);
    echo "Updated $file\n";
}
