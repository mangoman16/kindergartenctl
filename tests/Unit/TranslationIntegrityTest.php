<?php
/**
 * Translation Integrity Tests
 *
 * Guards against two recurring bug classes in the language files:
 *  - Duplicate array keys (PHP silently keeps the last, so an earlier value is
 *    lost without any warning — this has regressed before, e.g. auth.logout).
 *  - Drift between the German and English files (a key in one but not the other
 *    surfaces as a raw key string to users).
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TranslationIntegrityTest extends TestCase
{
    private function langFile(string $name): string
    {
        return SRC_PATH . '/lang/' . $name . '.php';
    }

    /**
     * Extract the top-level keys from the raw source text. include() would
     * silently collapse duplicates, so the file must be scanned line by line to
     * detect them.
     *
     * @return string[]
     */
    private function rawKeys(string $file): array
    {
        $keys = [];
        foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match("/^\s*'([^']+)'\s*=>/", $line, $m)) {
                $keys[] = $m[1];
            }
        }
        return $keys;
    }

    /**
     * @return string[]
     */
    private function duplicates(array $keys): array
    {
        return array_keys(array_filter(array_count_values($keys), static fn ($c) => $c > 1));
    }

    public function testGermanHasNoDuplicateKeys(): void
    {
        $dupes = $this->duplicates($this->rawKeys($this->langFile('de')));
        $this->assertSame([], $dupes, 'Duplicate keys in de.php silently overwrite earlier values: ' . implode(', ', $dupes));
    }

    public function testEnglishHasNoDuplicateKeys(): void
    {
        $dupes = $this->duplicates($this->rawKeys($this->langFile('en')));
        $this->assertSame([], $dupes, 'Duplicate keys in en.php silently overwrite earlier values: ' . implode(', ', $dupes));
    }

    public function testGermanAndEnglishHaveIdenticalKeys(): void
    {
        $de = include $this->langFile('de');
        $en = include $this->langFile('en');

        $missingInEn = array_keys(array_diff_key($de, $en));
        $missingInDe = array_keys(array_diff_key($en, $de));

        $this->assertSame([], $missingInEn, 'Keys present in de.php but missing from en.php: ' . implode(', ', $missingInEn));
        $this->assertSame([], $missingInDe, 'Keys present in en.php but missing from de.php: ' . implode(', ', $missingInDe));
    }
}
