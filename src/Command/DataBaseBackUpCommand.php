<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DataBaseBackUpCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'Buco:db:backup';
    protected $kernel;
    protected $process;

    protected function configure()
    {
        $this->setDescription('Crea backup de la base de datos');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Obtengo el nombre de la BD
        $database = $this->getContainer()->get('doctrine.dbal.default_connection')->getDatabase();
        // Obtengo los parametros de conexion
        $params = $this->getContainer()->get('doctrine.dbal.default_connection')->getParams();
        // Obtengo el directorio donde se guardara el backup
        $pathBackups = $this->getContainer()->get('kernel')->getRootDir() . "/../var/backupsDB/";

        // tipo de compresión segun el ambiente
        if (strcmp($this->getContainer()->getParameter('ambiente'), 'prod') === 0) {
            $compress = "bzip2";
            $ext = "bz2";
        } else {
            $compress = "gzip";
            $ext = "gz";
        }

        $datetime = date('Ymd-His');
        $file_name = "$database-$datetime.sql.$ext";

        $this->process = new Process(sprintf(
            'mysqldump -u %s --password=%s %s | %s > %s',
            $params["user"],
            $params["password"],
            $database,
            $compress,
            $pathBackups . $file_name
        ));

        try {
            $this->process->mustRun();

            $io->success('The backup has been proceed successfully.');
        } catch (ProcessFailedException $exception) {
            $io->error($exception);
        }

        return 0;
    }
}
