<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SeriesWasCreated;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SendNewSeriesEmailHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerInterface $mailer,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(SeriesWasCreated $message): void
    {
        $users = $this->userRepository->findAll();
        $usersEmails = array_map(fn (User $user) => $user->getEmail(), $users);
        $series = $message->series;

        $email = (new Email())
            ->from('sistema@teste.com.br')
            ->to(...$usersEmails)
            ->subject('Série Criada!')
            ->text("Série {$series->getName()} foi criada!")
            ->html("<p>Série {$series->getName()} foi criada!</p>");

        $this->mailer->send($email);
    }
}
