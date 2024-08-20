<?php

namespace App\NotificationPublisher\UserInterface\CLI;

use App\NotificationPublisher\Application\Command\SendNotificationCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:send-notification',
    description: 'Sends a notification through the configured providers.',
)]
class SendNotificationConsoleCommand extends Command
{
    protected static $defaultName = 'app:send-notification';
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        parent::__construct();
        $this->bus = $bus;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('recipient', InputArgument::REQUIRED, 'The recipient of the notification')
            ->addArgument('content', InputArgument::REQUIRED, 'The content of the notification')
            ->addArgument('channel', InputArgument::REQUIRED, 'The channel to send the notification (e.g., sms, email)')
            ->addArgument('limitPerHour', InputArgument::OPTIONAL, 'Limit of notifications per hour');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $recipient = $input->getArgument('recipient');
        $content = $input->getArgument('content');
        $channel = $input->getArgument('channel');
        $limitPerHour = $input->getArgument('limitPerHour');

        $io->title('Sending Notification');
        $io->text([
            'Recipient: ' . $recipient,
            'Content: ' . $content,
            'Channel: ' . $channel,
            'Limit per hour: ' . $limitPerHour,
        ]);

        try {
            // Create the SendNotificationCommand and dispatch it using Symfony Messenger
            $command = new SendNotificationCommand($content, $recipient, $channel, $limitPerHour);
            $this->bus->dispatch($command);
            $io->success('Notification command dispatched successfully.');
        } catch (\Exception $e) {
            $io->error('Failed to dispatch notification command: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
