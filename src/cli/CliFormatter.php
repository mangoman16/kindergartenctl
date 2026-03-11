<?php
declare(strict_types=1);

/**
 * CliFormatter - Terminal output helpers for the CLI tool.
 */
class CliFormatter
{
    private bool $colors;

    public function __construct()
    {
        // Detect color support
        $this->colors = (getenv('NO_COLOR') === false)
            && (PHP_SAPI === 'cli')
            && (getenv('TERM') !== 'dumb')
            && (function_exists('posix_isatty') ? posix_isatty(STDOUT) : true);
    }

    // ------------------------------------------------------------------
    // Text helpers
    // ------------------------------------------------------------------

    public function success(string $message): void
    {
        $this->writeln($this->green('  OK  ') . ' ' . $message);
    }

    public function error(string $message): void
    {
        $this->writeln($this->red(' ERROR ') . ' ' . $message);
    }

    public function warn(string $message): void
    {
        $this->writeln($this->yellow(' WARN ') . ' ' . $message);
    }

    public function info(string $message): void
    {
        $this->writeln($this->cyan(' INFO ') . ' ' . $message);
    }

    public function line(string $message = ''): void
    {
        $this->writeln($message);
    }

    public function newline(): void
    {
        echo PHP_EOL;
    }

    // ------------------------------------------------------------------
    // Table rendering
    // ------------------------------------------------------------------

    /**
     * Render an ASCII table.
     *
     * @param string[] $headers Column headers
     * @param array[]  $rows    Array of associative or indexed arrays
     */
    public function table(array $headers, array $rows): void
    {
        if (empty($rows)) {
            $this->warn('No results.');
            return;
        }

        // Calculate column widths
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = mb_strlen($header);
        }
        foreach ($rows as $row) {
            $values = array_values($row);
            foreach ($values as $i => $value) {
                $len = mb_strlen((string)$value);
                $widths[$i] = max($widths[$i] ?? 0, $len);
            }
        }

        // Cap column widths at 60
        foreach ($widths as $i => $w) {
            $widths[$i] = min($w, 60);
        }

        // Build separator
        $sep = '+';
        foreach ($widths as $w) {
            $sep .= str_repeat('-', $w + 2) . '+';
        }

        // Print header
        $this->writeln($sep);
        $headerLine = '|';
        foreach ($headers as $i => $header) {
            $headerLine .= ' ' . $this->bold($this->pad($header, $widths[$i])) . ' |';
        }
        $this->writeln($headerLine);
        $this->writeln($sep);

        // Print rows
        foreach ($rows as $row) {
            $values = array_values($row);
            $rowLine = '|';
            foreach ($values as $i => $value) {
                $display = mb_substr((string)$value, 0, $widths[$i]);
                $rowLine .= ' ' . $this->pad($display, $widths[$i]) . ' |';
            }
            $this->writeln($rowLine);
        }
        $this->writeln($sep);

        $this->writeln(count($rows) . ' row(s)');
    }

    // ------------------------------------------------------------------
    // Detail view (key-value pairs)
    // ------------------------------------------------------------------

    /**
     * Display a record as key-value pairs.
     */
    public function detail(array $data, array $labels = []): void
    {
        $maxKey = 0;
        foreach ($data as $key => $value) {
            $label = $labels[$key] ?? $key;
            $maxKey = max($maxKey, mb_strlen($label));
        }

        foreach ($data as $key => $value) {
            $label = $labels[$key] ?? $key;
            $display = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
            $this->writeln('  ' . $this->bold($this->pad($label, $maxKey)) . '  ' . $display);
        }
    }

    // ------------------------------------------------------------------
    // Prompts
    // ------------------------------------------------------------------

    /**
     * Ask for y/n confirmation.
     */
    public function confirm(string $question, bool $default = false): bool
    {
        $hint = $default ? '[Y/n]' : '[y/N]';
        echo $question . ' ' . $hint . ' ';
        $answer = trim((string)fgets(STDIN));

        if ($answer === '') {
            return $default;
        }

        return strtolower($answer[0]) === 'y';
    }

    // ------------------------------------------------------------------
    // Validation errors display
    // ------------------------------------------------------------------

    public function validationErrors(array $errors): void
    {
        $this->error('Validation failed:');
        foreach ($errors as $field => $messages) {
            foreach ((array)$messages as $msg) {
                $this->writeln('  ' . $this->yellow($field) . ': ' . $msg);
            }
        }
    }

    // ------------------------------------------------------------------
    // ServiceResult display
    // ------------------------------------------------------------------

    /**
     * Print the outcome of a ServiceResult.
     */
    public function result(ServiceResult $result): void
    {
        if ($result->succeeded()) {
            $this->success($result->message ?? 'Done.');
        } else {
            if (!empty($result->errors)) {
                $this->validationErrors($result->errors);
            }
            if ($result->message) {
                $this->error($result->message);
            }
        }
    }

    // ------------------------------------------------------------------
    // Color helpers
    // ------------------------------------------------------------------

    public function green(string $text): string
    {
        return $this->colors ? "\033[42;30m{$text}\033[0m" : $text;
    }

    public function red(string $text): string
    {
        return $this->colors ? "\033[41;37m{$text}\033[0m" : $text;
    }

    public function yellow(string $text): string
    {
        return $this->colors ? "\033[33m{$text}\033[0m" : $text;
    }

    public function cyan(string $text): string
    {
        return $this->colors ? "\033[36m{$text}\033[0m" : $text;
    }

    public function bold(string $text): string
    {
        return $this->colors ? "\033[1m{$text}\033[0m" : $text;
    }

    // ------------------------------------------------------------------
    // Internal
    // ------------------------------------------------------------------

    private function pad(string $text, int $width): string
    {
        $pad = $width - mb_strlen($text);
        return $text . ($pad > 0 ? str_repeat(' ', $pad) : '');
    }

    private function writeln(string $text): void
    {
        echo $text . PHP_EOL;
    }
}
