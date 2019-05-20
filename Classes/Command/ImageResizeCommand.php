<?php
declare(strict_types=1);

namespace Iresults\ResizeImageError\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ImageService;

class ImageResizeCommand extends Command
{
    protected function configure()
    {
        $help = 'Resize an image';

        $this->setDescription('Import the products')
            ->setHelp($help)
            ->addArgument('input', InputArgument::REQUIRED, 'Input file')
            ->addArgument('destination', InputArgument::REQUIRED, 'Output file');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var ImageService $imageService */
        $imageService = $om->get(ImageService::class);

        $inputPath = $input->getArgument('input');
        $destination = $input->getArgument('destination');

        $output->writeln(
            sprintf('Resize file %s and store in %s as %s', $inputPath, dirname($destination), basename($destination))
        );

        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        $destinationFolder = $resourceFactory->retrieveFileOrFolderObject(dirname($destination));
        $image = $resourceFactory->retrieveFileOrFolderObject($inputPath);
        if (substr($image->getMimeType(), 0, 6) === 'image/') {
            $processingInstructions = [
                'maxWidth'  => 1200,
                'maxHeight' => 1200,
                'crop'      => false,
            ];
            $processedImage = $imageService->applyProcessingInstructions($image, $processingInstructions);
            $output->writeln('Did process file');

            // This will crash:
            $processedImage->moveTo($destinationFolder);
            $output->writeln('Did move file');
        }
    }
}

