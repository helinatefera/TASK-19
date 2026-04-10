<?php

namespace App\Services\Notification;

use App\Models\BusinessParameter;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationService
{
    public function dispatch(User $recipient, string $templateKey, array $data = []): ?Notification
    {
        $locale = $recipient->locale ?? config('app.locale', 'en');
        $timezone = $recipient->timezone ?? config('app.timezone', 'UTC');

        $template = NotificationTemplate::where('key', $templateKey)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first()
            ?? NotificationTemplate::where('key', $templateKey)
                ->where('locale', config('app.fallback_locale', 'en'))
                ->where('is_active', true)
                ->first()
            ?? NotificationTemplate::where('key', $templateKey)
                ->where('is_active', true)
                ->first();

        if (! $template) {
            return null;
        }

        $title = $this->renderTemplate($template->title_template, $data, $timezone);
        $body = $this->renderTemplate($template->body_template, $data, $timezone);

        $retentionDays = (int) (BusinessParameter::where('key', 'notification_retention_days')->first()?->getTypedValue() ?? 90);

        return Notification::create([
            'id' => (string) Str::uuid(),
            'user_id' => $recipient->id,
            'template_id' => $template->id,
            'type' => $template->type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'rendered_locale' => $locale,
            'rendered_timezone' => $timezone,
            'expires_at' => now()->addDays($retentionDays),
        ]);
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->update([
            'read_at' => now(),
        ]);
    }

    public function pruneExpired(): int
    {
        return Notification::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();
    }

    private function renderTemplate(string $template, array $data, string $timezone = 'UTC'): string
    {
        $rendered = $template;

        foreach ($data as $key => $value) {
            if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
                $value = \Carbon\Carbon::parse($value)->timezone($timezone)->format('M j, Y g:i A T');
            }
            if (is_string($value) || is_numeric($value)) {
                $rendered = str_replace('{{' . $key . '}}', (string) $value, $rendered);
            }
        }

        return $rendered;
    }
}
