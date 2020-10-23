<?php

namespace App\Console\Commands\TalkRabbitMq;

use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TopicPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbit:topic_publish {message}';

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

        $exchangeName = 'topic_teste';
        $exchangeType = 'topic';
        $rountingKey = 'serie.teste';
        $channel->exchange_declare($exchangeName, $exchangeType, false, true, false);
        $this->declareQueues($channel, $exchangeName);

        $messageBody = $this->argument('message') ?? 'Hello World!';

        $message = new AMQPMessage(
            $messageBody,
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]
        );

        $channel->basic_publish($message, $exchangeName, $rountingKey);

        echo " [x] Sent $messageBody" . PHP_EOL;

        $channel->close();
        $connection->close();
    }

    /**
     * @param AMQPChannel $channel
     * @param string $exchangeName
     */
    private function declareQueues(AMQPChannel $channel, $exchangeName)
    {
        $channel->queue_declare('black_phanter_queue', false, true, false, false);
        $channel->queue_bind('black_phanter_queue', $exchangeName, 'movie.*');

        $channel->queue_declare('lord_of_rings_queue', false, true, false, false);
        $channel->queue_bind('lord_of_rings_queue', $exchangeName, 'movie.*');

        $channel->queue_declare('game_of_thrones_queue', false, true, false, false);
        $channel->queue_bind('game_of_thrones_queue', $exchangeName, 'serie.*');

        $channel->queue_declare('breaking_bad_queue', false, true, false, false);
        $channel->queue_bind('breaking_bad_queue', $exchangeName, 'serie.*');

        $channel->queue_declare('dilema_das_redes_queue', false, true, false, false);
        $channel->queue_bind('dilema_das_redes_queue', $exchangeName, 'movie.*');

        $channel->queue_declare('beijo_do_vampiro_queue', false, true, false, false);
        $channel->queue_bind('beijo_do_vampiro_queue', $exchangeName, 'novel.*');
    }
}
