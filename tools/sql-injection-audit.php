<?php
/**
 * Portable SQL-injection footgun scanner for this Yii2/PHP repo.
 * Used by tools/sql-injection-audit.sh when ripgrep (rg) is not installed.
 *
 * Mirrors the rg checks in sql-injection-audit.sh (same allow marker).
 */

declare(strict_types=1);

const ALLOW_MARKER = 'sql-audit:allow';

/** @return iterable<string> */
function iterPhpFiles(string $root): iterable
{
    // Skip vendored/generated code and tooling that embeds the audit regexes themselves.
    $skipDirs = ['vendor', 'runtime', 'node_modules', '.git', 'tools'];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $root,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
        ),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    /** @var SplFileInfo $file */
    foreach ($it as $file) {
        if (!$file->isFile() || strcasecmp($file->getExtension(), 'php') !== 0) {
            continue;
        }
        $path = $file->getPathname();
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($skipDirs as $sd) {
            if (in_array($sd, $parts, true)) {
                continue 2;
            }
        }
        // Published web assets (if present) are not application code.
        if (str_contains($path . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR)) {
            continue;
        }

        yield $path;
    }
}

/**
 * @param string $path
 * @param string $re PCRE pattern including delimiters + flags
 * @return list<array{int,string}> pairs of (1-based line, full line text)
 */
function matchLines(string $path, string $re): array
{
    $raw = @file_get_contents($path);
    if ($raw === false) {
        fwrite(STDERR, "warning: could not read: {$path}\n");
        return [];
    }

    // Normalize newlines for stable offsets.
    $text = str_replace(["\r\n", "\r"], "\n", $raw);
    $lines = explode("\n", $text);

    $out = [];
    foreach ($lines as $idx => $line) {
        $lineNo = $idx + 1;
        if (str_contains($line, ALLOW_MARKER)) {
            continue;
        }
        if (preg_match($re, $line) === 1) {
            $out[] = [$lineNo, $line];
        }
    }

    return $out;
}

/**
 * @param list<array{int,string}> $matches
 */
function printMatches(string $root, string $path, array $matches): void
{
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
    foreach ($matches as [$lineNo, $line]) {
        echo "{$rel}:{$lineNo}:{$line}\n";
    }
}

$root = realpath(dirname(__DIR__));
if ($root === false) {
    fwrite(STDERR, "error: could not resolve repository root\n");
    exit(2);
}

$checks = [
    'createCommand($var) / createCommand($sql)' => '/createCommand\(\s*\$/',
    'where/andWhere/orWhere with string conditions' => '/->(where|andWhere|orWhere)\(\s*["\']/',
    'SQL string literal concatenation ("SELECT..." .)' => '/["\x27](?:SELECT|INSERT|UPDATE|DELETE)\b[^"\x27]*["\x27]\s*\./i',
    // Only flag PHP string literals that contain BOTH a superglobal and a SQL keyword.
    'Superglobals inside quoted SQL-ish PHP strings' =>
        '/(?<q>["\'])[^"\']*\$_(?:GET|POST|REQUEST)\b[^"\']*(?:SELECT|INSERT|UPDATE|DELETE|FROM|WHERE)\b[^"\']*\k<q>/i',
];

$fail = 0;
foreach ($checks as $name => $re) {
    echo "==> {$name}\n";
    $any = false;
    foreach (iterPhpFiles($root) as $file) {
        $m = matchLines($file, $re);
        if ($m !== []) {
            $any = true;
            printMatches($root, $file, $m);
        }
    }

    if ($any) {
        echo "\nFAILED: {$name}\n";
        echo "Fix the matches above, or add '" . ALLOW_MARKER . "' on the same line if truly justified.\n\n";
        $fail = 1;
    } else {
        echo "OK: {$name}\n\n";
    }
}

if ($fail !== 0) {
    echo "NOTE: If a match is a false positive, add '" . ALLOW_MARKER . "' on that line.\n";
    exit(1);
}

echo "All SQL injection guardrail checks passed.\n";
