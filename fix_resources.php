<?php
$files = glob('app/Filament/Admin/Resources/*/*Resource.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Pattern to remove "public static function canAccess(): bool { ... }"
    $pattern = '/\s*public static function can(Access|ViewAny|Create|Edit|Delete)(?:\([^)]*\))?\s*:\s*bool\s*\{[^}]+\}/m';
    
    $newContent = preg_replace($pattern, '', $content);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Cleaned up $file\n";
    }
}
