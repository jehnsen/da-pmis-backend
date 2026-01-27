<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Project $project;

    private float $utilizationRate;

    private float $totalBudget;

    private float $totalDisbursed;

    private string $alertType;

    public function __construct(
        Project $project,
        float $utilizationRate,
        float $totalBudget,
        float $totalDisbursed,
        string $alertType = 'threshold'
    ) {
        $this->project = $project;
        $this->utilizationRate = $utilizationRate;
        $this->totalBudget = $totalBudget;
        $this->totalDisbursed = $totalDisbursed;
        $this->alertType = $alertType;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $remaining = $this->totalBudget - $this->totalDisbursed;

        $alertMessage = match ($this->alertType) {
            'exceeded' => 'Budget has been exceeded!',
            'critical' => 'Budget utilization has reached a critical level (90%+)',
            'warning' => 'Budget utilization is approaching threshold (75%+)',
            default => 'Budget threshold alert',
        };

        $urgency = match ($this->alertType) {
            'exceeded' => 'URGENT: ',
            'critical' => 'CRITICAL: ',
            'warning' => 'WARNING: ',
            default => '',
        };

        return (new MailMessage)
            ->subject($urgency.'Budget Alert for '.$this->project->title)
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->username))
            ->line($alertMessage)
            ->line('')
            ->line('**Budget Details:**')
            ->line('Project: '.$this->project->title)
            ->line('Total Budget: ₱'.number_format($this->totalBudget, 2))
            ->line('Total Disbursed: ₱'.number_format($this->totalDisbursed, 2))
            ->line('Remaining: ₱'.number_format($remaining, 2))
            ->line('Utilization Rate: '.round($this->utilizationRate, 1).'%')
            ->line('')
            ->action('View Project Financials', url('/projects/'.$this->project->id.'/financials'))
            ->line('Please review and take appropriate action.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'budget_alert',
            'alert_type' => $this->alertType,
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'utilization_rate' => $this->utilizationRate,
            'total_budget' => $this->totalBudget,
            'total_disbursed' => $this->totalDisbursed,
            'remaining' => $this->totalBudget - $this->totalDisbursed,
            'message' => 'Budget alert for "'.$this->project->title.'": '.round($this->utilizationRate, 1).'% utilized',
        ];
    }
}
