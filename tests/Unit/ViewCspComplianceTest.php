<?php
/**
 * View CSP Compliance Tests
 *
 * The application serves a strict nonce-based Content-Security-Policy with no
 * 'unsafe-inline' (see public/index.php). Under that policy the browser silently
 * strips inline event-handler attributes (onclick, onchange, onsubmit, ...),
 * which previously broke delete confirmations, list filters, bulk actions and
 * modal controls across the app. These tests fail the build if any view
 * reintroduces an inline handler, or reads the CSRF token from the wrong session
 * key (the cause of the bulk-action 403s).
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ViewCspComplianceTest extends TestCase
{
    /**
     * @return string[] Absolute paths of every view template.
     */
    private function viewFiles(): array
    {
        $files = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(SRC_PATH . '/views', RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        sort($files);
        return $files;
    }

    public function testNoInlineEventHandlersInViews(): void
    {
        // Matches HTML attribute handlers (quoted value) but not JS property
        // assignments like el.onclick = fn (no leading whitespace before "on").
        $pattern = '/\son(?:click|change|submit|input|keyup|keydown|keypress|load|error|focus|blur|mouseover|mouseout|mousedown|mouseup|reset|select)\s*=\s*["\']/i';

        $offenders = [];
        foreach ($this->viewFiles() as $file) {
            $contents = file_get_contents($file);
            if (preg_match_all($pattern, $contents, $m) > 0) {
                $offenders[] = basename(dirname($file)) . '/' . basename($file) . ' (' . count($m[0]) . ')';
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Inline on*= handlers are blocked by the CSP and must be wired via " .
            "addEventListener / data-* hooks instead. Offending views:\n  " . implode("\n  ", $offenders)
        );
    }

    public function testNoViewReadsCsrfTokenFromWrongSessionKey(): void
    {
        // The token is stored under _csrf_token; Session::get('csrf_token')
        // returns null. Views must use $csrfToken / Session::csrfToken().
        $offenders = [];
        foreach ($this->viewFiles() as $file) {
            $contents = file_get_contents($file);
            if (str_contains($contents, "Session::get('csrf_token')")
                || str_contains($contents, 'Session::getCsrfToken(')) {
                $offenders[] = basename(dirname($file)) . '/' . basename($file);
            }
        }

        $this->assertSame([], $offenders, 'Views reading the CSRF token from the wrong key: ' . implode(', ', $offenders));
    }
}
