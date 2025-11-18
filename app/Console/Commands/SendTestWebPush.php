<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendWebPush;

class SendTestWebPush extends Command
{
    protected $signature = 'webpush:send-test {title=Test} {body="Hello from e-Mercerie"}';
    protected $description = 'Send a test WebPush notification to all subscriptions';

    public function handle()
    {
        $title = $this->argument('title');
        $body = $this->argument('body');

        $payload = ['title' => $title, 'body' => $body, 'icon' => '/images/default.png', 'url' => '/'];
        dispatch(new SendWebPush($payload));
        $this->info('Test webpush dispatched.');
        return 0;
    }
}
