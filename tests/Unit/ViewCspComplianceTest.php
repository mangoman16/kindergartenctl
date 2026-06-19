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

    /**
     * style="..." attributes are stripped by the CSP (style-src has no
     * 'unsafe-inline'). The migration of every view off inline styles
     * (utility classes / nonce'd <style> / data-bg+data-fg) is in progress;
     * this is a ratchet — the count must never rise above the baseline, which
     * is lowered as files are converted. Target is 0, at which point this
     * becomes a hard "=== 0" assertion. Email templates are exempt (mail
     * clients require inline styles and the CSP does not apply to them).
     */
    public function testInlineStyleAttributesDoNotIncrease(): void
    {
        $baseline = 104;

        $count = 0;
        $perFile = [];
        foreach ($this->viewFiles() as $file) {
            if (str_contains($file, '/emails/')) {
                continue;
            }
            $n = preg_match_all('/\sstyle\s*=\s*["\']/i', file_get_contents($file));
            if ($n > 0) {
                $count += $n;
                $perFile[] = basename(dirname($file)) . '/' . basename($file) . " ({$n})";
            }
        }

        $this->assertLessThanOrEqual(
            $baseline,
            $count,
            "Inline style attributes rose to {$count} (baseline {$baseline}). New inline " .
            "styles are CSP-stripped — use stylesheet classes, a nonce'd <style> block, or " .
            "data-bg/data-fg + App.applyDataStyles(). Files:\n  " . implode("\n  ", $perFile)
        );
    }

    public function testNoInlineStyleAttributesInJsTemplates(): void
    {
        // JS-built markup (innerHTML/template literals) is parsed as HTML, so
        // style attributes inside it are CSP-stripped too. Styling elements
        // via the CSSOM (el.style.x = ...) is fine and not matched here.
        $offenders = [];
        foreach (glob(ROOT_PATH . '/public/assets/js/*.js') as $file) {
            $contents = file_get_contents($file);
            if (($count = preg_match_all('/\sstyle\s*=\s*\\\\?["\']/i', $contents)) > 0) {
                $offenders[] = basename($file) . ' (' . $count . ')';
            }
        }

        $this->assertSame([], $offenders, 'style= attributes in JS-built markup are CSP-stripped: ' . implode(', ', $offenders));
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
