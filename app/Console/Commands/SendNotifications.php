<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class SendNotifications extends Command
{
    protected $signature = 'notifications:send';
    protected $description = 'Send all pending notifications (stock, invoices, etc)';

    public function handle()
    {
        $this->info('Sending notifications...');
        
        NotificationService::sendAllNotifications();
        
        $this->info('Notifications sent successfully!');
        return 0;
    }
}
