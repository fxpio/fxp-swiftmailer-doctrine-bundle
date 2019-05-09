<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SwiftmailerDoctrineBundle\Command;

use Fxp\Component\SwiftmailerDoctrine\Spool\DoctrineSpool;
use Symfony\Bundle\SwiftmailerBundle\Command\SendEmailCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Override the Symfony SendEmailCommand for work with doctrine spool and recover-timeout.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SendEmailCommandOverride extends SendEmailCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $name = $input->getOption('mailer');
        if ($name) {
            $this->processDoctrineMailer($name, $input, $output);
        } else {
            $mailers = array_keys($this->getContainer()->getParameter('swiftmailer.mailers'));
            foreach ($mailers as $name) {
                $this->processDoctrineMailer($name, $input, $output);
            }
        }
    }

    /**
     * Process the mailer.
     *
     * @param string          $name   The mailer name
     * @param InputInterface  $input  The input
     * @param OutputInterface $output The output
     */
    private function processDoctrineMailer(string $name, InputInterface $input, OutputInterface $output): void
    {
        if (!$this->getContainer()->has(sprintf('swiftmailer.mailer.%s', $name))) {
            throw new \InvalidArgumentException(sprintf('The mailer "%s" does not exist.', $name));
        }

        $output->write(sprintf(
            '<info>[%s]</info> Processing <info>%s</info> mailer... ',
            date('Y-m-d H:i:s'),
            $name
        ));

        if ($this->getContainer()->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name))) {
            $mailer = $this->getContainer()->get(sprintf('swiftmailer.mailer.%s', $name));
            $transport = $mailer->getTransport();

            if ($transport instanceof \Swift_Transport_SpoolTransport) {
                $this->flushQueue($name, $transport, $input, $output);
            }
        } else {
            $output->writeln('No email to send as the spool is disabled.');
        }
    }

    /**
     * @param string                          $name      The mailer name
     * @param \Swift_Transport_SpoolTransport $transport The swiftmailer transport
     * @param InputInterface                  $input     The input
     * @param OutputInterface                 $output    The output
     */
    private function flushQueue(
        string $name,
        \Swift_Transport_SpoolTransport $transport,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $spool = $transport->getSpool();

        if ($spool instanceof \Swift_ConfigurableSpool) {
            $spool->setMessageLimit($input->getOption('message-limit'));
            $spool->setTimeLimit($input->getOption('time-limit'));
        }

        if ($spool instanceof DoctrineSpool) {
            if (null !== $input->getOption('recover-timeout')) {
                $spool->recover($input->getOption('recover-timeout'));
            } else {
                $spool->recover();
            }
        }

        /** @var \Swift_Transport $realTransport */
        $realTransport = $this->getContainer()->get(sprintf('swiftmailer.mailer.%s.transport.real', $name));
        $sent = $spool->flushQueue($realTransport);

        $output->writeln(sprintf('<comment>%d</comment> emails sent', $sent));
    }
}
