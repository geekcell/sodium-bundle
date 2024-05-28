<?php

declare(strict_types=1);

namespace GeekCell\SodiumBundle\Command;

use GeekCell\SodiumBundle\Sodium\Crypto\Box;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateKeysCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('sodium:generate-keys')
            ->setDescription('Generate a new set of public and private keys')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (\extension_loaded('sodium') === false) {
            $output->writeln("<error>The sodium extension is not loaded. Please install and enable it.</error>");

            return Command::FAILURE;
        }

        $box = Box::create();
        $publicKey = $box->getPublicKey();
        $privateKey = $box->getPrivateKey();

        $output->writeln("<info>Generating a new set of public and private keys...</info>\n");
        $output->writeln("<info>Public Key: </info> " . $publicKey);
        $output->writeln("<info>Private Key:</info> " . $privateKey);
        $output->writeln([
            "\n<info>Please add or update the following environment variables in your .env.local file:</info>\n",
            "SODIUM_PUBLIC_KEY={$publicKey}",
            "SODIUM_PRIVATE_KEY={$privateKey}",
            "\n<info>Done!</info>",
        ]);

        return Command::SUCCESS;
    }
}
