<?php
namespace Pixelant\Importify\Tests\Unit\Property\TypeConverter;

/**
 * Test case.
 *
 * @author Tim Ta <tim.ta@pixelant.se>
 */
class UploadedFileReferenceConverterTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Pixelant\Importify\Property\TypeConverter\UploadedFileReferenceConverter
     */
    protected $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = $this->getMock(
            \Pixelant\Importify\Property\TypeConverter\UploadedFileReferenceConverter::class,
            ['redirect', 'forward', 'addFlashMessage'],
            [],
            '',
            false
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function convertFromTest()
    {
        // test if source error is \UPLOAD_ERR_NO_FILE
        $source['tmp_name'] = 'tmp_name';
        $source['error'] = \UPLOAD_ERR_NO_FILE;
        $source['submittedFile']['resourcePointer'] = 'resourcePointer';
        $targetType = 'TYPO3\\CMS\\Extbase\\Domain\\Model\\FileReference';

        // test resourcePointer start with string 'name:'
        $hashService = $this->getMock(
            \TYPO3\CMS\Extbase\Security\Cryptography\HashService::class,
            ['validateAndStripHmac'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'hashService', $hashService);
        $hashService->expects(self::any())->method(
            'validateAndStripHmac'
        )->will($this->returnValue('name: test'));

        $file = $this->getMock(
            \TYPO3\CMS\Core\Resource\File::class,
            [],
            [],
            '',
            false
        );
        $fileReference = $this->getMock(
            \TYPO3\CMS\Core\Resource\FileReference::class,
            ['setOriginalResource'],
            [],
            '',
            false
        );
        $resourceFactory = $this->getMock(
            \TYPO3\CMS\Core\Resource\ResourceFactory::class,
            ['getFileObject','getFileReferenceObject', 'retrieveFileOrFolderObject'],
            [],
            '',
            false
        );

        $this->inject($this->subject, 'resourceFactory', $resourceFactory);
        $resourceFactory->expects(self::any())->method(
            'getFileObject'
        )->will($this->returnValue($file));

        $this->inject($this->subject, 'resourceFactory', $resourceFactory);
        $resourceFactory->expects(self::any())->method(
            'getFileReferenceObject'
        )->will($this->returnValue($fileReference));

        $this->subject->convertFrom($source, $targetType);

        // test resourcePointer is null
        $hashServiceNull = $this->getMock(
            \TYPO3\CMS\Extbase\Security\Cryptography\HashService::class,
            ['validateAndStripHmac'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'hashService', $hashServiceNull);
        $hashServiceNull->expects(self::any())->method(
            'validateAndStripHmac'
        )->will($this->returnValue(null));

        $objectManager = $this->getMock(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class,
            ['get'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'objectManager', $objectManager);
        $objectManager->expects(self::any())->method(
            'get'
        )->will($this->returnValue($fileReference));
        $this->subject->convertFrom($source, $targetType);

        // test resourcePointer is not null and doesnt start with string 'name:'
        $hashServiceTest = $this->getMock(
            \TYPO3\CMS\Extbase\Security\Cryptography\HashService::class,
            ['validateAndStripHmac'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'hashService', $hashServiceTest);
        $hashServiceTest->expects(self::any())->method(
            'validateAndStripHmac'
        )->will($this->returnValue(' name:'));


        $persistenceManager = $this->getMock(
            \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class,
            ['getObjectByIdentifier'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'persistenceManager', $persistenceManager);
        $persistenceManager->expects(self::any())->method(
            'getObjectByIdentifier'
        )->will($this->returnValue($fileReference));
        $this->subject->convertFrom($source, $targetType);

        // test source has no error
        unset($source);
        $this->subject->convertFrom($source, $targetType);
        // test source error is \UPLOAD_ERR_PARTIAL
        $source['error'] = \UPLOAD_ERR_PARTIAL;
        $this->subject->convertFrom($source, $targetType);

        // test source error is set
        $source['error'] = 'test';
        $this->subject->convertFrom($source, $targetType);

        //unset($source);
        // test source error is \UPLOAD_ERR_OK and convertedResources is not set
        $source['error'] = \UPLOAD_ERR_OK;
        $source['name'] = 'test.csv';
        $source['submittedFile']['resourcePointer'] = null;
        $config = $this->getMock(
            \TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration::class,
            ['getConfigurationValue'],
            [],
            '',
            false
        );

        $folder = $this->getMock(
            TYPO3\CMS\Core\Resource\Folder::class,
            ['addUploadedFile'],
            [],
            '',
            false
        );
        $this->inject($this->subject, 'resourceFactory', $resourceFactory);
        $resourceFactory->expects(self::any())->method(
            'retrieveFileOrFolderObject'
        )->will($this->returnValue($folder));

        $folder->expects(self::any())->method(
            'addUploadedFile'
        )->will($this->returnValue($file));

        $config->expects(self::any())->method(
            'getConfigurationValue'
        )->will($this->returnValue('csv, txt'));


        $fileReferenceCMS = $this->getMock(
            \TYPO3\CMS\Extbase\Domain\Model\FileReference::class,
            ['setOriginalResource'],
            [],
            '',
            false
        );

        $fileRepository = $this->getMock(
            \TYPO3\CMS\Core\Resource\FileRepository::class,
            ['createFileReferenceObject'],
            [],
            '',
            false
        );

        $fileRepository->expects(self::any())->method(
            'createFileReferenceObject'
        )->will($this->returnValue($fileReferenceCMS));

        $this->subject->convertFrom($source, $targetType, [], $config);

        // test convertedResources is set
        $source['tmp_name'] = 'test';
        $convertedResources['test'] = 'test';
        $this->inject($this->subject, 'convertedResources', $convertedResources);
        $this->subject->convertFrom($source, $targetType, [], $config);
    }
}
