<?php

namespace App\Console\Commands\TalkRabbitMq;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DirectPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:direct_publish {message}';

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
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $exchangeName = 'direct_teste';
        $exchangeType = 'direct';
        $queueName = 'direct_queue';
        $routingKey = 'direct_routing_key';
        $channel->exchange_declare($exchangeName, $exchangeType, false, true, false);
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind(
            $queueName,
            $exchangeName,
            $routingKey
        );

        $messageBody = $this->argument('message') ?? 'Hello World!';

        $message = new AMQPMessage(
            $messageBody,
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $channel->basic_publish($message, $exchangeName, $routingKey);

        echo " [x] Sent $messageBody" . PHP_EOL;

        $channel->close();
        $connection->close();
    }
}
