<?php

namespace App\Console\Commands\TalkRabbitMq;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FanoutPublish extends Command
{
    const QUEUE_1_NAME = 'fanout1_queue';
    const QUEUE_2_NAME = 'fanout2_queue';
    const QUEUE_3_NAME = 'fanout3_queue';

    /**
     * @var string
     */
    protected $signature = 'rabbit:fanout_publish {message}';

    /**
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $exchangeName = 'teste_fanout';
        $exchangeType = 'fanout';
        $channel->exchange_declare($exchangeName, $exchangeType, false, false, false);
        $this->declareQueues($channel);
        $this->bindQueues($channel, $exchangeName);

        $messageBody = $this->argument('message') ?? 'Hello World!';

        $message = new AMQPMessage($messageBody);

        $channel->basic_publish($message, $exchangeName);

        echo " [x] Sent $messageBody" . PHP_EOL;

        $channel->close();
        $connection->close();
    }

    /**
     * @param AMQPChannel $channel
     */
    private function declareQueues(AMQPChannel $channel)
    {
        $channel->queue_declare(self::QUEUE_1_NAME, false, true, false, false);
        $channel->queue_declare(self::QUEUE_2_NAME, false, true, false, false);
        $channel->queue_declare(self::QUEUE_3_NAME, false, true, false, false);
    }

    /**
     * @param AMQPChannel $channel
     * @param string $exchangeName
     */
    private function bindQueues(AMQPChannel $channel, string $exchangeName): void
    {
        $channel->queue_bind(self::QUEUE_1_NAME, $exchangeName);
        $channel->queue_bind(self::QUEUE_2_NAME, $exchangeName);
        $channel->queue_bind(self::QUEUE_3_NAME, $exchangeName);
    }
}
