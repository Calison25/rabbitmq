<?php

namespace App\Console\Commands\Others;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Exchange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:exchange {message} {topic=anonymous.info}';

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

        $channel->exchange_declare('topic_logs', 'topic', false, false, false);
        $data = $this->argument('message');
        if (empty($data)) {
            $data = "Hello World!";
        }

        $msg = new AMQPMessage(
            $data,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        $rounting_key = $this->argument('topic');
        $channel->basic_publish($msg, 'topic_logs', $rounting_key);

        echo " [x] Sent $data" . PHP_EOL;

        $channel->close();
        $connection->close();
    }
}
