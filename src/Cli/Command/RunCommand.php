<?php

namespace Cadfael\Cli\Command;

use Cadfael\Engine\Check\MySQL\Column\CorrectUtf8Encoding;
use Cadfael\Engine\Check\MySQL\Column\ReservedKeywords;
use Cadfael\Engine\Check\MySQL\Column\SaneAutoIncrement;
use Cadfael\Engine\Check\MySQL\Table\AutoIncrementCapacity;
use Cadfael\Engine\Check\MySQL\Table\EmptyTable;
use Cadfael\Engine\Check\MySQL\Table\MustHavePrimaryKey;
use Cadfael\Engine\Check\MySQL\Table\RedundantIndexes;
use Cadfael\Engine\Check\MySQL\Table\SaneInnoDbPrimaryKey;
use Cadfael\Engine\Factory;
use Cadfael\Engine\Report;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class RunCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'run';

    const STATUS_COLOUR = [
        1 => '<fg=green>',
        2 => '<fg=blue>',
        3 => '<fg=cyan>',
        4 => '<fg=yellow>',
        5 => '<fg=red>',
    ];

    protected function renderStatus(Report $report): string
    {
        return self::STATUS_COLOUR[$report->getStatus()]. $report->getStatusLabel() . "</>";
    }

    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Run a collection of checks against a database.')

            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'The host of the database.', 'localhost')
            ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'The port of the database.', 3306)
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'The username of the database.', 'root')
            ->addArgument('schema', InputArgument::REQUIRED, 'The schema to scan.')
            // the full command description shown when running the command with
            // the "--help" option
//            ->setHelp('.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Cadfael CLI Tool');
        $output->writeln('');

        $output->writeln('<info>Host:</info> ' . $input->getOption('host') . ':' . $input->getOption('port'));
        $output->writeln('<info>User:</info> ' . $input->getOption('username'));
        $output->writeln('');

        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln("Attempting to scan schema <info>" . $input->getArgument('schema') . "</info>");

        $question = new Question('What is the database password? ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $helper = $this->getHelper('question');
        $password = $helper->ask($input, $output, $question);

        $connectionParams = array(
            'dbname'    => $input->getArgument('schema'),
            'user'      => $input->getOption('username'),
            'password'  => $password,
            'host'      => $input->getOption('host'),
            'driver'    => 'pdo_mysql',
        );
        $connection = DriverManager::getConnection($connectionParams);
        $factory = new Factory($connection);

        $table = new Table($output);
        $table->setHeaders(['Check', 'Entity', 'Status', 'Message']);
        $checks = [
            new MustHavePrimaryKey(),
            new SaneInnoDbPrimaryKey(),
            new EmptyTable(),
            new AutoIncrementCapacity(),
            new RedundantIndexes(),
            new ReservedKeywords(),
            new SaneAutoIncrement(),
            new CorrectUtf8Encoding(),
        ];

        foreach ($factory->getTables("tests") as $entity) {
            foreach ($checks as $check) {
                if ($check->supports($entity)) {
                    $report = $check->run($entity);
                    if (!is_null($report) && $report->getStatus() != Report::STATUS_OK) {
                        $table->addRow([
                            $report->getCheckLabel(),
                            $report->getEntity(),
                            $this->renderStatus($report),
                            implode("\n", $report->getMessages())
                        ]);
                    }
                }

                foreach ($entity->getColumns() as $column) {
                    if ($check->supports($column)) {
                        $report = $check->run($column);
                        if (!is_null($report) && $report->getStatus() != Report::STATUS_OK) {
                            $table->addRow([
                                $report->getCheckLabel(),
                                $report->getEntity(),
                                $this->renderStatus($report),
                                implode("\n", $report->getMessages())
                            ]);
                        }
                    }
                }
            }
        }

        $table->setColumnMaxWidth(0, 22);
        $table->setColumnMaxWidth(1, 40);
        $table->setColumnMaxWidth(2, 8);
        $table->setColumnMaxWidth(3, 80);
        $table->render();
        $output->writeln('');

        return Command::SUCCESS;
    }
}
