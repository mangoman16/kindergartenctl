<?php
declare(strict_types=1);

class CalendarCommand
{
    private CliFormatter $fmt;

    public function __construct(CliFormatter $fmt)
    {
        $this->fmt = $fmt;
    }

    public function list(array $parsed): void
    {
        $options = $parsed['options'];
        $start = (string) ($options['start'] ?? date('Y-m-01'));
        $end = (string) ($options['end'] ?? date('Y-m-t'));

        $result = (new CalendarService())->getEvents($start, $end);

        if ($result->failed()) {
            $this->fmt->result($result);
            return;
        }

        $rows = [];
        foreach ($result->data['events'] as $event) {
            $rows[] = [
                'ID'    => $event['id'] ?? '',
                'Title' => $event['title'] ?? '',
                'Start' => $event['start'] ?? '',
                'End'   => $event['end'] ?? '-',
                'Color' => $event['color'] ?? '-',
            ];
        }

        $this->fmt->table(['ID', 'Title', 'Start', 'End', 'Color'], $rows);
    }

    public function create(array $parsed): void
    {
        $options = $parsed['options'];

        if (empty($options['title'])) {
            $this->fmt->error('The --title option is required.');
            return;
        }
        if (empty($options['start'])) {
            $this->fmt->error('The --start option is required (YYYY-MM-DD).');
            return;
        }

        $data = [
            'title'      => (string) $options['title'],
            'start_date' => (string) $options['start'],
        ];

        if (isset($options['end'])) $data['end_date'] = (string) $options['end'];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];
        if (isset($options['color'])) $data['color'] = (string) $options['color'];
        if (isset($options['all-day'])) $data['all_day'] = 1;

        $result = (new CalendarService())->create($data);
        $this->fmt->result($result);

        if ($result->succeeded() && isset($result->data['id'])) {
            $this->fmt->info('New event ID: ' . $result->data['id']);
        }
    }

    public function update(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid event ID.');
            return;
        }

        $options = $parsed['options'];
        $data = [];
        if (isset($options['title'])) $data['title'] = (string) $options['title'];
        if (isset($options['start'])) $data['start_date'] = (string) $options['start'];
        if (isset($options['end'])) $data['end_date'] = (string) $options['end'];
        if (isset($options['description'])) $data['description'] = (string) $options['description'];
        if (isset($options['color'])) $data['color'] = (string) $options['color'];

        if (empty($data)) {
            $this->fmt->warn('No fields to update. Use --title, --start, --end, --color.');
            return;
        }

        $result = (new CalendarService())->update($id, $data);
        $this->fmt->result($result);
    }

    public function delete(array $parsed): void
    {
        $id = (int) ($parsed['args'][0] ?? 0);
        if ($id <= 0) {
            $this->fmt->error('Please provide a valid event ID.');
            return;
        }

        if (empty($parsed['options']['force'])) {
            if (!$this->fmt->confirm('Are you sure you want to delete event #' . $id . '?')) {
                $this->fmt->info('Cancelled.');
                return;
            }
        }

        $result = (new CalendarService())->delete($id);
        $this->fmt->result($result);
    }
}
