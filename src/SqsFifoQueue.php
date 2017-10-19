<?php

namespace Maqe\LaravelSqsFifo;

use Illuminate\Queue\SqsQueue;

class SqsFifoQueue extends SqsQueue
{
    static $groupId;
    
    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $response = $this->sqs->sendMessage([
            'QueueUrl' => $this->getQueue($queue),
            'MessageBody' => $payload,
            'MessageGroupId' => self::$groupId ?? uniqid(),
            'MessageDeduplicationId' => uniqid(),
        ]);

        return $response->get('MessageId');
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTime|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        if (method_exists($this, 'getSeconds')) { // Support for Laravel < v5.4
            $delay = $this->getSeconds($delay);
        } else {
            $delay = $this->secondsUntil($delay);
        }

        return $this->sqs->sendMessage([
            'QueueUrl' => $this->getQueue($queue),
            'MessageBody' => $payload,
            'DelaySeconds' => $delay,
            'MessageGroupId' => uniqid(),
            'MessageDeduplicationId' => uniqid(),

        ])->get('MessageId');
    }
}
