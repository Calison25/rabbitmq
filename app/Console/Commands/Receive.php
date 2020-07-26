<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Receive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('hello', false, true, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            sleep(substr_count($msg->body, '.'));
            echo " [x] Done\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_consume('task_queue', '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }
}
