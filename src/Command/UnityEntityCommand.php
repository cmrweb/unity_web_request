<?php 
namespace Cmrweb\UnityWebRequest\Command;

use Cmrweb\UnityWebRequest\UnityEntityMapper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command; 
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface; 
use Symfony\Component\Console\Output\OutputInterface; 
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem; 
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'unity:entity',
    description: 'Generate new C# script or mapped by entity',
    aliases: ['u:e', 'unity:e']
)]
class UnityEntityCommand extends Command
{
    private SymfonyStyle $io;
    private string $cacheDir;
    private const ACCEPTED_TYPES = ['int', 'string', 'float', 'bool'];

    public function __construct( 
        private readonly KernelInterface $kernel,
        private readonly UnityEntityMapper $unityEntityMapper
    ) {
        parent::__construct();
        $this->cacheDir = $this->kernel->getProjectDir() . '/assets/unity/'; 
    }

    protected function configure(): void
    {
        $this->addArgument('class', InputArgument::OPTIONAL, 'new c# class or entity to map');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    { 
        $this->io = new SymfonyStyle($input, $output);  

        $className = $input->getArgument('class');
        # get entities  
        if ($className) {
            $this->io->note(sprintf('You passed an argument: %s', $className));
        } else {   
            $className = $this->io->ask("Class name of the entity or new class name for create Unity C# script"); 
            if(null === $className) {
                $this->io->error('class name cannot be null');
                return Command::FAILURE;
            }  
        }
        $className = ucfirst($className);
        $this->io->info('Generating Class '.$className);
        #createclass
        $unityScript = $this->unityEntityMapper->createScriptFromEntity($className); 
        if(null === $unityScript) {
        $unityScript = $this->unityEntityMapper->createClass($className);
            $properties = [];
            $propertyCount = 0;
            $close = false;
            while (!$close) { 
                $properties[$propertyCount]['name'] = $this->io->ask("New property name (press <return> to stop adding fields)", null, function(mixed $name) {
                    if (preg_match('/[a-zA-Z]+/', $name)) {
                        return strtolower($name);
                    }
                });
                if(null !== $properties[$propertyCount]['name']) { 
                    $type = null;
                    while (null === $type) { 
                        $type = $this->io->ask("Field type (enter ? to see all types)", "string", function(string $value) {
                            if(!in_array($value, self::ACCEPTED_TYPES)) {
                                $this->io->writeln("<info>Main Types</>");
                                $this->io->listing(self::ACCEPTED_TYPES); 
                            } else {
                                return $value;
                            }
                        }); 
                    }
                    $properties[$propertyCount]['type'] = $type;
                    $unityScript->addproperty([
                        $unityScript->createProperty(name: $properties[$propertyCount]['name'], type: $type)
                    ]); 
                    # add in file
                    $this->io->success('add '.$properties[$propertyCount]['name']);  
                    $propertyCount++;
                } else {
                    $close = true;
                }
            }
            # create file 

        } 
            # unityScript->doSomething(array formattedInput) 
            # end loop  
            # create C# script
            $file = new Filesystem();   
            $path = $this->cacheDir . $className . '.cs';
            if($file->exists($path)) {
                $file->remove($path);
            }
            $file->appendToFile($path, $unityScript->getFile());  
            $this->io->success('Class ' . $className . ' created at ' . $path);

        return Command::SUCCESS;
    }
}