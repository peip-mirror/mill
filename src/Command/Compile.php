<?php
namespace Mill\Command;

use Mill\Application;
use Mill\Config;
use Mill\Compiler\Specification\ApiBlueprint;
use Mill\Exceptions\Version\UnrecognizedSchemaException;
use Mill\Parser\Version;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Compile extends Application
{
    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('compile')
            ->setDescription('Compile API documentation.')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'API specification format to compile documentation into. Available formats: `apiblueprint`, `openapi`',
                'openapi'
            )
            ->addOption(
                'constraint',
                null,
                InputOption::VALUE_OPTIONAL,
                'Version constraint to compile documentation for. eg. "3.*", "3.1 - 3.2"',
                null
            )
            ->addOption(
                'default',
                null,
                InputOption::VALUE_OPTIONAL,
                'Compile just the configured default API version documentation. `defaultApiVersion` in your ' .
                    '`mill.xml` file.',
                false
            )
            ->addArgument('output', InputArgument::REQUIRED, 'Directory to output your compiled documentation in.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $output_dir = realpath($input->getArgument('output'));
        $format = strtolower($input->getOption('format'));
        $version = $input->getOption('constraint');

        if (!in_array($format, ['apiblueprint', 'openapi'])) {
            $output->writeLn('<error>`' . $format . '` is an unknown compilation format.</error>');
            return 1;
        }

        if ($input->getOption('default')) {
            $version = $this->container['config']->getDefaultApiVersion();
        }

        // Validate the current version constraint.
        if (!empty($version)) {
            try {
                $version = new Version($version, __CLASS__, __METHOD__);
            } catch (UnrecognizedSchemaException $e) {
                $output->writeLn('<error>' . $e->getValidationMessage() . '</error>');
                return 1;
            }
        }

        /** @var Config $config */
        $config = $this->container['config'];

        /** @var \League\Flysystem\Filesystem $filesystem */
        $filesystem = $this->container['filesystem'];

        $output->writeln('<comment>Compiling controllers and representations...</comment>');
        $compiler = new ApiBlueprint($config, $version);

        $output->writeln('<comment>Compiling API Blueprint files...</comment>');
        $blueprints = $compiler->compile();

        foreach ($blueprints as $version => $section) {
            $version_dir = $output_dir . self::DS . $version . self::DS;

            $output->writeLn('<comment> - API version: ' . $version . '</comment>');

            $total_work = (isset($section['groups'])) ? count($section['groups']) : 0;
            $total_work += (isset($section['structures'])) ? count($section['structures']) : 0;

            $progress = new ProgressBar($output, $total_work);
            $progress->setFormatDefinition('custom', ' %current%/%max% [%bar%] %message%');
            $progress->setFormat('custom');
            $progress->start();

            // Process resource groups.
            if (isset($section['groups'])) {
                $progress->setMessage('Processing resources...');
                foreach ($section['groups'] as $group => $markdown) {
                    $progress->advance();

                    // Convert any nested groups, like `Me\Videos`, into a proper directory structure: `Me/Videos`.
                    $group = str_replace('\\', self::DS, $group);

                    $filesystem->put(
                        $version_dir . 'resources' . self::DS . $group . '.apib',
                        trim($markdown)
                    );
                }
            }

            // Process data structures.
            if (isset($section['structures'])) {
                $progress->setMessage('Processing representations...');
                foreach ($section['structures'] as $structure => $markdown) {
                    $progress->advance();

                    // Sanitize any structure names with forward slashes to avoid them from being nested in directories
                    // by Flysystem.
                    $structure = str_replace('/', '-', $structure);

                    $filesystem->put(
                        $version_dir . 'representations' . self::DS . $structure . '.apib',
                        trim($markdown)
                    );
                }
            }

            // Save a, single, combined API Blueprint file.
            $filesystem->put($version_dir . 'api.apib', $section['combined']);

            $progress->setMessage('');
            $progress->finish();
            $output->writeLn('');
        }

        $output->writeln(['', '<success>Done!</success>']);

        return 0;
    }
}
